<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test ログインユーザーが休憩開始できるかを確認するテスト */
    public function test_user_can_start_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の日付で出勤済みの勤怠レコードを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
        ]);

        $response = $this->post('/attendance/break/start'); // 休憩開始リクエストを送信
        $response->assertRedirect('/attendance'); // 勤怠ページにリダイレクトされることを確認

        // DBに休憩開始レコードが登録されたか確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test ログインユーザーが休憩終了できるかを確認するテスト */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create(); // ユーザー作成して
        $this->actingAs($user); // ログイン

        // 出勤済み勤怠レコード作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
        ]);

        // 休憩中レコードを事前に作成（終了時間はまだ）
        $attendance->breakTimes()->create([
            'break_start' => now()->subMinutes(30), // 休憩が今から30分前に始まっていた
            'break_end' => null,
        ]);

        // 休憩終了リクエストを送信
        $response = $this->post('/attendance/break/end');
        $response->assertRedirect('/attendance');

        // 終了していない休憩がDBから消えていること（つまり終了時間が登録された）
        $this->assertDatabaseMissing('break_times', [
            'attendance_id' => $attendance->id,
            'break_end' => null,
        ]);
    }

    /** @test 未ログインユーザーは休憩開始できないことを確認するテスト */
    public function test_guest_cannot_start_break()
    {
        $response = $this->post('/attendance/break/start'); // 未ログイン状態で休憩開始リクエスト
        $response->assertRedirect('/login'); // ログインページにリダイレクトされることを確認
    }

    /** @test 未ログインユーザーは休憩終了できないことを確認するテスト */
    public function test_guest_cannot_end_break()
    {
        $response = $this->post('/attendance/break/end'); // 未ログイン状態で休憩終了リクエスト
        $response->assertRedirect('/login');
    }

    /** @test 同日に複数回休憩できることを確認するテスト */
    public function test_user_can_take_multiple_breaks_in_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤済みレコード作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        // 1回目の休憩
        $this->post('/attendance/break/start');
        $this->post('/attendance/break/end');

        // 2回目の休憩
        $this->post('/attendance/break/start');
        $this->post('/attendance/break/end');

        // break_timesテーブルに2件休憩記録があることを確認
        $this->assertEquals(2, $attendance->breakTimes()->count());
    }

    /** @test 出勤中のユーザーには「休憩入」ボタンが表示されることを確認するテスト */
    public function test_break_start_button_is_visible_when_working()
    {
        $user = User::factory()->create();

        // 出勤済み・退勤前の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(1),
            'clock_out' => null,
        ]);

        // 勤怠ページを開く
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入'); // Blade内ボタン表示を確認
    }

    /** @test 休憩中のユーザーには「休憩戻」ボタンが表示されることを確認するテスト */
    public function test_break_end_button_is_visible_when_on_break()
    {
        $user = User::factory()->create();
        // 出勤済み勤怠作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
        ]);

        // 休憩中レコード（終了時間が未登録）
        $attendance->breakTimes()->create([
            'break_start' => now()->subMinutes(20),
            'break_end' => null,
        ]);

        // 勤怠ページを開く
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻'); // Blade内ボタン表示を確認
    }
}
