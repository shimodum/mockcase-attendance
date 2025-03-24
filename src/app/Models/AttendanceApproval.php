<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'admin_id',
        'is_approved',
        'admin_comment',
    ];

    //勤怠情報とのリレーション（N:1）
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    
    //管理者ユーザーとのリレーション（N:1）
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
