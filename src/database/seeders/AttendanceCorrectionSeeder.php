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
            // 承認待ち（申請中）の勤怠を3件取得
            $unconfirmedAttendances = Attendance::where('user_id', $user->id)
                ->where('status', 'unconfirmed')
                ->doesntHave('correction')
                ->inRandomOrder()
                ->take(3)
                ->get();

            foreach ($unconfirmedAttendances as $attendance) {
                $attendance->update(['status' => 'waiting_approval']);

                AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'request_reason' => '電車遅延のため',
                    'requested_clock_in' => $attendance->clock_in
                        ? Carbon::parse($attendance->clock_in)->subMinutes(5)
                        : null,
                    'requested_clock_out' => $attendance->clock_out
                        ? Carbon::parse($attendance->clock_out)->addMinutes(10)
                        : null,
                    'status' => 'waiting_approval',
                    'admin_comment' => null, //
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // 承認済み：未申請の approved 勤怠を選定し、Correction追加
            $approvedAttendances = Attendance::where('user_id', $user->id)
                ->where('status', 'unconfirmed')
                ->doesntHave('correction')
                ->inRandomOrder()
                ->take(3)
                ->get();

            foreach ($approvedAttendances as $attendance) {
                $attendance->update(['status' => 'approved']);

                AttendanceCorrection::create([
                    'attendance_id' => $attendance->id,
                    'request_reason' => '退勤時間に誤りがあったため',
                    'requested_clock_in' => $attendance->clock_in,
                    'requested_clock_out' => $attendance->clock_out
                        ? Carbon::parse($attendance->clock_out)->addMinutes(15)
                        : null,
                    'status' => 'approved',
                    'admin_comment' => '承認済みのテストデータです',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
