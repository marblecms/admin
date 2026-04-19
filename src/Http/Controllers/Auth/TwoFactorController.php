<?php

namespace Marble\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Marble\Admin\Models\User;
use Marble\Admin\Support\Totp;

class TwoFactorController extends Controller
{
    // ── Challenge (login flow) ────────────────────────────────────────────────

    public function showChallenge(Request $request)
    {
        if (!$request->session()->has('marble_2fa_user_id')) {
            return redirect()->route('marble.login');
        }

        return view('marble::auth.two-factor');
    }

    public function verify(Request $request)
    {
        $userId = $request->session()->get('marble_2fa_user_id');
        if (!$userId) {
            return redirect()->route('marble.login');
        }

        $request->validate(['code' => 'required|string']);

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('marble.login');
        }

        $code = preg_replace('/[\s\-]/', '', $request->input('code'));

        // Check backup codes first
        $backupCodes = $user->two_factor_backup_codes ?? [];
        if (in_array($code, $backupCodes, true)) {
            $user->update([
                'two_factor_backup_codes' => array_values(array_filter($backupCodes, fn($c) => $c !== $code)),
            ]);
            $this->completeLogin($request, $user);
            return redirect()->route('marble.dashboard');
        }

        // Verify TOTP
        if (!Totp::verify($user->two_factor_secret, $code)) {
            return back()->withErrors(['code' => trans('marble::admin.two_factor_invalid_code')]);
        }

        $this->completeLogin($request, $user);
        return redirect()->route('marble.dashboard');
    }

    private function completeLogin(Request $request, User $user): void
    {
        Auth::guard('marble')->login($user, $request->session()->get('marble_2fa_remember', false));
        $request->session()->forget(['marble_2fa_user_id', 'marble_2fa_remember']);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);
    }

    // ── Setup / Disable (authenticated) ──────────────────────────────────────

    public function generateSecret(Request $request, User $user)
    {
        $secret = Totp::generateSecret();
        $request->session()->put('marble_2fa_pending_secret_' . $user->id, $secret);

        $uri     = Totp::otpauthUri($secret, $user->email, config('app.name', 'Marble CMS'));
        $qrUrl   = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($uri);

        return response()->json([
            'secret' => $secret,
            'qr_url' => $qrUrl,
        ]);
    }

    public function enable(Request $request, User $user)
    {
        $request->validate(['code' => 'required|string']);

        $secret = $request->session()->get('marble_2fa_pending_secret_' . $user->id);
        if (!$secret) {
            return back()->withErrors(['code' => trans('marble::admin.two_factor_session_expired')]);
        }

        $code = preg_replace('/\s+/', '', $request->input('code'));
        if (!Totp::verify($secret, $code)) {
            return back()->withErrors(['code' => trans('marble::admin.two_factor_invalid_code')]);
        }

        $backupCodes = Totp::generateBackupCodes();

        $user->update([
            'two_factor_enabled'      => true,
            'two_factor_secret'       => $secret,
            'two_factor_backup_codes' => $backupCodes,
        ]);

        $request->session()->forget('marble_2fa_pending_secret_' . $user->id);

        return redirect()->route('marble.user.edit', $user)
            ->with('two_factor_backup_codes', $backupCodes)
            ->with('success', trans('marble::admin.two_factor_enabled'));
    }

    public function disable(Request $request, User $user)
    {
        $user->update([
            'two_factor_enabled'      => false,
            'two_factor_secret'       => null,
            'two_factor_backup_codes' => null,
        ]);

        return redirect()->route('marble.user.edit', $user)
            ->with('success', trans('marble::admin.two_factor_disabled'));
    }
}
