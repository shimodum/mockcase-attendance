<?php

namespace Database\Factories;

use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceCorrectionFactory extends Factory
{
    protected $model = AttendanceCorrection::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(), // 出勤情報に紐付ける
            'requested_clock_in' => $this->faker->time('H:i', '10:00'), // faker を使って 10:00 までの時間をランダムに生成
            'requested_clock_out' => $this->faker->time('H:i', '19:00'),
            'request_reason' => $this->faker->sentence(3),
        ];
    }
}
