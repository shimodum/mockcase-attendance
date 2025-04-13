<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DateTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    /** @test 出勤前の画面で「現在の日付と時刻」が正しい形式で表示されているかを確認するテスト */
    public function it_displays_current_datetime_in_expected_format()
    {
        // テスト用に現在時刻を固定（2025年4月13日 09時30分）
        Carbon::setTestNow(Carbon::create(2025, 4, 13, 9, 30));

        $user = User::factory()->create(); // ユーザーを1人作成し、ログイン状態にする
        $this->actingAs($user); // テストでログイン状態を再現

        // 勤怠登録画面（出勤前）にアクセス
        $response = $this->get('/attendance');

        $response->assertSee('2025年4月13日（日）'); // 画面に「2025年4月13日（日）」という日付が表示されていることを確認
        $response->assertSee('09:30'); // 画面に「09:30」という時刻が表示されていることを確認
    }
}
