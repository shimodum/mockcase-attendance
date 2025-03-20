<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'break_time_id',
        'requested_break_start',
        'requested_break_end',
        'request_reason',
    ];

    //休憩情報とのリレーション（N:1）
    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }
}

