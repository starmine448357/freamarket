<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * アプリケーションのイベントリスナ
     * イベント => [リスナ, ...] をここに列挙します。
     */
    protected $listen = [
        // \App\Events\SomethingHappened::class => [
        //     \App\Listeners\DoSomething::class,
        // ],
    ];

    /**
     * アプリケーションのイベント登録
     */
    public function boot(): void
    {
        //
    }
}
