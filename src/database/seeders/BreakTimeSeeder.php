<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);

                // 休憩回数：1～3回でランダム
                $breakCount = rand(1, 3);
                $currentStart = $clockIn->copy()->addHour(); // 初回の休憩開始目安

                for ($i = 0; $i < $breakCount; $i++) {
                    // 休憩開始：前回終了から30～90分後
                    $breakStart = $currentStart->copy()->addMinutes(rand(30, 90));
                    $breakDuration = rand(15, 60); // 休憩時間：15～60分
                    $breakEnd = $breakStart->copy()->addMinutes($breakDuration);

                    // 勤務時間を超えたら休憩スキップ
                    if ($breakStart->gte($clockOut) || $breakEnd->gt($clockOut)) {
                        break;
                    }

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $breakStart,
                        'break_end'     => $breakEnd,
                    ]);

                    $currentStart = $breakEnd->copy(); // 次回の開始目安更新
                }
            }
        }
    }
}
