<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test 出勤ボタンを押すと出勤レコードが作成されるテスト */
    public function user_can_clock_in_once_per_day()
    {
        // 認証済みのユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 現在日時を固定（Carbon::now() が使われているため）
        Carbon::setTestNow(Carbon::parse('2025-04-13 09:00:00'));

        // ログイン状態で出勤処理を送信
        $response = $this->actingAs($user)->post('/attendance');

        // 出勤レコードがDBに保存されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2025-04-13',
            'status' => 'unconfirmed',
        ]);

        // 登録された出勤時刻が想定通りか確認（フォーマット：H:i:s）
        $attendance = Attendance::first();
        $this->assertEquals('09:00:00', Carbon::parse($attendance->clock_in)->format('H:i:s'));

        // 正常に勤怠画面にリダイレクトされていることを確認
        $response->assertRedirect('/attendance');
    }

    /** @test 同じ日に2回出勤しても2回目は記録されないことを確認するテスト */
    public function user_cannot_clock_in_twice_on_same_day()
    {
        $user = User::factory()->create(['email_verified_at' => now()]); // 認証済みユーザーを作成

        Carbon::setTestNow(Carbon::parse('2025-04-13 09:00:00')); // 出勤日時を固定

        // 1回目の出勤
        $this->actingAs($user)->post('/attendance');

        // 2回目の出勤（同じ日）
        $this->actingAs($user)->post('/attendance');

        // 出勤レコードが1件だけであることを確認（2回目は無効）
        $this->assertEquals(1, Attendance::count());
    }

    /** @test 未ログインユーザーが出勤しようとするとログイン画面にリダイレクトされるテスト */
    public function guest_cannot_clock_in()
    {
        // 現在時刻を固定
        Carbon::setTestNow(Carbon::parse('2025-04-13 09:00:00'));

        // 未ログイン状態で出勤処理を実行
        $response = $this->post('/attendance');

        // ログイン画面にリダイレクトされることを確認
        $response->assertRedirect('/login');

        // 勤怠テーブルに出勤レコードが作成されていないことを確認
        $this->assertDatabaseMissing('attendances', [
            'date' => '2025-04-13'
        ]);
    }
}
