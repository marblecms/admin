<?php

namespace Marble\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('marble::auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('marble')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('marble')->user();

            if (!$user->active) {
                Auth::guard('marble')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => trans('marble::admin.account_disabled')]);
            }

            if ($user->two_factor_enabled) {
                Auth::guard('marble')->logout();
                $request->session()->put('marble_2fa_user_id', $user->id);
                $request->session()->put('marble_2fa_remember', $request->boolean('remember'));
                return redirect()->route('marble.two-factor');
            }

            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);

            return redirect()->route('marble.dashboard');
        }

        return back()->withErrors(['email' => __('auth.failed')]);
    }

    public function logout(Request $request)
    {
        Auth::guard('marble')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('marble.login'));
    }
}
