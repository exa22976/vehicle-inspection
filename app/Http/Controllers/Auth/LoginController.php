<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * ログイン画面を表示
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function login(Request $request)
    {
        // ★★★★★ トラブルシューティング用コード ★★★★★
        // ログインボタンが押されたら、まずこのメッセージが表示されるかを確認します。
        // この画面が表示されたら、その旨を教えてください。
        dd('LoginControllerのloginメソッドが実行されました。');
        // ★★★★★ ここまで ★★★★★


        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $credentials['email'])->where('is_admin', true)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => '指定されたメールアドレスは管理者として登録されていません。',
        ])->onlyInput('email');
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
