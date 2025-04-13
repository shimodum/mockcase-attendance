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
        // ユーザーと、そのユーザーの勤怠レコードを作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        // ログインして、修正申請をPOST送信（退勤時間が出勤時間よりも前の値）
        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => '修正',
            ])
            ->assertSessionHasErrors('clock_out'); // バリデーションエラーが「clock_out」に対して出ることを確認
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
                'break_start' => '08:00', // 休憩が勤務開始前の時間
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
                'note' => '', // 備考が未入力
            ])
            ->assertSessionHasErrors('note');
    }

    /** @test */
    public function 修正申請が保存される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();
        BreakTime::factory()->for($attendance)->create(); // 休憩1件を先に作成

        // ログイン状態で修正申請を送信
        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => '12:00',
                'break_end' => '13:00',
                'note' => '修正内容',
            ])
            ->assertRedirect("/attendance/{$attendance->id}");

        // 勤怠修正内容がデータベースに記録されているか確認
        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $attendance->id,
            'requested_clock_in' => '09:00:00',
            'requested_clock_out' => '18:00:00',
            'request_reason' => '修正内容',
        ]);

        // 休憩の修正内容もデータベースに保存されたか確認
        $this->assertDatabaseHas('break_time_corrections', [
            'requested_break_start' => '12:00:00',
            'requested_break_end' => '13:00:00',
        ]);
    }

    /** @test */
    public function 修正申請一覧に自分の申請が表示される()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->for($user)->create([
            'status' => 'waiting_approval',
            'clock_out' => now(), //
        ]);

        AttendanceCorrection::factory()->for($attendance)->create([
            'request_reason' => 'テスト申請',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=waiting_approval');
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
