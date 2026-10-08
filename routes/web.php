<?php

use App\Livewire\LicenseForm;
use App\Livewire\ReadData;
use App\Models\Settings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Route::view('/', 'welcome')->name('dashboard');

// reset session sebelum load welcome page
Route::get('/', function () {
    $livewireTmpPath = storage_path('app/private/livewire-tmp');
    // Memastikan folder tersebut ada sebelum dibersihkan agar tidak memicu error
    if (File::exists($livewireTmpPath)) {
        File::cleanDirectory($livewireTmpPath);
    }

    $storagePath = storage_path('framework');
    $totalSize = 0;

    // 1. Hitung total ukuran file di folder storage/framework
    if (File::exists($storagePath)) {
        foreach (File::allFiles($storagePath) as $file) {
            $totalSize += $file->getSize(); // dalam bytes
        }
    }

    // // 2. Konversi 100 MB ke Bytes (100 * 1024 * 1024)
    // $maxSize = 104857600;
    // 2. Konversi 1 MB ke Bytes (1 * 1024 * 1024)
    $maxSize = 1048576;

    // 3. Jika melebihi batas, bersihkan cache
    if ($totalSize >= $maxSize) {
        Artisan::call('optimize:clear');
    }

    if (auth()->user()?->id) {
        auth()->guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    // cek uuid sekolah
    $license = Settings::query()->where('name', '=', 'license_id')->first();

    if (empty($license->uuid)) {
        // return redirect('/admin');
        return redirect()->route('license.activate');
    }

    if (!file_exists(public_path('storage'))) {
        Artisan::call('storage:link');
    }

    return view('welcome');
})->name('dashboard');

Route::get('/activate-license', LicenseForm::class)->name('license.activate');
Route::get('/ztss-reader', ReadData::class)->name('ztss.reader');
Route::get('/storage/ztss_extracted/{uuid}/{userId}/{kelasId}/{htmlName}', function ($uuid, $userId, $kelasId, $htmlName) {
    // Mencoba mencari di folder private karena visibilitas di set private pada form
    $path = 'ztss_extracted/' . "$uuid/$userId/$kelasId/$htmlName";
    // dd($path, Storage::disk('local')->exists($path));
    if (!Storage::disk('local')->exists($path)) {
        // Fallback jika file ternyata tidak berada di folder private
        $path = 'storage/ztss_extracted/' . "$uuid/$userId/$kelasId/$htmlName";
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }
    }

    return Storage::disk('local')->response($path);
})->name('html.viewer')->middleware(['auth']);
