<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\BreakTimeCorrection;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤より退勤が前だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => '修正',
            ])
            ->assertSessionHasErrors('clock_out');
    }

    /** @test */
    public function 休憩開始より休憩終了が前だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => '16:00',
                'break_end' => '15:00',
                'note' => '修正',
            ])
            ->assertSessionHasErrors('break_end');
    }

    /** @test */
    public function 休憩が勤務時間外ならエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => '08:00', // 勤務開始前
                'break_end' => '09:30',
                'note' => '修正',
            ])
            ->assertSessionHasErrors('break_start');
    }

    /** @test */
    public function 備考が未入力だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '', // 備考なし
            ])
            ->assertSessionHasErrors('note');
    }

    /** @test */
    public function 修正申請が保存される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();
        BreakTime::factory()->for($attendance)->create();

        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => '12:00',
                'break_end' => '13:00',
                'note' => '修正内容',
            ])
            ->assertRedirect("/attendance/{$attendance->id}");

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:00:00',
            'requested_clock_out' => '18:00:00',
            'request_reason' => '修正内容',
        ]);

        $this->assertDatabaseHas('break_time_corrections', [
            'requested_break_start' => '12:00:00',
            'requested_break_end' => '13:00:00',
        ]);
    }

    /** @test */
    public function 修正申請一覧に自分の申請が表示される()
    {
        $user = User::factory()->create();

        // status を明示的に waiting_approval に！
        $attendance = Attendance::factory()->for($user)->create([
            'status' => 'waiting_approval',
        ]);

        AttendanceCorrection::factory()->for($attendance)->create([
            'request_reason' => 'テスト申請',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=waiting_approval');

        // 申請理由が一覧に表示されることを確認
        $response->assertSee('テスト申請');
    }

    /** @test */
    public function 承認済みの申請が一覧に表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertSee('承認済');
    }

    /** @test */
    public function 修正申請の詳細画面に遷移できる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");
        $response->assertStatus(200)->assertSee('勤怠詳細');
    }
}
