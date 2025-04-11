<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // 対象ユーザー
        $users = User::whereIn('email', [
            'general1@example.com',
            'general2@example.com',
        ])->get();

        // 「ユーザー別・null日付設定一覧」
        $nullSettings = [
            'general1@example.com' => [
                '2025-03' => [
                    'clock_in_null' => [5, 20],      // 3/5, 3/20 → 出勤なし
                    'clock_out_null' => [10, 21],    // 3/10, 3/21 → 退勤なし
                    'both_null'      => [15],        // 3/15 → 両方なし
                ],
                '2025-04' => [
                    'clock_in_null' => [3],
                    'clock_out_null' => [12],
                    'both_null' => [25],
                ],
                '2025-05' => [
                    'clock_in_null' => [1, 3],
                    'clock_out_null' => [8, 9],
                    'both_null' => [15],
                ],
            ],
            'general2@example.com' => [
                '2025-03' => [
                    'clock_in_null' => [8],
                    'clock_out_null' => [18],
                    'both_null' => [28],
                ],
                '2025-04' => [
                    'clock_in_null' => [7],
                    'clock_out_null' => [14],
                    'both_null' => [20],
                ],
                '2025-05' => [
                    'clock_in_null' => [1, 3],
                    'clock_out_null' => [7, 11],
                    'both_null' => [18],
                ]
            ],
        ];

        // 勤怠データ作成対象期間：2025年2月〜5月
        $startMonth = Carbon::create(2025, 2, 1);
        $endMonth = Carbon::create(2025, 5, 1);

        foreach ($users as $user) {
            $month = $startMonth->copy();

            while ($month <= $endMonth) {
                $daysInMonth = $month->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = $month->copy()->day($day);
                    $dateStr = $date->format('Y-m-d');
                    $monthStr = $date->format('Y-m');

                    // 基本出勤・退勤時刻
                    $clockIn = $date->copy()->setTime(9, 0);
                    $clockOut = $date->copy()->setTime(18, 0);

                    // ユーザー別に時間ズレを追加
                    if ($user->email === 'general1@example.com') {
                        $clockIn->addMinutes(rand(-10, 10));
                        $clockOut->addMinutes(rand(-10, 10));
                    } elseif ($user->email === 'general2@example.com') {
                        $clockIn->addMinutes(rand(-20, 5));
                        $clockOut->addMinutes(rand(0, 15));
                    }

                    // null適用処理
                    $userSettings = $nullSettings[$user->email][$monthStr] ?? [];

                    if (isset($userSettings['both_null']) && in_array($day, $userSettings['both_null'])) {
                        $clockIn = null;
                        $clockOut = null;
                    } elseif (isset($userSettings['clock_in_null']) && in_array($day, $userSettings['clock_in_null'])) {
                        $clockIn = null;
                    } elseif (isset($userSettings['clock_out_null']) && in_array($day, $userSettings['clock_out_null'])) {
                        $clockOut = null;
                    }

                    Attendance::create([
                        'user_id'   => $user->id,
                        'date'      => $dateStr,
                        'clock_in'  => $clockIn,
                        'clock_out' => $clockOut,
                        'status'    => 'unconfirmed',
                        'note' => '電車遅延のため',
                    ]);
                }

                $month->addMonth();
            }
        }
    }
}
