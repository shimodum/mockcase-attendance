<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_hours',
        'status',
    ];

    //ユーザーとのリレーション（N:1）
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //休憩情報とのリレーション（1:N）
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    //勤怠修正申請とのリレーション（1:N）
    public function attendanceCorrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    //勤怠承認とのリレーション（1:N）
    public function attendanceApprovals()
    {
        return $this->hasMany(AttendanceApproval::class);
    }
}
