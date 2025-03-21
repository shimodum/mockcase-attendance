<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceApproval;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザー1の勤怠
        Attendance::create([
            'user_id' => 1,
            'date' => '2023-06-01',
            'clock_in' => '2023-06-01 09:00:00',
            'clock_out' => '2023-06-01 18:00:00',
            'total_hours' => 8.00,
            'status' => 'waiting_approval',
            'note' => '電車遅延のため',
        ]);

        // 一般ユーザー2の勤怠
        Attendance::create([
            'user_id' => 2,
            'date' => '2023-06-01',
            'clock_in' => '2023-06-01 08:30:00',
            'clock_out' => '2023-06-01 17:30:00',
            'total_hours' => 8.00,
            'status' => 'approved',
            'note' => null,
        ]);

        // 管理者による承認コメントも追加（attendance_id=2に対して）
        AttendanceApproval::create([
            'attendance_id' => 2,
            'admin_id' => 3,
            'is_approved' => true,
            'admin_comment' => '問題なし',
        ]);
    }
}
