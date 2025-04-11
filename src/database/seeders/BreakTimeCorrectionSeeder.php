<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use App\Models\BreakTimeCorrection;
use Carbon\Carbon;

class BreakTimeCorrectionSeeder extends Seeder
{
    public function run()
    {
        $breakTimes = BreakTime::all();

        foreach ($breakTimes as $break) {
            // 元の休憩時間が存在するものだけ対象
            if ($break->break_start && $break->break_end) {
                $originalStart = Carbon::parse($break->break_start);
                $originalEnd = Carbon::parse($break->break_end);

                // 少しずらした時間で申請内容を生成
                $requestedStart = $originalStart->copy()->subMinutes(rand(1, 10));
                $requestedEnd = $originalEnd->copy()->addMinutes(rand(1, 10));

                BreakTimeCorrection::create([
                    'break_time_id' => $break->id,
                    'requested_break_start' => $requestedStart,
                    'requested_break_end' => $requestedEnd,
                    'request_reason' => '業務対応により少しズレました',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

