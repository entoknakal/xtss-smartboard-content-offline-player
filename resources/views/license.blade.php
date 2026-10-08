<x-filament-panels::layout.simple>
    <!-- Kontainer kustom tambahan agar isi form Anda terlihat rapi -->
    <div
        class="w-full max-w-xl mx-auto bg-white dark:bg-gray-900 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        {{ $slot }}
    </div>
</x-filament-panels::layout.simple>