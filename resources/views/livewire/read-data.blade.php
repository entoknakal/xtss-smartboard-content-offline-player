<div class="relative w-full">
    <div class="w-full bg-white dark:bg-zinc-800 overflow-hidden shadow-xl sm:rounded-lg p-4 sm:p-6">
        <div class="block w-full border-b border-gray-200 dark:border-zinc-700 pb-4 mb-6">
            <span class="text-2xl sm:text-3xl font-extrabold text-gray-800 dark:text-white block">
                {{ $nama_sekolah }}
            </span>
        </div>

        <!-- DROPDOWN AREA -->
        <div
            class="grid grid-cols-1 lg:grid-cols-5 gap-4 w-full mb-4 [&_label]:text-gray-500 [&_label]:dark:text-gray-400 [&_label]:font-semibold [&_label]:text-xs [&_label]:uppercase [&_label]:tracking-wider">
            <!-- 1. Dropdown Guru -->
            <div class="flex flex-col gap-2">
                <label>Nama Guru</label>
                <select wire:model.live="selectedGuru"
                    class="w-full bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 text-lg sm:text-xl rounded-lg p-2.5 sm:p-3 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Nama Guru</option>
                    @foreach ($list_guru as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Dropdown Kelas -->
            <div class="flex flex-col gap-2">
                <label>Nama Kelas</label>
                <select wire:model.live="selectedKelas" {{ empty($selectedGuru) ? 'disabled' : '' }}
                    class="w-full bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 text-lg sm:text-xl rounded-lg p-2.5 sm:p-3 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-zinc-800">
                    <option value="">Pilih Nama Kelas</option>
                    @foreach ($list_kelas as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Dropdown Mata Pelajaran -->
            <div class="flex flex-col gap-2">
                <label>Mata Pelajaran</label>
                <select wire:model.live="selectedMapel" {{ empty($selectedKelas) ? 'disabled' : '' }}
                    class="w-full bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 text-lg sm:text-xl rounded-lg p-2.5 sm:p-3 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-zinc-800">
                    <option value="">Pilih Mata Pelajaran</option>
                    @foreach ($list_mapel as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Dropdown Semester -->
            <div class="flex flex-col gap-2">
                <label>Semester</label>
                <select wire:model.live="selectedSemester" {{ empty($selectedMapel) ? 'disabled' : '' }}
                    class="w-full bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 text-lg sm:text-xl rounded-lg p-2.5 sm:p-3 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-zinc-800">
                    <option value="">Pilih Semester</option>
                    @foreach ($list_semester as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 5. Dropdown Elemen Pembelajaran -->
            <div class="flex flex-col gap-2">
                <label>Elemen Pembelajaran</label>
                <select wire:model.live="selectedElemen" {{ empty($selectedSemester) ? 'disabled' : '' }}
                    class="w-full bg-white dark:bg-zinc-700 border border-gray-300 dark:border-zinc-600 text-lg sm:text-xl rounded-lg p-2.5 sm:p-3 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-zinc-800">
                    <option value="">Pilih Elemen Pembelajaran</option>
                    @foreach ($list_elemen as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- END DROPDOWN AREA -->

        <!-- CONTENT AREA -->
        @if ($selectedGuru && $selectedKelas && $selectedMapel && $selectedSemester && $selectedElemen)
            @if ($selectedElemenMateri)
                <div
                    class="mt-6 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-4 sm:p-6 flex flex-col gap-4 shadow-sm">

                    <!-- Grid Row Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-gray-600 dark:text-gray-400 font-bold mb-1 text-lg sm:text-2xl">Kategori Mata
                                Pelajaran</div>
                            <div class="dark:text-white font-normal text-lg sm:text-2xl">
                                {{ $selectedElemenMateri['kategori_mata_pelajaran'] }}
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-600 dark:text-gray-400 font-bold mb-1 text-lg sm:text-2xl">Elemen Pembelajaran
                            </div>
                            <div class="dark:text-white font-normal text-lg sm:text-2xl">
                                {{ $selectedElemenMateri['elemen_pembelajaran'] }}
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-zinc-700">

                    <!-- Deskripsi -->
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 font-bold mb-1.5 text-lg sm:text-2xl">Deskripsi Elemen
                            Pembelajaran</div>
                        <div class="dark:text-white leading-relaxed text-justify text-lg sm:text-2xl">
                            {{ $selectedElemenMateri['deskripsi_elemen_pembelajaran'] }}
                        </div>
                    </div>

                    <!-- Tabel Bab & Materi -->
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 font-bold mb-2 text-lg sm:text-2xl">Materi Ajar</div>
                        @if (!empty($selectedElemenMateri['materi']))

                            <!-- TAMPILAN DESKTOP (Hidden di HP, Muncul di Layar md Keatas) -->
                            <div
                                class="hidden md:block border border-gray-200 dark:border-zinc-700 rounded-md overflow-hidden shadow-sm">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-teal-600 dark:bg-teal-700/80">
                                        <tr class="text-white font-bold">
                                            <th class="p-3 w-1/3 uppercase text-xl sm:text-2xl tracking-wider">Nama Bab</th>
                                            <th class="p-3 uppercase text-xl sm:text-2xl tracking-wider">Tujuan Pembelajaran</th>
                                            <th class="p-3 uppercase text-xl sm:text-2xl tracking-wider w-1/5 text-right">Alokasi
                                                Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                        @foreach ($selectedElemenMateri['materi'] as $materi)
                                            <tr
                                                class="align-top odd:bg-white even:bg-gray-50 dark:odd:bg-[#1c1e22] dark:even:bg-[#24272d]">
                                                <td class="p-3 font-bold text-gray-900 dark:text-gray-100 text-xl sm:text-2xl">
                                                    {{ $materi['nama_bab'] }}
                                                </td>
                                                <td class="p-3 text-gray-600 dark:text-gray-300 text-pretty">
                                                    <!-- ✨ PERBAIKAN DESKTOP: Mengembalikan style bullet list bawaan dengan prose classes kustom -->
                                                    <div
                                                        class="text-gray-700 dark:text-gray-300 text-lg sm:text-2xl leading-relaxed [&_ul]:list-disc! [&_ul]:pl-8! [&_ul]:my-2! [&_ol]:list-decimal! [&_ol]:pl-8! [&_ol]:my-2! [&_li]:my-1.5!">
                                                        {!! $materi['tujuan_pembelajaran'] !!}
                                                    </div>
                                                </td>
                                                <td class="p-3 text-right">
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full font-bold bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-100 shadow-sm text-xl sm:text-2xl">
                                                        {{ $materi['alokasi_waktu'] }} JP
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- TAMPILAN RESPONSIVE HP (Muncul di HP, Hidden di Layar Desktop) -->
                            <div class="grid grid-cols-1 gap-4 md:hidden w-full">
                                @foreach ($selectedElemenMateri['materi'] as $materi)
                                    <div
                                        class="w-full border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-gray-50 dark:bg-[#1c1e22] flex flex-col gap-3 shadow-sm">
                                        <!-- Header Card (Nama Bab & Alokasi JP) -->
                                        <div class="flex flex-col gap-1 border-b border-gray-200 dark:border-zinc-700 pb-2">
                                            <span
                                                class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">Nama
                                                Bab</span>
                                            <span
                                                class="font-bold text-gray-900 dark:text-gray-100 text-xl">{{ $materi['nama_bab'] }}</span>
                                        </div>

                                        <!-- Isi / Konten Tujuan Pembelajaran -->
                                        <div class="flex flex-col gap-1.5">
                                            <span
                                                class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">Tujuan
                                                Pembelajaran</span>
                                            <!-- ✨ PERBAIKAN HP: Mengembalikan style bullet list bawaan dengan prose classes kustom -->
                                            <div
                                                class="text-gray-700 dark:text-gray-300 text-lg sm:text-2xl leading-relaxed [&_ul]:list-disc! [&_ul]:pl-8! [&_ul]:my-2! [&_ol]:list-decimal! [&_ol]:pl-8! [&_ol]:my-2! [&_li]:my-1.5!">
                                                {!! $materi['tujuan_pembelajaran'] !!}
                                            </div>
                                        </div>

                                        <!-- Footer Waktu -->
                                        <div
                                            class="flex items-center justify-between mt-2 pt-2 border-t border-gray-200 dark:border-zinc-700">
                                            <span
                                                class="text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider">Alokasi
                                                Waktu</span>
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full font-bold bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-200 shadow-sm text-lg">
                                                {{ $materi['alokasi_waktu'] }} JP
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        @endif
                    </div>

                    <!-- File Materi Section -->
                    <div>
                        <div class="text-gray-600 dark:text-gray-400 font-bold mb-2 text-lg sm:text-2xl">File Materi</div>
                        @if (!empty($selectedElemenMateri['konten']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 w-full">
                                @foreach ($selectedElemenMateri['konten'] as $konten)
                                    <div
                                        class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 bg-gray-50 dark:bg-zinc-900/50 flex flex-col justify-between gap-3 shadow-sm">
                                        <div class="flex items-start gap-2 text-gray-600 dark:text-gray-300">
                                            <svg class="w-5 h-5 text-gray-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-sm sm:text-md font-medium line-clamp-2 break-all"
                                                title="{{ $konten['file_name'] }}">{{ $konten['file_name'] }}</span>
                                        </div>
                                        <button type="button"
                                            wire:click="openFileInModal('{{ $konten['fullFilePath'] }}', '{{ $konten['file_name'] }}')"
                                            class="w-full text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-1.5 px-4 rounded-md transition-all active:scale-95 dark:bg-amber-400 dark:text-zinc-900 dark:hover:bg-amber-500 text-xl sm:text-2xl shadow-sm">
                                            Preview
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-3 bg-gray-50 dark:bg-zinc-900/30">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada file media pendukung.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="mt-6 border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-gray-50 dark:bg-zinc-900/30">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada rincian materi pembelajaran yang ditemukan
                        untuk kombinasi ini.</p>
                </div>
            @endif
        @endif

        <!-- MODAL PREVIEW -->
        <div x-data="{ show: @entangle('showFullscreenModal') }" x-show="show"
            class="fixed inset-0 z-50 flex flex-col bg-white dark:bg-black dark:bg-opacity-95 overflow-hidden"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
            <div
                class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 text-black">
                <span class="text-sm sm:text-lg dark:text-white font-bold truncate">{{ $modalFileName }}</span>
                <button type="button" wire:click="closeFullscreenModal"
                    class="text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-zinc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-5 sm:w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 w-full overflow-hidden bg-white dark:bg-zinc-900">
                <iframe src="{{ $modalContentUrl }}" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>
</div>