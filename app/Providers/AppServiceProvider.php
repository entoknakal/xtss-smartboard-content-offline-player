<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ini_set('upload_max_filesize', '200M');
        ini_set('post_max_size', '205M');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '600');
        // Config::set('nativephp.php_ini', [
            // 'upload_max_filesize' => '100M',
            // 'post_max_size'       => '105M',
            // 'memory_limit'        => '512M',
            // 'max_execution_time'  => '600',
            // // 'extension' => [
            // //     'gd',
            // //     'zip',
            // // ],
        // ]);
    }
}
