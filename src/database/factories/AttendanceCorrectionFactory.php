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
            'attendance_id' => Attendance::factory(),
            'requested_clock_in' => $this->faker->time('H:i', '10:00'),
            'requested_clock_out' => $this->faker->time('H:i', '19:00'),
            'request_reason' => $this->faker->sentence(3),
            'status' => 'waiting_approval',
            'admin_comment' => null,
        ];
    }

    /**
     * 状態：承認済み
     */
    public function approved()
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'admin_comment' => '承認済みのテストコメント',
        ]);
    }
}
