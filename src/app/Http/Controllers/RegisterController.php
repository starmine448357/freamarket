<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * 会員登録フォーム表示
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * 会員登録処理
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // 認証メール送信
        event(new Registered($user));

        // 登録直後にログインさせて認証画面を表示
        Auth::login($user);

        return redirect()
            ->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }
}
