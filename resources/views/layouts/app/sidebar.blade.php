<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    @fluxAppearance
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<!-- ✨ 1. Tambahkan state Alpine.js 'sidebarOpen' pada tag body -->

<body x-data="{ sidebarOpen: false }"
    class="nativephp-safe-area min-h-screen bg-gray-100 dark:bg-zinc-800 flex flex-col lg:flex-row relative">

    <!-- ✨ 2. TOMBOL HAMBURGER (Hanya muncul di layar HP/Tablet untuk membuka sidebar) -->
    <div
        class="lg:hidden w-full bg-teal-800 dark:bg-zinc-900 p-4 flex items-center justify-between border-b border-teal-700 dark:border-zinc-700 shadow-md sticky top-0 z-50">
        <img src="{{ asset('Xtss.svg') }}" class="w-auto h-8" alt="Logo" />
        <button @click="sidebarOpen = !sidebarOpen" type="button"
            class="text-white p-2 rounded-md hover:bg-teal-700 focus:outline-none">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- ✨ 3. SIDEBAR RESPONSIVE (Tersembunyi di HP, melayang saat dibuka, dan permanen di Desktop) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="w-80 h-screen bg-teal-800 dark:bg-zinc-900 border-e border-zinc-200 dark:border-zinc-700 flex flex-col p-6 fixed lg:sticky top-0 left-0 shadow-lg z-50 transform lg:transform-none transition-transform duration-300 ease-in-out">

        <!-- Header / Logo & Tombol Close (Tombol close hanya muncul di HP) -->
        <div class="mb-10 flex items-center justify-between lg:justify-center">
            <img src="{{ asset('Xtss.svg') }}" class="w-auto h-12" alt="Logo" />
            <button @click="sidebarOpen = false" class="lg:hidden text-teal-200 hover:text-white p-2">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Group Title (Platform) -->
        <div class="mb-4">
            <span class="text-xl font-bold uppercase tracking-wider text-teal-200 dark:text-zinc-400">
                {{ __('Platform') }}
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-col gap-4">

            <!-- 1. Menu Dashboard -->
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex items-center gap-4 px-4 py-3 rounded-lg font-semibold text-lg transition-all duration-200 
               {{ request()->routeIs('dashboard') ? 'bg-teal-700 text-white shadow-md' : 'text-teal-100 hover:bg-teal-700/50 hover:text-white' }}">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>{{ __('Dashboard') }}</span>
            </a>

            <!-- 2. Menu ZTSS Reader -->
            <a href="{{ route('ztss.reader') }}" wire:navigate
                class="flex items-center gap-4 px-4 py-3 rounded-lg font-semibold text-lg transition-all duration-200 
               {{ request()->routeIs('ztss.reader') ? 'bg-teal-700 text-white shadow-md' : 'text-teal-100 hover:bg-teal-700/50 hover:text-white' }}">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9l-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>{{ __('ZTSS Reader') }}</span>
            </a>

        </nav>

        <div class="flex-1"></div>

        <div class="pt-4 border-t border-teal-700 dark:border-zinc-700">
            {{-- <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" /> --}}
        </div>
    </aside>

    <!-- ✨ 4. LAPISAN LATAR HITAM (Muncul saat sidebar terbuka di HP untuk menutup menu saat area luar diklik) -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="lg:hidden fixed inset-0 bg-black/50 z-40" x-cloak>
    </div>

    <!-- Konten Utama Aplikasi (Berada di sebelah kanan sidebar) -->
    <main class="flex-1 min-h-screen pb-24 p-8 overflow-y-auto">
        {{ $slot }}
    </main>

    @persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>