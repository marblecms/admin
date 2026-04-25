<?php

namespace Marble\Admin\Http\Controllers\Portal;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Marble\Admin\Events\PortalUserLoggedIn;
use Marble\Admin\Events\PortalUserRegistered;
use Marble\Admin\Models\PortalUser;

class PortalAuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::guard('portal')->check()) {
            return redirect(config('marble.portal_home', '/'));
        }

        return view('marble::portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = PortalUser::where('email', $credentials['email'])->first();

        // Always run a hash check to prevent timing-based user enumeration.
        // If user doesn't exist, check against a dummy hash so response time
        // is indistinguishable from a wrong-password attempt.
        $hash  = $user?->password ?? '$2y$10$dummyhashfortimingnormalization.......................';
        $valid = Hash::check($credentials['password'], $hash) && $user && $user->enabled;

        if (!$valid) {
            return back()->withErrors(['email' => trans('marble::portal.invalid_credentials')])->withInput();
        }

        Auth::guard('portal')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        PortalUserLoggedIn::dispatch($user);

        return redirect()->intended(config('marble.portal_home', '/'));
    }

    public function logout(Request $request)
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('marble.portal.login'));
    }

    public function registerForm()
    {
        if (!config('marble.portal_registration', false)) {
            abort(404);
        }

        if (Auth::guard('portal')->check()) {
            return redirect(config('marble.portal_home', '/'));
        }

        return view('marble::portal.register');
    }

    public function register(Request $request)
    {
        if (!config('marble.portal_registration', false)) {
            abort(404);
        }

        $request->validate([
            'email'    => 'required|email|unique:portal_users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = PortalUser::create([
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'enabled'  => true,
        ]);

        Auth::guard('portal')->login($user);
        $request->session()->regenerate();
        PortalUserRegistered::dispatch($user);

        return redirect(config('marble.portal_home', '/'));
    }
}
