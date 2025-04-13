<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test 出勤前のユーザーは before.blade.php が表示されること */
    public function user_without_attendance_sees_before_page()
    {
        $user = User::factory()->create(); // ユーザーを1人作成（出勤前の状態）
        $response = $this->actingAs($user)->get('/attendance'); // ログイン状態にして勤怠ページにアクセス

        $response->assertStatus(200); // ステータスコード200（正常）が返ってくるか
        $response->assertSee('勤務外'); // 「勤務外」という文字が表示されているか
        $response->assertViewIs('attendance.before'); // before.blade.php （出勤前画面）が表示されているか
    }

    /** @test 出勤後、退勤前、休憩中でないユーザーは working.blade.php が表示されること */
    public function user_with_attendance_but_not_clocked_out_sees_working_page()
    {
        $user = User::factory()->create(); // 出勤済みユーザーを作成

        // 出勤済みで退勤・休憩はしていない状態を作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now(), // 出勤はしている
            'clock_out' => null, // 退勤していない
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200); // ステータスコード200（正常）が返ってくるか
        $response->assertSee('出勤中'); // 出勤中という文字が表示されているか
        $response->assertViewIs('attendance.working'); // working.blade.php（出勤後画面）が表示されることを確認
    }

    /** @test 出勤後、休憩中のユーザーは break.blade.php が表示されること */
    public function user_on_break_sees_break_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now(), // 出勤済み
        ]);

        // break_start はあるが break_end が null の状態（休憩中）
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => now(), // 休憩を開始しているが
            'break_end' => null, // 終わっていない → 休憩中と判断
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200); // ステータスコード200（正常）が返ってくるか
        $response->assertSee('休憩中'); // 休憩中という文字が表示されているか
        $response->assertViewIs('attendance.break'); // break.blade.php（休憩中画面）が表示されているか
    }

    /** @test 退勤済みのユーザーは after.blade.php が表示されること */
    public function user_clocked_out_sees_after_page()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200); // ステータスコード200（正常）が返ってくるか
        $response->assertSee('退勤済'); // 退勤済という文字が表示されているか
        $response->assertViewIs('attendance.after'); // after.blade.php（退勤後画面）が表示されているか
    }
}
