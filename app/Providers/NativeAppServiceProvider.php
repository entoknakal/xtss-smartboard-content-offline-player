<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // 1. Jalankan migrasi database otomatis
        Artisan::call('migrate', ['--force' => true]);

        // 2. Logika proteksi Seeder agar tidak duplikat
        if (Schema::hasTable('users') && DB::table('users')->count() === 0) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        // 3. Trik memaksa pembuatan ulang shortcut storage yang hilang di komputer pengguna
        if (!file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
        }

        Window::open()
            ->fullscreen()
            ->showDevTools(false); // Untuk debugging set menjadi true;
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            // 'display_errors' => '1',
            // 'error_reporting' => 'E_ALL',
            'max_execution_time' => '0',
            'max_input_time' => '0',
            'upload_max_filesize' => '80M',
            'max_file_uploads' => '5',
            'post_max_size' => '80M',
        ];
    }
}
