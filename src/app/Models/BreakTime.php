<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    // モデル名が BreakTime でも、テーブル名が breaks であることを明示（Laravelの規約と異なる場合）
    protected $table = 'breaks';

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
}
