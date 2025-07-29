<x-app-layout>
    {{-- CSS Kustom untuk membuat tabel menjadi responsif seperti kartu di mobile --}}
    <style>
        @media (max-width: 767px) {
            .responsive-table thead {
                display: none;
            }
            .responsive-table tr {
                display: block;
                border: 1px solid #e2e8f0;
                border-radius: 0.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            }
            .responsive-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid #edf2f7;
                text-align: right;
            }
            .responsive-table td:last-child {
                border-bottom: none;
            }
            .responsive-table td::before {
                content: attr(data-label);
                font-weight: 600;
                text-align: left;
                padding-right: 1rem;
            }
        }
    </style>

    <div class="container mx-auto flex flex-col lg:flex-row space-y-8 lg:space-y-0 lg:space-x-8 text-gray-950">
        <!-- Panel Kiri: Profil Pengguna (Tidak berubah) -->
        <div class="w-full lg:w-1/3 bg-white p-6 rounded-lg shadow-md">
            <div class="text-center">
                <button onclick="my_modal_1.showModal()">
                    <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://static.vecteezy.com/system/resources/previews/033/541/875/original/user-thick-line-filled-dark-colors-free-vector.jpg' }}"
                         alt="Avatar" class="rounded-full w-16 h-16 mx-auto">
                </button>
                <dialog id="my_modal_1" class="modal bg-gray-500 bg-opacity-50 backdrop-blur-md">
                    <div class="modal-box bg-slate-200 p-6 rounded-lg shadow-lg max-w-md w-full">
                        <h2 class="text-2xl font-bold mb-4 text-black">Upload Foto Anda</h2>
                        <form method="POST" action="{{ route('user.uploadProfilePicture') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="profile_picture" class="file-input w-full max-w-xs mb-4" />
                            <div class="modal-action flex justify-between">
                                <button type="submit" class="btn btn-primary bg-red-500 border-none hover:bg-slate-100 text-white">Upload</button>
                                <button class="btn btn-secondary bg-green-600 border-none hover:bg-slate-100 text-white" type="button" onclick="my_modal_1.close()">Close</button>
                            </div>
                        </form>
                    </div>
                </dialog>
                <h3 class="text-xl font-semibold mt-4">{{ Auth::user()->name }}</h3>
                <p class="text-gray-500">{{ Auth::user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="border-gray-300 border-[1px] text-gray-950 py-2 px-4 rounded hover:bg-gray-300 w-full text-xl font-bold">Logout</button>
                </form>
            </div>
        </div>

        <!-- Panel Kanan: Riwayat Presensi (Sudah Responsif) -->
        <div x-data="{ isImageModalOpen: false, modalImageUrl: '' }" class="w-full lg:w-2/3 bg-white p-6 rounded-lg shadow-md">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 space-y-4 md:space-y-0">
                <h2 class="text-2xl font-bold">Riwayat Presensi</h2>
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 w-full md:w-auto">
                    <!-- FORM FILTER TANGGAL -->
                    <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center space-x-2 w-full sm:w-auto">
                        <input type="date" name="filter_date" value="{{ $filterDate ?? '' }}" class="border-gray-300 rounded-md shadow-sm text-sm w-full">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">Filter</button>
                        <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-3 rounded text-sm">Reset</a>
                    </form>
                    <!-- TOMBOL EXPORT PDF DINAMIS -->
                    @php
                        $exportParams = $filterDate ? ['filter_date' => $filterDate] : [];
                    @endphp
                    <a href="{{ route('admin.presensi.rekap.pdf', $exportParams) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm w-full sm:w-auto justify-center">
                        <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z" /></svg>
                        <span>Export PDF</span>
                    </a>
                </div>
            </div>

            <table class="w-full text-left responsive-table">
                <thead>
                    <tr>
                        <th class="px-4 py-2">No</th>
                        <th class="px-4 py-2">Foto</th>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Lokasi</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Check In</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Alasan</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($presensi as $index => $item)
                        <tr class="border-b">
                            <td data-label="No">{{ $loop->iteration }}</td>
                            <td data-label="Foto">
                                @php
                                    $fotoPath = $item->foto_lokasi ? str_replace('public/', '', $item->foto_lokasi) : null;
                                @endphp
                                @if ($fotoPath)
                                    <button type="button" @click="isImageModalOpen = true; modalImageUrl = '{{ asset('storage/' . $fotoPath) }}'">
                                        <img src="{{ asset('storage/' . $fotoPath) }}" alt="Foto Lokasi" class="rounded-full w-10 h-10 object-cover cursor-pointer hover:opacity-80 transition ml-auto">
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Nama">{{ $item->user->name }}</td>
                            <td data-label="Lokasi">{{ $item->lokasi ?? '-' }}</td>
                            <td data-label="Tanggal">{{ $item->created_at->format('d M Y') }}</td>
                            <td data-label="Check In">{{ $item->clock_in ? $item->clock_in->format('H:i:s') : '-' }}</td>
                            <td data-label="Status">{{ $item->status }}</td>
                            <td data-label="Alasan">{{ $item->catatan ? $item->catatan : '-' }}</td>
                            <td data-label="Aksi">
                                <form action="{{ route('admin.presensi.delete', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus presensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form> 
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Tidak ada data presensi yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Modal untuk Tampilan Gambar (tidak berubah) -->
            <div x-show="isImageModalOpen" @keydown.escape.window="isImageModalOpen = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75" style="display: none;">
                <div @click.away="isImageModalOpen = false" class="relative bg-white p-4 rounded-lg shadow-lg max-w-3xl max-h-[90vh]">
                    <button @click="isImageModalOpen = false" class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-xl font-bold z-10">&times;</button>
                    <img :src="modalImageUrl" alt="Tampilan Penuh" class="object-contain max-w-full max-h-[85vh]">
                </div>
            </div>

        </div>
    </div>
</x-app-layout>