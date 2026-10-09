<x-filament-panels::page.simple>
    {{ $this->content }}

    <div style="display: grid; place-items: center; width: 100%; text-align: center;">
        <span class="text-xs text-gray-500 dark:text-gray-400" style="font-size: small; color: #C7BCB8;">
            version {{ Native\Desktop\Facades\App::version() }}
        </span>
    </div>
    {{-- <div class="mt-4 text-center">
        <x-filament::link :href="route('dashboard')" icon="heroicon-m-arrow-left">
            Kembali ke Halaman Utama
        </x-filament::link>
    </div> --}}
</x-filament-panels::page.simple>