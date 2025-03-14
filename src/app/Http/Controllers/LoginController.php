<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //ログイン画面表示
    //現在のURLが「admin/〜」であれば管理者用ビュー、それ以外は一般ユーザー用ビューを表示する
    public function showForm()
    {
        $isAdmin = request()->is('admin/*');
        return view($isAdmin ? 'admin.auth.login' : 'auth.login');
    }

    //ログイン処理
    //ロールごとにログイン後の遷移先を変更
    //redirect()->intended() により、ログイン前にアクセスしようとしたURLがあればそこに戻る
    public function authenticate(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 管理者と一般ユーザーで遷移先を分岐（ログイン成功時：管理者 → 管理者勤怠一覧、一般ユーザー → 出勤画面へリダイレクト）
            $redirectPath = ($user->role === 'admin') ? '/admin/attendance/list' : '/attendance';
            return redirect()->intended($redirectPath);
        }

        // 認証失敗時：エラーメッセージ表示
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }

}