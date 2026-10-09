<div>
    <!-- Judul Halaman -->
    <div style="text-align: center !important; margin-bottom: 2rem !important;">
        <div style="margin-bottom: 2rem">
            <img src="{{ asset('Xtss.svg') }}" class="w-auto h-8" alt="Logo" />
        </div>
        <h2
            style="font-size: 1.875rem !important; font-weight: 800 !important; line-height: 2.25rem !important; margin-bottom: 1rem !important; color: #ffffff !important;">
            Aktivasi Lisensi Aplikasi
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Silakan unggah file lisensi resmi sekolah Anda untuk melanjutkan.
        </p>
    </div>

    <!-- Form Utama -->
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3" style="margin-top:2rem !important;">
            <x-filament::button type="submit" color="primary">
                Simpan Lisensi
            </x-filament::button>
            <x-filament::button type="button" wire:click="resetForm" color="gray" outlined>
                Bersihkan Form
            </x-filament::button>
        </div>
    </form>

    <!-- PENGAMAN MUTLAK JAVASCRIPT DI FRONTEND -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('FilePond:init', (e) => {
                const pond = e.detail.pond;

                if (pond) {
                    pond.setOptions({
                        acceptedFileTypes: null,

                        beforeAddFile: (item) => {
                            const filename = item.file.name.toLowerCase();

                            // Jika ekstensinya BUKAN .xtss, tolak dan tendang keluar
                            if (!filename.endsWith('.xtss')) {

                                // PERBAIKAN: Memicu Toast Notification asli milik Filament via JavaScript
                                if (typeof FilamentNotification !== 'undefined') {
                                    new FilamentNotification()
                                        .title('Jenis berkas tidak valid')
                                        .body('Mengharapkan jenis file *.xtss')
                                        .danger()
                                        .send();
                                } else {
                                    // Fallback darurat jika asset Filament telat dimuat browser
                                    alert('Jenis berkas tidak valid. Mengharapkan jenis file *.xtss');
                                }

                                return false;
                            }
                            return true;
                        }
                    });
                }
            });
        });
    </script>
</div>