<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified; // 認証済みイベント
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event; // イベントを監視
use Illuminate\Support\Facades\Notification; // 通知を監視
use Illuminate\Auth\Notifications\VerifyEmail; // メール認証通知
use Tests\TestCase;
use Illuminate\Support\Facades\URL; // 認証リンクを作成するため

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test ユーザー登録後に認証メールが送信されるか確認 */
    public function verification_email_is_sent_after_registration()
    {
        Notification::fake(); // 実際のメール送信を止めて、テスト用に監視

        // 認証されていないユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証メールを手動で送信
        $user->sendEmailVerificationNotification();

        // ユーザーに認証メールが送信されたことを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test 未認証のユーザーがメール認証誘導画面にアクセスできるか確認 */
    public function unverified_user_can_access_verification_notice()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/email/verify'); // ログインして、認証ページにアクセス
        $response->assertStatus(200); // 画面が正常に表示されることを確認
        $response->assertSee('認証メールを再送する');
    }

    /** @test 認証リンクをクリックしたら、認証が完了して勤怠画面に遷移するかの確認 */
    public function email_can_be_verified()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 一時的な認証リンクを作成（60分間有効）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', // ルート名
            now()->addMinutes(60), // 有効期限
            ['id' => $user->id, 'hash' => sha1($user->email)] // 必要なパラメータ
        );

        Event::fake(); //イベントが発生したかを確認

        // 認証リンクをクリック
        $response = $this->actingAs($user)->get($verificationUrl);

        // 認証イベントが発火したことを確認
        Event::assertDispatched(Verified::class);
        // DB上でも認証済みか確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // 認証後は勤怠登録画面へ遷移することを確認
        $response->assertRedirect('/attendance');
    }

    /** @test 認証メールの再送処理が動作するかを確認 */
    public function resend_verification_email()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証メール再送用ルートにPOST（ログイン済みユーザーとして）
        $response = $this->actingAs($user)->post('/email/verification-notification');
        // 成功メッセージがセッションに保存されているか確認
        $response->assertSessionHas('message', '認証メールを再送信しました。');

        // 再送信メールが送信されたか確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
