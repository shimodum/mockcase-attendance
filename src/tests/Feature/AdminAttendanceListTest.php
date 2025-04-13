<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    private $admin; // 管理者ユーザーを格納するプロパティ

    // 各テストの前に実行される初期設定
    protected function setUp(): void
    {
        parent::setUp();
        // 管理者ユーザー作成
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function 当日の勤怠情報が正しく表示される()
    {
        $user = User::factory()->create(); // 一般ユーザーを作成
        $today = Carbon::today()->toDateString(); // 今日の日付を取得（文字列）

        // 出勤・退勤時間を含む勤怠情報を作成
        Attendance::factory()->for($user)->create([
            'date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 18:00:00',
        ]);

        // 管理者としてアクセスして勤怠一覧画面に移動
        $this->actingAs($this->admin)
            ->get('/admin/attendance/list')
            ->assertSee($user->name) // ユーザー名が表示されているか
            ->assertSee('09:00') // 出勤時間が表示されているか
            ->assertSee('18:00'); // 退勤時間が表示されているか
    }

    /** @test */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        $user = User::factory()->create();
        $prevDate = Carbon::yesterday()->toDateString(); // 昨日の日付を取得

        Attendance::factory()->for($user)->create([
            'date' => $prevDate,
            'clock_in' => $prevDate . ' 10:00:00',
            'clock_out' => $prevDate . ' 19:00:00',
        ]);

        // 管理者として前日指定でアクセス
        $this->actingAs($this->admin)
            ->get('/admin/attendance/list?date=' . $prevDate)
            ->assertSee($user->name)
            ->assertSee('10:00')
            ->assertSee('19:00');
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        $user = User::factory()->create();
        $nextDate = Carbon::tomorrow()->toDateString(); // 明日の日付を取得

        Attendance::factory()->for($user)->create([
            'date' => $nextDate,
            'clock_in' => $nextDate . ' 08:30:00',
            'clock_out' => $nextDate . ' 17:30:00',
        ]);

        // 管理者として翌日指定でアクセス
        $this->actingAs($this->admin)
            ->get('/admin/attendance/list?date=' . $nextDate)
            ->assertSee($user->name)
            ->assertSee('08:30')
            ->assertSee('17:30');
    }
}
