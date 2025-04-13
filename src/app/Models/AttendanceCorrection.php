<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requested_clock_in',
        'requested_clock_out',
        'request_reason',
        'status',
        'admin_comment',
    ];

    //勤怠情報とのリレーション（N:1）
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
