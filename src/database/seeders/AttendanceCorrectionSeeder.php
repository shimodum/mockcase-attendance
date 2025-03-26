<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\Carbon;

class AttendanceCorrectionSeeder extends Seeder
{
    public function run()
    {
        // 承認待ち用：attendances.status = 'waiting_approval'
        $waitingAttendances = Attendance::where('status', 'waiting_approval')->limit(5)->get();

        foreach ($waitingAttendances as $attendance) {
            AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'requested_clock_in' => Carbon::parse($attendance->clock_in)->addMinutes(5),
                'requested_clock_out' => Carbon::parse($attendance->clock_out)->subMinutes(5),
                'request_reason' => '遅延のため',
            ]);
        }

        // 承認済み用：attendances.status = 'approved'
        $approvedAttendances = Attendance::where('status', 'approved')->limit(5)->get();

        foreach ($approvedAttendances as $attendance) {
            AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'requested_clock_in' => Carbon::parse($attendance->clock_in)->addMinutes(3),
                'requested_clock_out' => Carbon::parse($attendance->clock_out)->subMinutes(2),
                'request_reason' => '私用による変更',
            ]);
        }
    }
}
