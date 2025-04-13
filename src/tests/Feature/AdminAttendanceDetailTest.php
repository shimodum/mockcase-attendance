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

        // 管理者として勤怠詳細ページにアクセス
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
        // 管理者ユーザーと出勤データ作成
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create();

        // 管理者として勤務修正フォームを送信（出勤が18時・退勤が9時 → 不正）
        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'),
                'clock_in' => '18:00',
                'clock_out' => '09:00',
                'note' => '出退勤逆',
            ])
            ->assertSessionHasErrors('clock_out'); // clock_out にエラーがあることを確認
    }

    /** @test */
    public function 休憩開始が退勤後ならエラーになる()
    {
        // 管理者ユーザーと出勤データ作成（09:00〜18:00勤務）
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 勤務時間外の19:00〜19:30を休憩として登録 → 不正
        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'),
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
        // 管理者ユーザーと出勤データ作成
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 勤務前の07:00〜08:30を休憩として登録 → 不正
        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'),
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
        // 管理者ユーザーと出勤データ作成
        $admin = User::factory()->create(['role' => 'admin']);
        $attendance = Attendance::factory()->for($admin)->create();

        // 備考が空欄のまま送信 → バリデーションエラーが出る
        $this->actingAs($admin)
            ->put("/admin/attendance/{$attendance->id}", [
                'date' => \Carbon\Carbon::parse($attendance->date)->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '', // 備考未入力
            ])
            ->assertSessionHasErrors('note'); // 備考のバリデーションエラーを確認
    }
}
