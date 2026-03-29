<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\ApiToken;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::with('user')->latest()->get();

        return view('marble::api-token.index', compact('tokens'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'abilities'  => 'nullable|array',
            'abilities.*' => 'string',
            'expires_at' => 'nullable|date',
        ]);

        // Generate a 64-char hex plain token
        $plain = bin2hex(random_bytes(32)); // 32 bytes = 64 hex chars
        $hash  = hash('sha256', $plain);

        ApiToken::create([
            'name'       => $request->input('name'),
            'token'      => $hash,
            'user_id'    => null,
            'abilities'  => $request->input('abilities', ['read']),
            'expires_at' => $request->input('expires_at') ?: null,
        ]);

        session()->flash('new_token', $plain);

        return redirect()->route('marble.api-token.index');
    }

    public function delete(ApiToken $token)
    {
        $token->delete();

        return redirect()->route('marble.api-token.index');
    }
}
