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

    /** @test ログインユーザーが休憩開始できることを確認するテスト */
    public function test_user_can_start_break()
    {
        $user = User::factory()->create(); // ユーザーを作成する
        $this->actingAs($user); // ユーザーをログイン状態にする

        // 今日の出勤データを作成（2時間前に出勤済み）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
        ]);

        // 「休憩開始」処理を実行（POSTリクエスト送信）
        $response = $this->post('/attendance/break/start');

        // 正常に勤怠画面にリダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // break_times テーブルに休憩開始データが保存されているか確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test ログインユーザーが休憩終了できることを確認するテスト */
    public function test_user_can_end_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤済みデータを作成（2時間前に出勤）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
        ]);

        // 30分前に休憩を開始したが、まだ終了していない休憩データを作成
        $attendance->breakTimes()->create([
            'break_start' => now()->subMinutes(30),
            'break_end' => null, // 終了していない状態
        ]);

        // 「休憩終了」処理を実行
        $response = $this->post('/attendance/break/end');

        // 勤怠画面にリダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // break_end が null のレコードがなくなっていること（＝終了時間が登録されたこと）を確認
        $this->assertDatabaseMissing('break_times', [
            'attendance_id' => $attendance->id,
            'break_end' => null,
        ]);
    }

    /** @test 未ログインユーザーは休憩開始できない事を確認するテスト */
    public function test_guest_cannot_start_break()
    {
        $response = $this->post('/attendance/break/start'); // ログインしていない状態で休憩開始リクエストを送信
        $response->assertRedirect('/login'); // 強制的にログイン画面へリダイレクトされることを確認
    }

    /** @test 未ログインユーザーは休憩終了できない確認するテスト */
    public function test_guest_cannot_end_break()
    {
        $response = $this->post('/attendance/break/end'); // ログインしていない状態で休憩終了リクエストを送信
        $response->assertRedirect('/login'); // ログイン画面へリダイレクトされることを確認
    }
}
