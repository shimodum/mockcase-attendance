<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use App\Models\BreakTimeCorrection;

class AdminCorrectionApprovalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_waiting_approval_corrections_are_listed()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->create(['status' => 'waiting_approval']);
        AttendanceCorrection::factory()->for($attendance)->create(['request_reason' => '承認待ち申請']);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=waiting_approval');

        $response->assertStatus(200)->assertSee('承認待ち申請');
    }

    /** @test */
    public function test_approved_corrections_are_listed()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->create(['status' => 'approved']);
        AttendanceCorrection::factory()->for($attendance)->create(['request_reason' => '承認済み申請']);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200)->assertSee('承認済み申請');
    }

    /** @test */
    public function test_correction_detail_is_displayed_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create();
        $correction = AttendanceCorrection::factory()->for($attendance)->create([
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'request_reason' => '詳細確認テスト',
        ]);

        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/{$correction->id}");

        $response->assertStatus(200)->assertSee('09:00')->assertSee('18:00')->assertSee('詳細確認テスト');
    }

    /** @test */
    public function test_correction_approval_updates_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->create();
        $correction = AttendanceCorrection::factory()->for($attendance)->create([
            'requested_clock_in' => '09:30',
            'requested_clock_out' => '17:30',
            'request_reason' => '更新テスト',
        ]);

        BreakTime::factory()->for($attendance)->create();
        BreakTimeCorrection::factory()->for($attendance->breakTimes->first())->create([
            'requested_break_start' => '12:30',
            'requested_break_end' => '13:00',
        ]);

        $response = $this->actingAs($admin)->post("/stamp_correction_request/approve/{$correction->id}", [
            'admin_comment' => '確認済み',
        ]);

        $response->assertRedirect("/stamp_correction_request/approve/{$correction->id}");

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '17:30:00',
            'note' => '更新テスト',
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
            'admin_comment' => '確認済み',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' => $attendance->date . ' 12:30:00',
            'break_end' => $attendance->date . ' 13:00:00',
        ]);
    }
}
