<div class="w-full">
    <h2 class="text-lg font-bold mb-2 text-black dark:text-white">Unggah Berkas Baru</h2>
    <p class="text-sm text-black dark:text-white mb-6 font-sans" style="margin-bottom:1.25rem !important;">Hanya
        menerima
        file ekstensi <strong>*.ztss</strong><br>Max. besar file <strong>{{ $maxSize }}</strong></p>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 dark:text-black px-4 py-3 rounded relative mb-4"
            role="alert">
            <span class="block sm:inline !text-black">{!! session('message') !!}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 dark:text-black px-4 py-3 rounded relative mb-4"
            role="alert">
            <span class="block sm:inline text-red-700 font-bold dark:text-red-500">{!! session('error') !!}</span>
        </div>
    @endif

    <div x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="uploading = true; progress = 0;"
        x-on:livewire-upload-finish="uploading = false; progress = 100;"
        x-on:livewire-upload-error="uploading = false; progress = 0;"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
        x-on:reset-upload-progress.window="progress = 0; uploading = false;">

        <form wire:submit.prevent="save" enctype="multipart/form-data">
            @csrf
            <div
                class="group relative flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 dark:border-[#3a3a38] rounded-xl hover:border-blue-500 dark:hover:border-blue-400 transition-all cursor-pointer bg-gray-50 dark:bg-[#1b1b18]">
                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                    <svg class="w-10 h-10 mb-3 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    <p class="" id="file-label">
                        @if ($ztssFile)
                            <span class="text-blue-600 font-bold">{{ $ztssFile->getClientOriginalName() }}</span>
                        @else
                            Klik atau seret file .ztss ke sini
                        @endif
                    </p>
                </div>
                <input type="file" wire:model="ztssFile" id="ztss_file" accept=".ztss"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer mb-4" />
            </div>

            <!-- Progress Bar Kontainer (Hanya muncul saat proses upload berjalan) -->
            <div x-show="uploading" class="mb-4" x-cloak>
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium dark:text-amber-300">Mengunggah...</span>
                    <span class="text-sm font-medium dark:text-amber-300" x-text="progress + '%'"></span>
                </div>
                <!-- Baris Progress Menggunakan Tailwind CSS -->
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-150"
                        :style="`width: ${progress}%`"></div>
                </div>

                <!-- Tombol Batal/Cancel Upload (Opsional) -->
                <button type="button" wire:click="$cancelUpload('ztssFile'); $set('ztssFile', null)"
                    @click="uploading = false; progress = 0"
                    class="text-xs dark:text-lime-300 dark:font-bold mt-1 hover:underline">
                    Batal Unggah
                </button>
            </div>

            {{-- <div x-show="progress === 100" && $wire.ztssFile" x-cloak>
                @if ($errors->has('ztssFile'))
                <span class="text-red-500 dark:text-rose-400 text-xs mt-2 block">
                    {!! $errors->first('ztssFile') !!}
                </span>
                @else
                <span class="dark:text-green-500 text-xs mt-2 block">
                    File memenuhi syarat dan siap diunggah. Klik tombol <b>Unggah</b> untuk melanjutkan.
                </span>
                @endif
            </div> --}}
            <div x-show="$wire.licenseError || @json($errors->has('ztssFile')) || (progress === 100 && $wire.ztssFile)"
                x-cloak>
                @if ($licenseError)
                    <!-- Menampilkan Error Lisensi Kustom -->
                    <span class="text-red-500 dark:text-rose-400 text-xs mt-2 block">
                        {!! $licenseError !!}
                    </span>
                @else
                    @if ($errors->has('ztssFile'))
                        <!-- Menampilkan Error Validasi Bawaan -->
                        <span class="text-red-500 dark:text-rose-400 text-xs mt-2 block">
                            {!! $errors->first('ztssFile') !!}
                        </span>
                    @else
                        <!-- Menampilkan Teks Sukses / Loading Verifikasi -->
                        <span wire:loading.remove wire:target="ztssFile" class="dark:text-green-500 text-xs mt-2 block">
                            File memenuhi syarat dan siap diunggah. Klik tombol <b>Unggah</b> untuk melanjutkan.
                        </span>

                        <span wire:loading wire:target="ztssFile"
                            class="text-amber-500 dark:text-amber-300 text-xs mt-2 block animate-pulse">
                            Sedang memverifikasi lisensi berkas ZTSS...
                        </span>
                    @endif
                @endif
            </div>

            {{-- <button type="submit" class="btn-upload"> --}}
                <button type="submit"
                    class="flex items-center justify-center w-full p-[clamp(0.75rem,2vw,1.25rem)] bg-[#0d6efd] text-white no-underline rounded-xl font-bold text-[clamp(0.95rem,2vw,1.2rem)] transition-all duration-300 ease-in-out shadow-[0_4px_6px_-1px_rgba(231,76,60,0.2)] mb-2 mt-4 hover:bg-[#428ef7] hover:-translate-y-0.5 hover:shadow-[0_8px_20px_rgba(147,153,211,0.6)] dark:bg-[#1a73e8] dark:hover:bg-[#428ef7] dark:hover:shadow-[0_8px_20px_rgba(147,153,211,0.4)]">
                    Unggah
                </button>
        </form>
    </div>
</div>