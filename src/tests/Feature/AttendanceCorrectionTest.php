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
            ->assertSessionHasErrors('clock_out'); // clock_out にバリデーションエラーが発生することを確認
    }

    /** @test */
    public function 休憩開始より休憩終了が前だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        // ログイン状態で、休憩終了が開始より前の値を送る
        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '16:00', 'break_end' => '15:00'],
                ],
                'note' => '修正',
            ])
            ->assertSessionHasErrors('breaks.0.break_end'); // break_end にバリデーションエラーが出ることを確認
    }

    /** @test */
    public function 休憩が勤務時間外ならエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        // 休憩開始が出勤前の時間になっているケース
        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '08:00', 'break_end' => '09:30'], // 勤務前に休憩開始（不正）
                ],
                'note' => '修正',
            ])
            ->assertSessionHasErrors('breaks.0.break_start'); // break_start にバリデーションエラーが出ることを確認
    }

    /** @test */
    public function 備考が未入力だとエラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        // 備考を空にして修正申請を送る
        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '', // 備考が空
            ])
            ->assertSessionHasErrors('note'); // note にバリデーションエラーが出ることを確認
    }

    /** @test */
    public function 修正申請が保存される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();
        BreakTime::factory()->for($attendance)->create(); // 休憩レコードを事前に1件作っておく（修正申請の対象になる）

        // ログイン状態で正常な修正内容をPOST送信
        $this->actingAs($user)
            ->post("/attendance/{$attendance->id}/correction_request", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '12:00', 'break_end' => '13:00'],
                ],
                'note' => '修正内容',
            ])
            ->assertRedirect("/attendance/{$attendance->id}"); // 修正申請後は勤怠詳細画面へリダイレクトされることを確認

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

        // 自分の勤怠データを「修正申請中」に設定して作成
        $attendance = Attendance::factory()->for($user)->create([
            'status' => 'waiting_approval',
            'clock_out' => now(),
        ]);

        // 申請理由付きで修正申請データを作成
        AttendanceCorrection::factory()->for($attendance)->create([
            'request_reason' => 'テスト申請',
        ]);

        // ログインして一覧ページにアクセスし、テスト申請が画面に表示されることを確認
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=waiting_approval');
        $response->assertSee('テスト申請');
    }

    /** @test */
    public function 承認済みの申請が一覧に表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->for($user)->create();

        // 承認済みの修正申請データを作成
        AttendanceCorrection::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertSee('承認済み');
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
