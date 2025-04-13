<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test 勤怠一覧に自分の勤怠情報が表示されることを確認 */
    public function test_user_can_view_own_attendance_list()
    {
        // ログイン済みユーザーを作成（メール認証済みにしておく）
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        // 勤怠データを2件作成
        Attendance::factory()->count(2)->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->get('/attendance/list'); // 勤怠一覧ページにアクセス
        $response->assertStatus(200); // ステータスコード200（正常）が返ってくるか
        $response->assertViewHas('attendances'); // 変数attendances がビューに渡されているか
    }

    /** @test 現在の月の勤怠情報が表示されることを確認 */
    public function test_current_month_attendance_is_displayed()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        // 今日の日付の勤怠データを作成
        $today = now();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today->format('Y-m-d'),
        ]);

        $response = $this->get('/attendance/list'); // 勤怠一覧にアクセスし、年月が表示されているか確認
        $response->assertSee($today->format('Y/m')); // 表示年月があるか確認
    }

    /** @test 「前月」ボタンを押すと前月の勤怠が表示される */
    public function test_previous_month_attendance_can_be_viewed()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        // 前月の1日に勤怠データを作成
        $prevMonth = now()->subMonth();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevMonth->format('Y-m-01'),
        ]);

        // 前月をクエリパラメータで渡して表示を確認
        $response = $this->get('/attendance/list?month=' . $prevMonth->format('Y-m'));
        $response->assertSee($prevMonth->format('Y/m'));
    }

    /** @test 「翌月」ボタンを押すと翌月の勤怠が表示される */
    public function test_next_month_attendance_can_be_viewed()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        // 翌月の1日に勤怠データを作成
        $nextMonth = now()->addMonth();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->format('Y-m-01'),
        ]);

        // 翌月をクエリパラメータで渡して表示を確認
        $response = $this->get('/attendance/list?month=' . $nextMonth->format('Y-m'));
        $response->assertSee($nextMonth->format('Y/m'));
    }

    /** @test 「詳細」ボタンを押すと勤怠詳細画面に遷移する */
    public function test_clicking_detail_link_redirects_to_detail_page()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        // 勤怠データを作成し、詳細ページにアクセス
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertViewIs('attendance.detail'); // 勤怠詳細画面が表示されていること
    }
}
