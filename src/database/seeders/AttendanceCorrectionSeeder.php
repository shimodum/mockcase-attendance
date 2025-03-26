<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Carbon\Carbon;

class AttendanceCorrectionSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            // 承認待ち用の勤怠を3件取得
            $waitingAttendances = Attendance::where('user_id', $user->id)
                ->where('status', 'waiting_approval')
                ->inRandomOrder()
                ->take(3)
                ->get();

            foreach ($waitingAttendances as $attendance) {
                AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'request_reason' => '電車遅延のため',
                    'requested_clock_in' => $attendance->clock_in
                        ? Carbon::parse($attendance->clock_in)->subMinutes(5)
                        : null,
                    'requested_clock_out' => $attendance->clock_out
                        ? Carbon::parse($attendance->clock_out)->addMinutes(10)
                        : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 承認済み用の勤怠を3件取得（勤怠ステータスを approved に事前に更新）
            $approvedAttendances = Attendance::where('user_id', $user->id)
                ->inRandomOrder()
                ->take(3)
                ->get();

            foreach ($approvedAttendances as $attendance) {
                // 勤怠データのステータスを approved に更新
                $attendance->update(['status' => 'approved']);

                AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'request_reason' => '退勤時間に誤りがあったため',
                    'requested_clock_in' => $attendance->clock_in,
                    'requested_clock_out' => $attendance->clock_out
                        ? Carbon::parse($attendance->clock_out)->addMinutes(15)
                        : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
