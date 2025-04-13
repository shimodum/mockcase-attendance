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

    /**
     * @test
     * 「承認待ち」の修正申請が申請一覧画面に表示されることを確認するテスト
     */
    public function test_waiting_approval_corrections_are_listed()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);

        // 承認待ちの勤怠と修正申請を作成
        $attendance = Attendance::factory()->create(['status' => 'waiting_approval']);
        AttendanceCorrection::factory()->for($attendance)->create(['request_reason' => '承認待ち申請']);

        // 管理者として一覧画面にアクセスし、該当文言が表示されることを確認
        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=waiting_approval');

        $response->assertStatus(200)->assertSee('承認待ち申請');
    }

    /**
     * @test
     * 「承認済み」の修正申請が申請一覧画面に表示されることを確認するテスト
     */
    public function test_approved_corrections_are_listed()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);

        // 承認済みの勤怠と修正申請を作成
        $attendance = Attendance::factory()->create(['status' => 'approved']);
        AttendanceCorrection::factory()->for($attendance)->create(['request_reason' => '承認済み申請']);

        // 管理者として一覧画面にアクセスし、該当文言が表示されることを確認
        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200)->assertSee('承認済み申請');
    }

    /**
     * @test
     * 修正申請の詳細画面に、申請された出勤・退勤・備考が正しく表示されることを確認するテスト
     */
    public function test_correction_detail_is_displayed_correctly()
    {
        // 管理者ユーザーを作成
        $admin = User::factory()->create(['role' => 'admin']);

        // 勤怠データとそれに対応する修正申請データを作成
        $attendance = Attendance::factory()->for($admin)->create();
        $correction = AttendanceCorrection::factory()->for($attendance)->create([
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'request_reason' => '詳細確認テスト',
        ]);

        // 管理者として詳細画面にアクセスし、申請内容が表示されているか確認
        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/{$correction->id}");

        $response->assertStatus(200)->assertSee('09:00')->assertSee('18:00')->assertSee('詳細確認テスト');
    }

    /**
     * @test
     * 管理者が修正申請を承認したとき、勤怠・修正申請・休憩が正しく更新されることを確認するテスト
     */
    public function test_correction_approval_updates_attendance()
    {
        // 管理者ユーザー作成
        $admin = User::factory()->create(['role' => 'admin']);

        // 勤怠と修正申請を作成
        $attendance = Attendance::factory()->create();
        $correction = AttendanceCorrection::factory()->for($attendance)->create([
            'requested_clock_in' => '09:30',
            'requested_clock_out' => '17:30',
            'request_reason' => '更新テスト',
        ]);

        // 休憩とその修正申請を作成
        BreakTime::factory()->for($attendance)->create();
        BreakTimeCorrection::factory()->for($attendance->breakTimes->first())->create([
            'requested_break_start' => '12:30',
            'requested_break_end' => '13:00',
        ]);

        // 承認処理を実行（管理者コメント付き）
        $response = $this->actingAs($admin)->post("/stamp_correction_request/approve/{$correction->id}", [
            'admin_comment' => '確認済み',
        ]);

        // 正常にリダイレクトされることを確認
        $response->assertRedirect("/stamp_correction_request/approve/{$correction->id}");

        // 勤怠テーブルが修正申請の内容に更新されたか確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '17:30:00',
            'note' => '更新テスト',
            'status' => 'approved',
        ]);

        // 勤怠修正申請テーブルのステータスと管理者コメントを確認
        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => 'approved',
            'admin_comment' => '確認済み',
        ]);

        // 休憩時間も修正申請内容で更新されていることを確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start' => '12:30:00',
            'break_end' => '13:00:00',
        ]);
    }
}
