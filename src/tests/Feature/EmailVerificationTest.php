<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified; // 認証完了時に発生するイベント
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event; // イベントを監視
use Illuminate\Support\Facades\Notification; // 通知を監視
use Illuminate\Auth\Notifications\VerifyEmail; // メール認証通知
use Tests\TestCase;
use Illuminate\Support\Facades\URL; // 認証リンクを作成するための機能

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test ユーザー登録後に認証メールが送信されるか確認 */
    public function verification_email_is_sent_after_registration()
    {
        Notification::fake(); // 実際にメールを送らず、テストとして通知だけを確認できるようにする

        // 認証されていないユーザーを作成（email_verified_at が null）
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
        $response->assertStatus(200); // ステータス200（正常）を確認
        $response->assertSee('認証メールを再送する');
    }

    /** @test 認証リンクをクリックしたら、認証が完了して勤怠登録画面にリダイレクトされるか確認 */
    public function email_can_be_verified()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 一時的な認証リンクを作成（60分間有効）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', // 認証確認用のルート名
            now()->addMinutes(60), // 有効期限
            ['id' => $user->id, 'hash' => sha1($user->email)] // 署名に必要なパラメータ
        );

        Event::fake(); // イベント（Verified）が実行されたかテストで監視

        // 認証リンクをクリック（URLにアクセス）
        $response = $this->actingAs($user)->get($verificationUrl);

        // 認証イベントが実行されたか確認
        Event::assertDispatched(Verified::class);
        // ユーザーの email_verified_at が更新されているか（= 認証済み）を確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // // 認証完了後は「勤怠登録画面」へリダイレクトされることを確認
        $response->assertRedirect('/attendance');
    }

    /** @test 認証メールの再送処理が正しく動作するかを確認 */
    public function resend_verification_email()
    {
        Notification::fake(); // 通知送信を止めて監視モードにする

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 再送信リクエストをログイン済み状態で送信
        $response = $this->actingAs($user)->post('/email/verification-notification');
        // セッションに再送成功メッセージがあるか確認
        $response->assertSessionHas('message', '認証メールを再送信しました。');

        // 認証メールが送られていることを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
