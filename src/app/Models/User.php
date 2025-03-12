<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //勤怠情報とのリレーション（1:N）
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    //勤怠承認とのリレーション（1:N）
    public function attendanceApprovals()
    {
        return $this->hasMany(AttendanceApproval::class, 'admin_id');
    }

    //勤怠修正申請とのリレーション（1:N）
    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    //メール認証とのリレーション（1:N）※メール認証の履歴が複数ある可能性を想定で、hasMany
    public function emailVerifications()
    {
        return $this->hasMany(EmailVerification::class);
    }

    //ロール権限設定とのリレーション（1:N）
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'role', 'role');
    }
}
