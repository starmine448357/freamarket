<?php

// app/Http/Controllers/RegisterController.php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;   // ← 追加
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            // needs_profile_setup を使うならマイグレーションの default(true) に任せる or ここで true を明示
            // 'needs_profile_setup' => true,
        ]);

        event(new Registered($user)); // 認証メール送信

        Auth::login($user); // ★ ここで一度ログインして認証画面を表示可能にする

        return redirect()->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }
}
