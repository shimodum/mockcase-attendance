<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //ログイン画面表示
    public function showForm()
    {
        return view('auth.login');
    }

    //ログイン処理
    public function authenticate(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 管理者と一般ユーザーでリダイレクト先を切り替える
            if ($user->role === 'admin') {
                return redirect('/admin/attendance/list'); // 管理者 → 勤怠一覧
            }

            return redirect('/attendance'); // 一般ユーザー → 勤怠登録（出勤前）
        }

        // 認証失敗時
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }

}