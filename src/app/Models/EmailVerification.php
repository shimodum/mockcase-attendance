<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'created_at',
    ];

    // タイムスタンプ自動管理はcreated_atのみのため、updated_atは無効にしておく
    public $timestamps = false;

    //ユーザーとのリレーション（N:1）
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
