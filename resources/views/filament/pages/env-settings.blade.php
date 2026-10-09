<x-filament-panels::page>

    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3" style="margin-top:2rem !important;">

            <!-- Tombol Utama: Simpan Data -->
            <x-filament::button type="submit" color="primary">
                Simpan Perubahan
            </x-filament::button>

            <!-- Tombol Tambahan: Batal / Cancel (Mengarahkan ke Dashboard) -->
            <x-filament::button tag="a" href="{{ route('filament.admin.pages.dashboard') }}" color="gray" outlined>
                Batal
            </x-filament::button>

            @if (auth()->user()->role !== 'guru')
                <!-- Tombol Sinkronisasi -->
                <x-filament::button type="button" wire:click="syncToOnline" wire:loading.attr="disabled" color="info"
                    outlined>
                    <span wire:loading.remove wire:target="syncToOnline">Simpan & Sync ke XTSS Online</span>
                    <span wire:loading wire:target="syncToOnline">Mengirim Data...</span>
                </x-filament::button>
            @endif
        </div>
    </form>
</x-filament-panels::page>