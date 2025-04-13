<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function スタッフの氏名とメールアドレスが表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']); // 管理者ユーザーを作成（role = admin）
        $user = User::factory()->create(); // 一般ユーザー（スタッフ）を作成

        // 管理者として対象のスタッフ詳細ページにアクセス
        $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}") // 指定したスタッフの勤怠一覧にアクセス
            ->assertStatus(200) // ステータスコード200（正常表示）を確認できるか
            ->assertSee($user->name) // スタッフの名前が表示されているか
            ->assertSee($user->email); // スタッフのメールアドレスが表示されているか
    }

    /** @test */
    public function 今月の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']); // 管理者を作成
        $user = User::factory()->create(); // スタッフを作成

        // 今月の任意の日付（5日目）を作成
        $today = Carbon::now()->startOfMonth()->addDays(5);

        // 今日の日付で勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today,
        ]);

        // 管理者として該当月の勤怠一覧ページにアクセス
        $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?month=" . $today->format('Y-m'))
            ->assertStatus(200) // ステータスコード200（正常表示）を確認できるか
            ->assertSee($today->format('Y/m/d')); // 作成した日付が表示されているか確認
    }

    /** @test */
    public function 前月の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']); // 管理者を作成
        $user = User::factory()->create(); // スタッフを作成

        // 前月の11日目の日付を作成
        $lastMonth = Carbon::now()->subMonth()->startOfMonth()->addDays(10);

        // 勤怠データを前月の日付で作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $lastMonth,
        ]);

        // 管理者が前月の勤怠一覧ページにアクセス
        $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?month=" . $lastMonth->format('Y-m')) // 前月を指定
            ->assertStatus(200) // ステータスコード200（正常表示）を確認できるか
            ->assertSee($lastMonth->format('Y/m/d')); // 該当日が表示されているか
    }

    /** @test */
    public function 翌月の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']); // 管理者を作成
        $user = User::factory()->create(); // スタッフをを作成

        // 翌月の3日目の日付を作成
        $nextMonth = Carbon::now()->addMonth()->startOfMonth()->addDays(2);

        // 勤怠データを翌月の日付で作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth,
        ]);

        $this->actingAs($admin)
            ->get("/admin/attendance/staff/{$user->id}?month=" . $nextMonth->format('Y-m')) // 翌月を指定
            ->assertStatus(200) // ステータスコード200（正常表示）を確認できるか
            ->assertSee($nextMonth->format('Y/m/d')); // 該当日が表示されているか
    }

    /** @test */
    public function 詳細ボタンから勤怠詳細画面に遷移できる()
    {
        $admin = User::factory()->create(['role' => 'admin']); // 管理者を作成
        $user = User::factory()->create(); // スタッフを作成

        // スタッフの勤怠データを作成
        $attendance = Attendance::factory()->for($user)->create();

        // 管理者が詳細ページにアクセス
        $this->actingAs($admin)
            ->get("/admin/attendance/{$attendance->id}")  // 勤怠詳細画面にアクセス
            ->assertStatus(200) // ステータスコード200（正常表示）を確認できるか
            ->assertSee('勤怠詳細'); // 画面に「勤怠詳細」が表示されているか確認
    }
}
