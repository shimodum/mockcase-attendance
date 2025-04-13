<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test 勤怠詳細画面にログインユーザーの名前が表示される */
    public function user_name_is_displayed_on_detail_screen()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // ログイン状態で勤怠詳細画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200); // ステータスコード200（正常）が返ってくるか
        $response->assertSee('テスト太郎'); // ログインユーザーの名前が表示されているか
    }

    /** @test 勤怠詳細画面に勤怠の日付が表示される */
    public function attendance_date_is_displayed()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-04-13',
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('2025年4月13日'); // Blade側で「Y年n月j日」と整形されるため、この形式で確認する
    }

    /** @test 勤怠詳細画面に出勤・退勤時間が正しく表示される */
    public function clock_in_and_out_times_are_displayed()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test 勤怠詳細画面に休憩開始・終了時間が正しく表示される */
    public function break_times_are_displayed()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }
}
