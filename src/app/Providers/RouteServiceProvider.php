<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * ログイン後のリダイレクト先
     */
    public const HOME = '/mypage';

    public function boot(): void
    {
        parent::boot();

        // ルートの追加処理が必要ならここに書く
    }
}
