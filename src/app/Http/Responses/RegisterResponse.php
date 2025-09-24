<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * 新規登録後のレスポンス
     */
    public function toResponse($request)
    {
        return redirect()->route('verification.notice');
    }
}
