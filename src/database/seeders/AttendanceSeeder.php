<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\BreakTime;
use Carbon\Carbon;

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

        // 追加：月ごとのダミーデータ（User1）
        $userId = 1;
        $months = ['2025-02', '2025-03', '2025-04', '2025-05'];

        foreach ($months as $month) {
            $startDate = Carbon::parse($month . '-01');
            $endDate = $startDate->copy()->endOfMonth();

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                if ($date->isWeekday()) {
                    $attendance = Attendance::create([
                        'user_id' => $userId,
                        'date' => $date->format('Y-m-d'),
                        'clock_in' => $date->copy()->setTime(9, 0),
                        'clock_out' => $date->copy()->setTime(18, 0),
                        'status' => 'waiting_approval',
                        'note' => 'ダミーデータ',
                    ]);

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => $date->copy()->setTime(12, 0),
                        'break_end' => $date->copy()->setTime(13, 0),
                    ]);
                }
            }
        }
    }
}
