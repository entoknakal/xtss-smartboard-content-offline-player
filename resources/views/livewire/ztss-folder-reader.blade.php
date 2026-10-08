<div class="relative"> {{-- Container utama tetap relative untuk modal preview --}}
    <div class="w-full bg-white dark:bg-zinc-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
        @if ($statusType === 'error')
            <div class="border border-[#30363d] rounded-md overflow-hidden">
                <p class="p-3 text-sm text-gray-600 dark:text-gray-400">{!! $statusMessage !!}</p>
            </div>
        @elseif (empty($nama_sekolah))
            <div class="border border-[#30363d] rounded-md overflow-hidden">
                <p class="p-3 text-sm text-gray-600 dark:text-gray-400">Belum ada sekolah yang terdaftar. Silahkan
                    kembali ke halaman Dashboard lalu upload file *.ztss</p>
            </div>
        @else
                <div class="flex flex-col gap-4"> {{-- Wrapped all content in a single div --}}
                    <span class="font-bold text-gray-600 dark:text-white mb-4 md:mb-4">
                        {{ $nama_sekolah }}
                    </span>
                </div>

                <!-- ✨ DROPDOWN AREA -->
                <div
                    class="flex flex-col sm:flex-row gap-4 mb-8 [&_label]:text-gray-500 [&_label]:dark:text-gray-400 [&_label]:font-semibold [&_label]:text-xs [&_label]:uppercase [&_label]:tracking-wider">
                    <flux:select wire:model.live="selectedTeacher" class="flex-1 ztss-select-custom">
                        <flux:select.option value="">Pilih Nama Guru</flux:select.option>
                        @foreach ($teachers as $id => $name)
                            <flux:select.option value="{{ $id }}" class="dark:text-white">{{ $name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="selectedClass" class="flex-1 ztss-select-custom">
                        @if (!$selectedTeacher)
                            <flux:select.option value=""></flux:select.option>
                        @else
                            <flux:select.option value="">Pilih Kelas</flux:select.option>
                            @foreach ($classes as $id => $class)
                                @if ($class['user_id'] == $selectedTeacher)
                                    <flux:select.option value="{{ $id }}" class="dark:text-white">
                                        {{ $class['name'] }}
                                    </flux:select.option>
                                @endif
                            @endforeach
                        @endif
                    </flux:select>

                    <flux:select wire:model.live="selectedMapel" class="flex-1 ztss-select-custom">
                        @if (!$selectedClass)
                            <flux:select.option value=""></flux:select.option>
                        @else
                            <flux:select.option value="">Pilih Mapel</flux:select.option>
                            @foreach ($subjects[$selectedTeacher][$selectedClass] ?? [] as $mapel)
                                <flux:select.option value="{{ $mapel }}" class="dark:text-white">{{ $mapel }}
                                </flux:select.option>
                            @endforeach
                        @endif
                    </flux:select>

                    <flux:select wire:model.live="selectedElemenPembelajaran" class="flex-1 ztss-select-custom">
                        @if (!$selectedMapel)
                            <flux:select.option value=""></flux:select.option>
                        @else
                            <flux:select.option value="">Pilih Elemen Pembelajaran</flux:select.option>
                            @foreach ($elemenList[$selectedTeacher][$selectedClass][$selectedMapel] ?? [] as $elemen)
                                <flux:select.option value="{{ $elemen }}" class="dark:text-white">{{ $elemen }}
                                </flux:select.option>
                            @endforeach
                        @endif
                    </flux:select>

                </div>
                <!-- END DROPDOWN AREA -->


                @if ($selectedElemenMateri)
                    <!-- Content Card -->
                    <div
                        class="mt-4 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6 flex flex-col gap-3 text-md shadow-sm">

                        <!-- Grid Row 2 -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-gray-600 dark:text-gray-400 font-bold mb-1">Semester</div>
                                <div class="dark:text-white font-normal">{{ $selectedElemenMateri['semester'] }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600 dark:text-gray-400 font-bold mb-1">Kategori mata pelajaran</div>
                                <div class="dark:text-white font-normal">
                                    {{ $selectedElemenMateri['kategori_mata_pelajaran'] }}
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-teal-500/20 -mx-6">

                        <!-- Elemen Pembelajaran -->
                        <div>
                            <div class="text-gray-600 dark:text-gray-400 font-bold mb-1">Elemen Pembelajaran</div>
                            <div class="dark:text-white font-normal">{{ $selectedElemenMateri['elemen_pembelajaran'] }}
                            </div>
                        </div>

                        <!-- Deskripsi Elemen Pembelajaran -->
                        <div>
                            <div class="text-gray-600 dark:text-gray-400 font-bold mb-1.5">Deskripsi Elemen Pembelajaran
                            </div>
                            <div class="dark:text-white leading-relaxed text-justify">
                                {{ $selectedElemenMateri['deskripsi_elemen_pembelajaran'] }}
                            </div>
                        </div>

                        <!-- Materi Ajar Table Section -->
                        <div>
                            <div class="text-gray-600 dark:text-gray-400 font-bold mb-2">Materi ajar</div>
                            @if (!empty($selectedElemenMateri['materi']))
                                <div class="border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden shadow-sm">
                                    <table class="w-full text-left border-collapse">
                                        <thead class="bg-teal-600 dark:bg-teal-700/80">
                                            <tr class="text-white font-bold">
                                                <th class="p-3 w-1/3 uppercase text-xs tracking-wider">Nama bab</th>
                                                <th class="p-3 uppercase text-xs tracking-wider">Tujuan pembelajaran</th>
                                                <th class="p-3 uppercase text-xs tracking-wider w-1/5">Alokasi waktu</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-[#3d414a]">
                                            @foreach ($selectedElemenMateri['materi'] as $materi)
                                                <tr
                                                    class="align-top odd:bg-white even:bg-gray-50 dark:odd:bg-[#1c1e22] dark:even:bg-[#24272d] transition-colors">
                                                    <td class="p-3 font-bold text-gray-900 dark:text-gray-100">
                                                        {{ $materi['nama_bab'] }}
                                                    </td>
                                                    <td class="p-3 text-gray-600 dark:text-gray-300 text-pretty">
                                                        <div class="[&>ul]:list-disc [&>ul]:list-inside [&>ul]:pl-4 [&>ul_li]:space-y-2">
                                                            {!! str_replace(['<p>', '</p>'], '', $materi['tujuan_pembelajaran']) !!}
                                                        </div>
                                                    </td>
                                                    <td class="p-3">
                                                        <span
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-100 shadow-sm">
                                                            {{ $materi['alokasi_waktu'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- File Materi Section -->
                        <div>
                            <div class="text-gray-600 dark:text-gray-400 font-normal mb-2">File Materi</div>
                            @if (!empty($selectedContent['konten']))
                                @foreach ($selectedContent['konten'] as $konten)
                                    <div class="border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden mb-2">
                                        <table class="w-full text-left border-collapse ">
                                            <tbody>
                                                <tr>
                                                    <td class="p-3 flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        {{ $konten['file_name'] }}
                                                    </td>
                                                    <td class="p-3 text-right text-md font-normal space-x-3">
                                                        <button
                                                            wire:click="openFileInModal('{{ $konten['fullFilePath'] }}', '{{ $konten['file_name'] }}')"
                                                            class="bg-amber-500 hover:bg-amber-600 text-white dark:text-gray-600 font-bold py-1.5 px-5 rounded-full transition-all duration-200 active:scale-95 dark:bg-amber-400 dark:hover:bg-amber-500">Preview</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach

                            @endif
                        </div>
                    </div>
                @else
                    <div class="border border-[#30363d] rounded-md overflow-hidden">
                        <p class="p-3 text-sm text-gray-600 dark:text-gray-400">Tidak ada materi pembelajaran yang ditemukan
                            untuk elemen ini.</p>
                    </div>
                @endif

            </div>
            <!-- Fullscreen Modal for Content Preview -->
            <div x-data="{ show: @entangle('showFullscreenModal') }" x-show="show"
                class="absolute inset-0 z-50 flex flex-col bg-white dark:bg-black dark:bg-opacity-95 overflow-hidden" {{--
                Menambahkan overflow-hidden untuk menghilangkan scroll --}} x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" x-cloak>

                <!-- Modal Header -->
                <div
                    class="flex items-center justify-between px-6 py-4 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 text-black">
                    <h3 class="text-lg dark:text-white font-normal truncate" x-text="$wire.modalFileName"></h3>
                    <button wire:click="closeFullscreenModal"
                        class="text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors p-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-900 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="sr-only">Tutup</span>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="flex-1 w-full overflow-hidden bg-white dark:bg-zinc-900">
                    <iframe :src="$wire.modalContentUrl" class="w-full h-full border-0"></iframe>
                </div>
        @endif
    </div>
</div>