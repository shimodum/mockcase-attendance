<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // 自動でユーザーも一緒に作る
            'date' => now()->toDateString(), // 今日の日付を設定
            'clock_in' => null,
            'clock_out' => null,
            'total_hours' => null,
            'status' => 'unconfirmed',
            'note' => null,
        ];
    }
}
