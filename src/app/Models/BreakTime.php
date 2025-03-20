<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'break_times';

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    //勤怠情報とのリレーション（N:1）
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    //休憩修正申請とのリレーション（1:N）
    public function breakTimeCorrections()
    {
        return $this->hasMany(BreakTimeCorrection::class);
    }
}
