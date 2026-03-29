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
            $request->session()->regenerate();

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
