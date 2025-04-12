<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        $start = Carbon::createFromTime(12, 0); // 休憩の開始時間：12時00分
        $end = (clone $start)->addMinutes(60); // 休憩の終了時間：開始から60分後

        return [
            // 関連する勤怠レコードも一緒に作る
            'attendance_id' => Attendance::factory(),
            'break_start' => $start,
            'break_end' => $end,
        ];
    }
}
