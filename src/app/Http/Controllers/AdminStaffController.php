<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    // スタッフ一覧の表示（管理者）
    public function index()
    {
        // 一般ユーザーのみ取得（管理者は除外）
        $users = User::where('role', 'user')->get();

        return view('admin.staff.list', compact('users'));
    }
}
