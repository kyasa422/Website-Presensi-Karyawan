<x-app-layout>

    {{-- CSS Kustom untuk membuat tabel riwayat menjadi responsif di mobile --}}
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
                color: #4a5568;
                /* Warna label */
            }
        }
    </style>
    <div class="container mx-auto flex flex-col lg:flex-row space-y-8 lg:space-y-0 lg:space-x-8 text-gray-950">
        <!-- Panel Kiri: Profil Pengguna dan Presensi -->
        <div class="w-full lg:w-1/3 bg-white p-6 rounded-lg shadow-md">
            <div class="text-center">
                <button onclick="my_modal_1.showModal()">
                    <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://static.vecteezy.com/system/resources/previews/033/541/875/original/user-thick-line-filled-dark-colors-free-vector.jpg' }}"
                        alt="Avatar" class="rounded-full w-16 h-16">
                </button>
                <dialog id="my_modal_1" class="modal bg-gray-500 bg-opacity-50 backdrop-blur-md">
                    <div class="modal-box bg-slate-200 p-6 rounded-lg shadow-lg max-w-md w-full">
                        <h2 class="text-2xl font-bold mb-4 text-black">Upload Foto Anda</h2>
                        <p class="mb-4 text-black">Silakan unggah foto profil Anda.</p>
                        <form method="POST" action="{{ route('user.uploadProfilePicture') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="profile_picture" class="file-input w-full max-w-xs mb-4" />
                            <div class="modal-action flex justify-between">
                                <button type="submit"
                                    class="btn btn-primary bg-red-500 border-none hover:bg-slate-100 text-white">Upload</button>
                                <button class="btn btn-secondary bg-green-600 border-none hover:bg-slate-100 text-white"
                                    type="button" onclick="my_modal_1.close()">Close</button>
                            </div>
                        </form>
                    </div>
                </dialog>

                <h3 class="text-xl font-semibold mt-4">{{ Auth::user()->name }}</h3>
                <p class="text-gray-500">{{ Auth::user()->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="border-gray-300 border-[1px] text-gray-950 py-2 px-4 rounded hover:bg-gray-300 w-full text-xl font-bold ">
                        Logout
                    </button>
                </form>
            </div>

            <div class="mt-8 bg-gray-100 p-4 rounded-lg">
                @if(session('success'))
                    <div class="bg-green-500 text-white p-4 rounded-lg mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-500 text-white p-4 rounded-lg mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <h4 class="text-xl font-semibold mb-2">Presensi</h4>
                <p class="">Lakukan Check in dan Check out untuk melengkapi daftar hadir harian anda</p>

                <p class="text-gray-700 mt-4 text-center">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}
                </p>
                <p class="bg-custom-gradient text-2xl font-bold mt-2 text-center">
                    {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat(' H:i:s') }}
                </p>



                <div class="flex  sm:flex-row  mt-6  sm:space-y-0 sm:space-x-4 flex-row justify-between ">
                    <!-- Tombol Check-In -->
                    <div x-data="presensiModal()">
                        {{-- Gunakan variabel $hasTakenActionToday untuk disable tombol --}}
                        <button @click.prevent="openModal()"
                            class="{{ $hasTakenActionToday ? 'bg-gray-300 text-gray-500' : 'bg-red-500 text-white' }} py-1 sm:py-2 px-4 sm:px-6 rounded-lg flex items-center space-x-2"
                            {{ $hasTakenActionToday ? 'disabled' : '' }}>

                            <i data-feather="log-in"></i>

                            <div class="flex flex-col text-left">
                                {{-- Logika untuk menampilkan teks tombol --}}
                                @if ($todayPresensi)
                                    {{-- Jika ada data, cek apakah itu check-in atau izin --}}
                                    @if ($todayPresensi->clock_in)
                                        <p class="text-sm sm:text-base">Check-In</p>
                                        <p class="text-xs sm:text-sm">
                                            {{ $todayPresensi->clock_in->format('H:i:s') }}
                                        </p>
                                    @else
                                        {{-- Jika bukan check-in, berarti itu Izin/Sakit --}}
                                        <p class="text-sm sm:text-base">Izin Diajukan</p>
                                        <p class="text-xs sm:text-sm">
                                            {{ $todayPresensi->status }}
                                        </p>
                                    @endif
                                @else
                                    {{-- Jika belum ada data sama sekali --}}
                                    <p class="text-sm sm:text-base">Check-In</p>
                                    <p class="text-xs sm:text-sm">-</p>
                                @endif
                            </div>
                        </button>

                        <div x-show="isModalOpen" @keydown.escape.window="isModalOpen = false"
                            class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
                            aria-modal="true">
                            <div
                                class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="isModalOpen" x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0" @click="isModalOpen = false"
                                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                                    aria-hidden="true"></div>

                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"
                                    aria-hidden="true">&#8203;</span>
                                <div x-show="isModalOpen" x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                    class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                        Konfirmasi Kehadiran
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500" x-text="locationStatus"></p>
                                    </div>

                                    <form @submit.prevent="submitPresensi($event)" class="mt-4 space-y-4">
                                        <div>
                                            <label for="foto" class="block text-sm font-medium text-gray-700">Ambil Foto
                                                Selfie</label>
                                            <input type="file" name="foto_lokasi" id="foto" accept="image/*"
                                                capture="user" required
                                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                onchange="if(this.files[0] && this.files[0].size > 2 * 1024 * 1024){ alert('Ukuran gambar maksimal 2MB!'); this.value=''; }">
                                            <p class="mt-1 text-sm text-gray-500">Format: JPG, PNG, Max: 2MB</p>
                                        </div>

                                        <div>
                                            <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan
                                                (Opsional)</label>
                                            <textarea name="catatan" id="catatan" rows="3"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                        </div>

                                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" :disabled="isSubmitting || !locationName"
                                                class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:bg-gray-400">
                                                <span x-show="isSubmitting">Memproses...</span>
                                                <span x-show="!isSubmitting">Konfirmasi Check-In</span>
                                            </button>
                                            <button type="button" @click="isModalOpen = false"
                                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function presensiModal() {
                            return {
                                isModalOpen: false,
                                isSubmitting: false,
                                locationStatus: 'Mencari lokasi Anda...',
                                locationName: '',

                                openModal() {
                                    this.isModalOpen = true;
                                    this.getLocation();
                                },

                                getLocation() {
                                    if (!navigator.geolocation) {
                                        this.locationStatus = 'Geolocation tidak didukung oleh browser ini.';
                                        return;
                                    }

                                    navigator.geolocation.getCurrentPosition(
                                        (position) => {
                                            this.locationStatus = 'Lokasi ditemukan! Mengambil nama lokasi...';
                                            this.getCityFromCoordinates(position.coords.latitude, position.coords.longitude);
                                        },
                                        () => {
                                            this.locationStatus = 'Gagal mengakses lokasi. Pastikan Anda telah memberikan izin lokasi.';
                                        }
                                    );
                                },

                                // Menggunakan API gratis OpenStreetMap Nominatim
                                async getCityFromCoordinates(lat, lon) {
                                    try {
                                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`);
                                        const data = await response.json();

                                        // Cek seluruh isi data untuk debugging jika perlu
                                        // console.log(data.address);

                                        // --- PERUBAIKAN DI SINI ---
                                        // Prioritaskan field yang paling mungkin berisi nama kecamatan
                                        const kecamatan = data.address.suburb ||
                                            data.address.city_district ||
                                            data.address.quarter ||
                                            data.address.neighbourhood ||
                                            data.address.county;

                                        const city = data.address.city || data.address.town || data.address.village;

                                        // Gabungkan dengan rapi, hindari "undefined, Bandar Lampung"
                                        this.locationName = [kecamatan, city].filter(Boolean).join(', ');

                                        if (this.locationName) {
                                            this.locationStatus = `📍 Lokasi Anda: ${this.locationName}`;
                                            console.log(data.address.suburb, data.address.city_district, data.address.quarter, data.address.neighbourhood, data.address.county);
                                        } else {
                                            this.locationStatus = 'Nama lokasi tidak ditemukan.';
                                        }

                                        console.log(" longitude:", lon, "latitude:", lat);

                                    } catch (error) {
                                        this.locationStatus = 'Gagal mendapatkan nama lokasi.';
                                        console.error("Error reverse geocoding:", error);
                                    }
                                },

                                async submitPresensi(event) {
                                    this.isSubmitting = true;

                                    const form = event.target;
                                    const formData = new FormData(form);
                                    formData.append('lokasi', this.locationName);

                                    // Ambil CSRF token
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                    try {
                                        const response = await fetch("{{ route('presensi.clockIn') }}", {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': csrfToken,
                                                'Accept': 'application/json',
                                            },
                                            body: formData
                                        });

                                        const result = await response.json();

                                        if (!response.ok) {
                                            console.error('Submit error:', result);
                                            this.isSubmitting = false;
                                            alert(result.error || 'Terjadi kesalahan.');
                                        } else {
                                            alert(result.message);
                                            window.location.reload(); // Refresh halaman setelah berhasil
                                        }

                                    } catch (error) {

                                        console.error('Submit error:', error);
                                        alert('Gagal mengirim data presensi.');
                                    } finally {
                                        this.isSubmitting = false;
                                        this.isModalOpen = false;
                                    }
                                }
                            }
                        }
                    </script>

                    </form>
                </div>


                {{-- izin modal --}}
                <div class="text-center mt-4">
                    <p>Tidak Hadir? <button onclick="my_modal_4.showModal()"> <span
                                class="bg-custom-gradient font-semibold">Klik Disini</span></button></p>
                </div>
                <dialog id="my_modal_4" class="modal bg-gray-500 bg-opacity-50 backdrop-blur-md">
                    <div class="modal-box w-11/12 max-w-5xl bg-white">
                        <h2 class="text-2xl font-bold mb-4">Lapor Presensi KKN Universitas Lampung</h2>

                        {{-- PENTING: Tambahkan enctype untuk upload file --}}
                        <form method="POST" action="{{ route('presensi.izinSubmit') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <p class="my-5">Form ini digunakan untuk melaporkan kehadiran tanpa check in</p>
                                <label for="keterangan"
                                    class="block text-sm font-medium text-gray-700">Keterangan</label>
                                <select name="keterangan" id="keterangan"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="Izin">Izin</option>
                                    <option value="Sakit">Sakit</option>
                                </select>
                            </div>

                            {{-- Tambahkan input file di sini --}}
                            <div class="mb-4">
                                <label for="foto_lokasi" class="block text-sm font-medium text-gray-700">Upload Foto
                                    Bukti (Opsional)</label>
                                <input type="file" name="foto_lokasi" id="foto_lokasi" accept="image/*"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>

                            <div class="mb-4">
                                <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan</label>
                                <textarea name="catatan" id="catatan" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                            </div>
                            <div class="flex justify-between">
                                <button class="btn px-4 py-2 rounded bg-sky-600 border-none text-white font-bold"
                                    type="button" onclick="my_modal_4.close()">Batal</button>
                                <button type="submit"
                                    class="bg-lime-800 btn text-white px-4 py-2 rounded border-none hover:bg-pink-300">Submit</button>
                            </div>
                        </form>
                    </div>
                </dialog>
            </div>
        </div>


        <!-- Panel Kanan: Riwayat Presensi -->
        <!-- Panel Kanan: Riwayat Presensi (Sudah Responsif) -->
        <div x-data="{ isImageModalOpen: false, modalImageUrl: '' }"
            class="w-full lg:w-2/3 bg-white p-6 rounded-lg shadow-md">
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-2 sm:space-y-0">
                <h2 class="text-2xl font-bold">Riwayat Presensi Saya</h2>
                <a href="{{ route('presensi.rekap.pdf') }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center w-full sm:w-auto justify-center">
                    <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z" />
                    </svg>
                    <span>Export ke PDF</span>
                </a>
            </div>

            {{-- SATU TABEL UNTUK SEMUA UKURAN LAYAR --}}
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($presensi as $item)
                        <tr class="border-b">
                            <td data-label="No">{{ $loop->iteration }}</td>
                            <td data-label="Foto">
                                @php
                                    $fotoPath = $item->foto_lokasi ? str_replace('public/', '', $item->foto_lokasi) : null;
                                @endphp
                                @if ($fotoPath)
                                    <button type="button"
                                        @click="isImageModalOpen = true; modalImageUrl = '{{ asset('storage/' . $fotoPath) }}'">
                                        <img src="{{ asset('storage/' . $fotoPath) }}" alt="Foto Lokasi"
                                            class="rounded-full w-10 h-10 object-cover cursor-pointer hover:opacity-80 transition ml-auto">
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Nama">{{ $item->user->name }}</td>
                            <td data-label="Lokasi">{{ $item->lokasi }}</td>
                            <td data-label="Tanggal">{{ $item->created_at->format('d M Y') }}</td>
                            <td data-label="Check In">{{ $item->clock_in ? $item->clock_in->format('H:i:s') : '-' }}</td>
                            <td data-label="Status">{{ $item->status }}</td>
                            <td data-label="Alasan">{{ $item->catatan ? $item->catatan : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Anda belum memiliki riwayat presensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Modal untuk Tampilan Gambar (tidak berubah) -->
            <div x-show="isImageModalOpen" @keydown.escape.window="isImageModalOpen = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
                style="display: none;">
                <div @click.away="isImageModalOpen = false"
                    class="relative bg-white p-4 rounded-lg shadow-lg max-w-3xl max-h-[90vh]">
                    <button @click="isImageModalOpen = false"
                        class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-xl font-bold z-10">&times;</button>
                    <img :src="modalImageUrl" alt="Tampilan Penuh" class="object-contain max-w-full max-h-[85vh]">
                </div>
            </div>
        </div>
    </div>


    {{-- <div class="w-full lg:w-2/3 bg-white p-6 rounded-lg shadow-md  md:hidden">
        <h2 class="text-2xl font-bold mb-4">Riwayat Presensi</h2>
        <table class="table-auto w-full text-left">
            <thead>
                <tr>
                    <th class=" py-2">No</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Waktu </th>

                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Alasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($presensi as $index => $item)
                <tr class="border-b">
                    <td class=" py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $item->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-2"> <span class="btn btn-sm bg-green-400 text-gray-950 my-1">
                            {{ $item->clock_in ? $item->clock_in->format('H:i:s') : '-' }} </span>
                        <span class="btn btn-sm  bg-button text-white">{{ $item->clock_out ?
                            $item->clock_out->format('H:i:s') : '-' }}</span>
                    </td>
                    <td class="px-4 py-2">{{ $item->status }}</td>
                    <td class="px-4 py-2">{{ $item->catatan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div> --}}



    </div>
</x-app-layout>