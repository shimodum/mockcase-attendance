<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test 出勤ボタンを押すと出勤レコードが作成されるテスト */
    public function user_can_clock_in_once_per_day()
    {
        // ログインユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 現在日時を固定（Carbon::now() が使われているため）
        Carbon::setTestNow(Carbon::parse('2025-04-13 09:00:00'));

        // 出勤処理を実行
        $response = $this->actingAs($user)->post('/attendance');

        // 出勤レコードが作成されているか確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date' => '2025-04-13',
            'status' => 'unconfirmed',
        ]);

        $attendance = Attendance::first();
        $this->assertEquals('09:00:00', Carbon::parse($attendance->clock_in)->format('H:i:s'));

        // 正常リダイレクトされることを確認
        $response->assertRedirect('/attendance');
    }

    /** @test 同日に2回出勤すると2回目は記録されないテスト */
    public function user_cannot_clock_in_twice_on_same_day()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        Carbon::setTestNow(Carbon::parse('2025-04-13 09:00:00'));

        // 1回目の出勤
        $this->actingAs($user)->post('/attendance');

        // 2回目の出勤（同じ日）
        $this->actingAs($user)->post('/attendance');

        // 出勤レコードは1件のみであること
        $this->assertEquals(1, Attendance::count());
    }

    /** @test 未ログインユーザーは出勤できないテスト */
    public function guest_cannot_clock_in()
    {
        Carbon::setTestNow(Carbon::parse('2025-04-13 09:00:00'));

        // 未ログインで出勤を試みる
        $response = $this->post('/attendance');

        // 認証画面へリダイレクトされる（ミドルウェア確認）
        $response->assertRedirect('/login');

        // 出勤レコードは作成されない
        $this->assertDatabaseMissing('attendances', [
            'date' => '2025-04-13'
        ]);
    }
}
