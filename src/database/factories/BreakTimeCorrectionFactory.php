<?php

namespace Database\Factories;

use App\Models\BreakTimeCorrection;
use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeCorrectionFactory extends Factory
{
    protected $model = BreakTimeCorrection::class;

    public function definition()
    {
        return [
            'break_time_id' => BreakTime::factory(), // 休憩レコードに紐付ける
            'requested_break_start' => $this->faker->time('H:i', '13:00'),
            'requested_break_end' => $this->faker->time('H:i', '14:00'),
            'request_reason' => $this->faker->sentence(3),
        ];
    }
}
