<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 特に登録処理は不要な場合は空のままでOK
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 会員登録（ユーザー作成）時の処理クラスを登録
        Fortify::createUsersUsing(CreateNewUser::class);

        // 会員登録画面を表示するviewファイルを指定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ログイン画面を表示するviewファイルを指定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // ログイン時のリクエスト回数制限（1分間に最大10回）
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // Fortifyのログイン処理をカスタマイズ
        // email_verified_at（メール認証が完了しているか）を確認してからログイン成功させる
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (
                $user &&
                Hash::check($request->password, $user->password) && // パスワード一致
                ! is_null($user->email_verified_at)                // メール認証済み
            ) {
                return $user; // 認証成功
            }

            return null; // 認証失敗（→ログイン画面に戻る）
        });
    }
}
