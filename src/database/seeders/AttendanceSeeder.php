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

        // 勤怠データ作成対象期間：2025年2月〜5月
        $startMonth = Carbon::create(2025, 2, 1);
        $endMonth = Carbon::create(2025, 5, 1);

        foreach ($users as $user) {
            $month = $startMonth->copy();

            while ($month <= $endMonth) {
                $daysInMonth = $month->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = $month->copy()->day($day);

                    // 土日除外
                    if ($date->isWeekend()) continue;

                    // 出勤・退勤時間をユーザー別に少し変化させる
                    $clockIn = $date->copy()->setTime(9, 0);
                    $clockOut = $date->copy()->setTime(18, 0);

                    // ユーザーごとのズレ設定（例として分単位）
                    if ($user->email === 'general1@example.com') {
                        $clockIn->addMinutes(rand(-10, 10)); // ±10分
                        $clockOut->addMinutes(rand(-10, 10));
                    } elseif ($user->email === 'general2@example.com') {
                        $clockIn->addMinutes(rand(-20, 5));  // やや遅れがち
                        $clockOut->addMinutes(rand(0, 15));  // やや早退しがち
                    }

                    Attendance::create([
                        'user_id'   => $user->id,
                        'date'      => $date->format('Y-m-d'),
                        'clock_in'  => $clockIn,
                        'clock_out' => $clockOut,
                        'status'    => 'waiting_approval',
                    ]);
                }

                $month->addMonth();
            }
        }
    }
}
