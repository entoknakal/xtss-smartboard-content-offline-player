<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, viewport-fit=cover">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Memuat aset menggunakan Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance

</head>

<body class="nativephp-safe-area">

    <!-- Preloader -->
    <div id="preloader"
        class="fixed inset-0 z-9999 flex flex-col items-center justify-center bg-white dark:bg-[#161615] transition-opacity duration-500">
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-[#e74c3c] border-t-transparent rounded-full animate-spin"></div>
            <p class="mt-4 text-sm font-medium text-gray-600 dark:text-gray-400 animate-pulse">Memuat halaman...</p>
        </div>
    </div>
    <!-- Link ke Admin Panel -->
    <div class="fixed bottom-0 left-0 z-50" style="padding: 0 10px 10px 10px;">
        <a href="{{ url('/admin') }}"
            class="bg-lime-200 dark:bg-blue-500 flex items-center gap-2 px-4 py-2 rounded-full text-black dark:text-white backdrop-blur-md transition-all duration-300 shadow-lg"
            title="Login ke Panel Admin">
            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider">Login</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
        </a>
    </div>

    <!-- URL QUIT, MINIMIZE -->
    <div class="fixed bottom-0 right-0 z-50 flex items-center gap-4" style="padding: 0 10px 10px 10px;">
        <!-- Tombol Minimized Window -->
        <button onclick="fetch('{{ route('kecilin') }}')" title="Minimized Window"
            class="bg-white/80 dark:bg-[#161615]/80 backdrop-blur-md px-3 py-2 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-md hover:-translate-y-0.5 border border-gray-200 dark:border-gray-800 text-black dark:text-white cursor-pointer">
            <img src="{{ asset('img/minimized_window.png') }}" width="20" class="opacity-80">
            <span class="text-xs font-semibold">Minimize</span>
        </button>

        <!-- Tombol Quit -->
        <a href="{{ route('keluar') }}" onclick="showPreloader()" title="Keluar Aplikasi"
            class="bg-red-500/90 hover:bg-red-600/90 text-white px-3 py-2 rounded-xl flex items-center gap-2 transition-all duration-300 shadow-md hover:-translate-y-0.5">
            <img src="{{asset('img/switch.png')}}" width="20" class="invert dark:invert-0">
            <span class="text-xs font-semibold">Quit</span>
        </a>
    </div>

    <div class="relative h-screen w-screen flex flex-col justify-center items-center">
        <div class="absolute inset-0 z-1 overflow-hidden">
            {{-- <video autoplay muted playsinline> --}}
                <video autoplay muted playsinline class="h-full w-full object-cover object-center">
                    <source src="{{ asset('img/background5.webm') }}" type="video/webm">
                    <!-- Fallback jika video tidak support -->
                    <img src="{{ asset('img/background2.png') }}" alt="Background">
                </video>
        </div>

        <!-- Elemen bayangan gelap -->
        <div
            class="absolute inset-0 bg-linear-to-b from-black/10 to-black/10 z-2 dark:absolute dark:inset-0 dark:bg-linear-to-b dark:from-black/25 dark:to-black/25 dark:z-2">
        </div>



        <!-- Kotak konten utama -->
        <div
            class="[position:inherit] z-3 bg-white/90 text-black p-[clamp(1.5rem,5vw,3rem)] rounded-2xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.4)] max-w-[clamp(22rem,95vw,55rem)] w-full max-h-[90vh] overflow-y-auto text-center border border-[#e5e7eb] dark:bg-[#161615]/75 dark:border-[#262624] dark:text-white">
            <h1 class="text-black dark:text-white text-2xl sm:text-3xl md:text-4xl font-extrabold mb-3">Selamat
                Datang
            </h1>
            <p class="text-sm sm:text-base md:text-lg mb-6 leading-relaxed">
                Akses materi pembelajaran Smartboard Offline Anda dengan mudah.
            </p>

            <div class="flex flex-col lg:flex-row items-center gap-4 lg:gap-8 lg:mt-6">
                <div class="w-full lg:flex-1">
                    <div style="margin-top:0;">
                        @livewire('file-upload-ztss')
                        {{-- @livewire('file-picker') --}}
                    </div>
                </div>

                <div class="relative flex lg:flex-col items-center justify-center w-full lg:w-auto my-2 lg:my-0">
                    <div
                        class="grow lg:flex-none lg:h-32 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-gray-800">
                    </div>
                    <span
                        class="shrink mx-4 lg:mx-0 lg:my-2 text-black dark:text-white text-[10px] font-bold uppercase tracking-widest">atau</span>
                    <div
                        class="grow lg:flex-none lg:h-32 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-gray-800">
                    </div>
                </div>

                <div class="w-full lg:flex-1 flex flex-col items-center justify-center text-center">
                    <img src="{{ asset('img/masuk_kelas.png') }}" alt="Siap Belajar"
                        class="opacity-70 mb-4 max-w-full h-auto">
                    <div class="mt-4"></div>
                    <a href="{{ route('ztss.reader') }}" onclick="showPreloader()"
                        class="flex items-center justify-center w-full p-[clamp(1.25rem,2vw,1.25rem)] bg-[#e74c3c] text-white rounded-xl font-bold text-base md:text-lg transition-all duration-300 ease-in-out shadow-md mb-2 hover:bg-[#c0392b] hover:-translate-y-0.5 hover:shadow-lg dark:bg-[#c0392b] dark:hover:bg-[#b03026]">
                        Mulai Belajar
                    </a>
                </div>
            </div>
        </div>
        <div class="z-3 text-xs text-white/80 dark:text-white/70 font-medium drop-shadow-md">
            version {{ Native\Desktop\Facades\App::version() }}
        </div>
    </div>
    @livewireScripts
    @fluxScripts

    <script>
        // Fungsi untuk memunculkan preloader kembali (saat klik tombol)
        function showPreloader() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'flex';
                preloader.classList.remove('opacity-0');
            }
        }

        // Fungsi untuk menyembunyikan preloader dengan efek transisi
        function hidePreloader() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.classList.add('opacity-0');
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }
        }

        // Sembunyikan preloader saat load awal atau navigasi Livewire (wire:navigate)
        window.addEventListener('load', () => setTimeout(hidePreloader, 800));
        document.addEventListener('livewire:navigated', () => setTimeout(hidePreloader, 500));

        // Antisipasi jika script dijalankan setelah halaman selesai dimuat (seperti pada wire:navigate)
        if (document.readyState === 'complete') setTimeout(hidePreloader, 800);
    </script>
</body>

</html>