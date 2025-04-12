<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DateTimeStorageTest extends TestCase
{
    use RefreshDatabase;

    // 出勤したときに clock_in が保存されるかテストする
    public function test_clock_in_time_is_recorded_when_clocking_in()
    {
        $user = User::factory()->create();  // ユーザー作成
        $this->actingAs($user); // ログイン状態にする

        $response = $this->post('/attendance'); // 出勤処理を実行

        $response->assertRedirect('/attendance'); // 出勤後、勤怠画面にリダイレクトされるか確認
        $this->assertDatabaseHas('attendances', [ // 勤怠テーブルにレコードがあるか確認
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);
    }

    // 休憩開始が記録されるかテスト
    public function test_break_start_time_is_recorded_when_starting_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤済みの状態を作る
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'status' => 'unconfirmed',
        ]);

        // 休憩開始ボタンを押したときの動作を確認
        $response = $this->post('/attendance/break/start');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('break_times', [
            'break_start' => now()->format('H:i:s'), // 現在時刻で休憩開始が登録されているか
        ]);
    }

    // 休憩終了が記録されるかテスト
    public function test_break_end_time_is_recorded_when_ending_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤済みの状態を作る
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'status' => 'unconfirmed',
        ]);

        // 30分前に休憩を開始していた状態を作る
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subMinutes(30),
        ]);

        // 休憩終了処理
        $response = $this->post('/attendance/break/end');
        $response->assertRedirect('/attendance');

        // break_end が null のレコードがないこと（＝記録されたこと）を確認
        $this->assertDatabaseMissing('break_times', [
            'break_end' => null,
        ]);
    }

    // 退勤時に clock_out が保存されるかテスト
    public function test_clock_out_time_is_recorded_when_clocking_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤済みの勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'status' => 'unconfirmed',
        ]);

        // 退勤処理を実行
        $response = $this->post('/attendance/clockout');
        $response->assertRedirect('/attendance');

        // clock_out が null のままではないことを確認（＝記録された）
        $this->assertDatabaseMissing('attendances', [
            'clock_out' => null,
        ]);
    }
}