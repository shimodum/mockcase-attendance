<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に表示される情報が正しい()
    {
        // 管理者ユーザーと勤怠情報を作成
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin, 'user')->create([
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
            'note' => 'テスト備考'
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200)
                 ->assertSee('勤怠詳細')
                 ->assertSee($admin->name)
                 ->assertSee('09:00')
                 ->assertSee('18:00')
                 ->assertSee('テスト備考');
    }

    /** @test */
    public function 出勤より退勤が前だとエラーになる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create();

        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => $attendance->date->format('Y-m-d'),
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => '出退勤逆',
            ])
            ->assertSessionHasErrors('clock_out');
    }

    /** @test */
    public function 休憩開始が退勤後ならエラーになる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => $attendance->date->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '19:00', 'break_end' => '19:30'],
                ],
                'note' => '休憩開始が退勤後',
            ])
            ->assertSessionHasErrors('breaks.0.break_start');
    }

    /** @test */
    public function 休憩終了が出勤前ならエラーになる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => $attendance->date->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '07:00', 'break_end' => '08:30'],
                ],
                'note' => '休憩終了が出勤前',
            ])
            ->assertSessionHasErrors('breaks.0.break_end');
    }

    /** @test */
    public function 備考が未入力だとエラーになる()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create();

        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => $attendance->date->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '', // 備考未入力
            ])
            ->assertSessionHasErrors('note');
    }
}
