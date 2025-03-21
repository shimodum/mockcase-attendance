<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ▼ general1の勤怠データ（備考付き）
        $attendance1 = Attendance::create([
            'user_id' => 1, // general1
            'date' => '2023-06-01',
            'clock_in' => '2023-06-01 09:00:00',
            'clock_out' => '2023-06-01 18:00:00',
            'status' => 'waiting_approval',
            'note' => '電車遅延のため', // 備考
        ]);

        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => '2023-06-01 12:00:00',
            'break_end'   => '2023-06-01 13:00:00',
        ]);

        // ▼ general2の勤怠データ（備考なし）
        $attendance2 = Attendance::create([
            'user_id' => 2, // general2
            'date' => '2023-06-01',
            'clock_in' => '2023-06-01 10:00:00',
            'clock_out' => '2023-06-01 19:00:00',
            'status' => 'approved', // 承認済みにしてみる例
            'note' => null,
        ]);

        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => '2023-06-01 13:00:00',
            'break_end'   => '2023-06-01 14:00:00',
        ]);
    }
}
