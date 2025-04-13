<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //ログイン画面表示
    //現在のURLが「admin/〜」であれば管理者用ビュー、それ以外は一般ユーザー用ビューを表示する
    public function showForm(Request $request)
    {
        $isAdmin = $request->is('admin/*');
        return view($isAdmin ? 'admin.auth.login' : 'auth.login');
    }

    //ログイン処理
    //ユーザーのロール（一般ユーザー or 管理者）に応じてログイン後の動きを切り分ける
    public function authenticate(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 認証を試みる（入力情報と一致するユーザーがいればログイン成功）
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 管理者ページにアクセスしようとしているが、管理者ではない場合はログアウト
            if ($request->is('admin/*') && !$user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => '管理者アカウントではありません']);
            }

            // 一般ユーザー用ログインURLで、管理者がログインしようとした場合もエラー
            if ($request->is('login') && $user->isAdmin()) {
                Auth::logout();
                return back()->withErrors(['email' => '一般ユーザーアカウントではありません']);
            }

            // 正常にログインできたら、ロールに応じて明示的にリダイレクト
            if ($user->isAdmin()) {
                return redirect()->route('admin.attendance.list'); // 管理者トップページへ
            } else {
                return redirect()->route('attendance'); // 一般ユーザーの出勤前画面へ
            }
        }

        // バリデーション済みならこのエラーは出してOK
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput(); // 入力値も保持
    }

    //ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout(); // 現在ログイン中のユーザー情報を削除
        $request->session()->invalidate(); // セッション無効化
        $request->session()->regenerateToken(); // CSRFトークン再生成

        // ロールに応じた遷移先にリダイレクト
        $isAdmin = $request->is('admin/*');
        return redirect($isAdmin ? '/admin/login' : '/login');
    }
}
