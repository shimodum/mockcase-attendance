<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test 勤務中ユーザーが退勤できるかを確認するテスト */
    public function user_can_clock_out()
    {
        // ユーザーを作成してログイン（勤務中とする）
        $user = User::factory()->create();
        $this->actingAs($user);

        // 今日の日付で「出勤済み・未退勤」の勤怠レコードを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => now()->subHours(8), // 8時間前に出勤
            'clock_out' => null, // まだ退勤していない
        ]);

        // 「退勤」処理（POSTリクエスト）を送信
        $response = $this->post('/attendance/clockout');

        // 勤怠登録画面（/attendance）にリダイレクトされることを確認
        $response->assertRedirect('/attendance');

        // DB上で、該当の勤怠レコードに退勤時間（clock_out）が保存されているか確認
        $this->assertNotNull($attendance->fresh()->clock_out);
    }

    /** @test 未ログインのユーザーは退勤処理ができず、ログイン画面にリダイレクトされることを確認する */
    public function guest_cannot_clock_out()
    {
        // ログインしていない状態で退勤リクエストを送信
        $response = $this->post('/attendance/clockout');

        // 未認証のため、ログイン画面へリダイレクトされることを確認
        $response->assertRedirect('/login');
    }
}
