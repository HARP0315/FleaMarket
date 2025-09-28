<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // ▼▼▼ ここからが今回の「最強の解決策」 ▼▼▼
        // もし、アプリケーションがテスト環境で実行されているなら
        if ($app->environment('testing')) { // env()ヘルパーより確実な書き方
            $config = $app->make('config');
            $config->set('database.connections.mysql.database', 'fleamarket_test');
            $config->set('database.connections.mysql.username', 'root');
            $config->set('database.connections.mysql.password', 'root');
        }
        // ▲▲▲ ここまで ▲▲▲

        return $app;
    }
}
