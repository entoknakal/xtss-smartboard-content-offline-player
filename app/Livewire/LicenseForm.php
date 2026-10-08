<?php

namespace App\Livewire;

use App\Models\Settings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class LicenseForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('license_file')
                    ->label('File Lisensi Sekolah')
                    ->placeholder('Pilih atau seret file .xtss ke sini...')
                    ->directory('licenses')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save()
    {
        // 1. Ambil data yang divalidasi oleh Filament
        $state = $this->form->getState();

        // PERBAIKAN: Ambil nilai string murni dari index [0] jika Filament mengembalikannya sebagai array
        $licenseFile = $state['license_file'] ?? null;
        $filePath = is_array($licenseFile) ? array_values($licenseFile)[0] : $licenseFile;

        // Pastikan file path tidak kosong sebelum memproses ke Storage
        if (empty($filePath)) {
            Notification::make()
                ->title('Gagal Memproses File')
                ->body('File tidak terdeteksi di server.')
                ->danger()
                ->send();
            return;
        }

        // Ambil path fisik file dari storage
        $fullPath = Storage::disk('local')->path($filePath);

        // 2. Mulai membaca file menggunakan Stream Reader
        if (($handle = fopen($fullPath, "r")) !== FALSE) {

            // Baca baris pertama (Header) - pisahkan menggunakan karakter semikolon (;)
            $header = fgetcsv($handle, 1000, ";");

            // Bersihkan tanda kutip dua atau spasi aneh pada header jika ada
            $header = array_map(fn($item) => trim($item, " \""), $header);

            // 3. VALIDASI STRUKTUR HEADER FILE .XTSS
            $expectedHeader = ["uuid", "npsn", "nama_sekolah", "status", "alamat", "lintang", "bujur", "propinsi", "kota", "kecamatan"];

            if ($header !== $expectedHeader) {
                fclose($handle);
                // Hapus file ilegal dari storage agar tidak memenuhi server
                Storage::disk('local')->delete($filePath);

                Notification::make()
                    ->title('Validasi Lisensi Gagal')
                    ->body('Struktur atau isi komponen di dalam file .xtss rusak / tidak dikenali.')
                    ->danger()
                    ->send();
                return;
            }

            // 4. BACA BARIS DATA LISENSI (Baris kedua dst)
            $licenseData = [];
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $row = array_map(fn($item) => trim($item, " \""), $row);
                // Gabungkan header dengan data agar menjadi array asosiatif
                $licenseData[] = array_combine($header, $row);
            }

            fclose($handle);

            // 5. PROSES MEMASUKKAN DATA KE TABEL SETTINGS
            if (!empty($licenseData)) {
                // Mengambil baris data sekolah pertama
                $schoolData = $licenseData[0];

                // Lakukan looping berpasangan (Key = Nama Header, Value = Data)
                foreach ($schoolData as $key => $value) {
                    if ($key === 'uuid') {
                        $key = 'license_id';
                    }

                    Settings::updateOrCreate(
                        ['name' => $key],  // Jika record dengan 'name' ini sudah ada, perbarui nilainya
                        ['uuid' => $value] // Masukkan datanya ke dalam kolom uuid
                    );
                }
            }

            // Hapus file setelah selesai diproses agar storage tetap bersih
            Storage::disk('local')->delete($filePath);

            Notification::make()
                ->title('Lisensi Berhasil Diperbarui')
                ->body('Data lisensi sekolah telah berhasil disimpan ke database.')
                ->success()
                ->send();

            return redirect()->route('dashboard');
        } else {
            Notification::make()
                ->title('Gagal Membaca Berkas')
                ->body('Sistem tidak dapat membuka file .xtss yang diunggah.')
                ->danger()
                ->send();
        }
    }

    public function resetForm(): void
    {
        // Mengosongkan status form kembali ke data awal / kosong
        $this->form->fill();
    }

    #[Layout('license')]
    public function render()
    {
        return view('livewire.license-form');
    }
}
