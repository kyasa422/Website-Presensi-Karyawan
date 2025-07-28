<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <-- Import facade PDF


class PresensiController extends Controller
{
    // public function index()
    // {
    //     // Ambil presensi terakhir untuk user yang sedang login
    //     $latestPresensi = Presensi::where('user_id', Auth::id())
    //         ->latest()
    //         ->first();

    //     // Tentukan apakah user sudah clock-in dan clock-out hari ini
    //     $hasClockedIn = $latestPresensi && $latestPresensi->clock_in && $latestPresensi->created_at->isToday();
    //     $hasClockedOut = $latestPresensi && $latestPresensi->clock_out && $latestPresensi->created_at->isToday();

    //     // Ambil presensi untuk hari ini
    //     $todayPresensi = Presensi::where('user_id', Auth::id())
    //         ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
    //         ->first();

    //     // Ambil semua presensi untuk ditampilkan
    //     $presensi = Presensi::where('user_id', Auth::id())->get();

    //     return view('dashboard', compact('presensi', 'todayPresensi', 'hasClockedIn', 'hasClockedOut'));
    // }

    // public function clockIn(Request $request)
    // {
    //     $existingPresensi = Presensi::where('user_id', Auth::id())
    //         ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
    //         ->first();

    //     if ($existingPresensi) {
    //         // Jika sudah ada clockIn atau izin hari ini, tolak clockIn
    //         return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan presensi atau izin hari ini.');
    //     }

    //     $presensi = new Presensi();
    //     $presensi->user_id = Auth::id();
    //     $presensi->clock_in = Carbon::now('Asia/Jakarta');
    //     $presensi->status = 'Hadir';
    //     $presensi->save();

    //     return redirect()->route('dashboard')->with('success', 'Clock-in berhasil.');
    // }


    public function index()
    {
        // 1. Ambil data presensi atau izin untuk user yang login hari ini
        $todayPresensi = Presensi::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
            ->first();

        // 2. Buat variabel boolean untuk mengecek apakah sudah ada aksi (check-in atau izin)
        // Variabel ini akan bernilai true jika $todayPresensi tidak null
        $hasTakenActionToday = $todayPresensi !== null;

        // 3. Kirim kedua variabel ke view
        // Ambil semua presensi untuk ditampilkan
        $presensi = Presensi::where('user_id', Auth::id())->get();
        return view('dashboard', [
            'todayPresensi' => $todayPresensi,
            'hasTakenActionToday' => $hasTakenActionToday,
            'presensi' => $presensi,
        ]);
    }


    public function clockIn(Request $request)
    {

        // Validasi input dari form modal
        $request->validate([
            'lokasi' => 'required|string',
            'foto_lokasi' => 'required|image|max:10240', // Validasi file gambar
            'catatan' => 'nullable|string',
        ]);

        // Cek apakah hari ini sudah clock in
        $todayPresensi = Presensi::where('user_id', Auth::id())
                            ->whereDate('created_at', Carbon::today())
                            ->first();

        if ($todayPresensi) {
            return response()->json(['error' => 'Anda sudah melakukan check-in hari ini.'], 422);
        }

        // Simpan foto
        $path = $request->file('foto_lokasi')->store('public/presensi_photos');

        // Buat record presensi baru
        Presensi::create([
            'user_id' => Auth::id(),
            'clock_in' => now(),
            'lokasi' => $request->lokasi,
            'foto_lokasi' => $path, // Simpan path fotonya
            'status' => 'Hadir',
            'catatan' => $request->catatan,
        ]);

        return response()->json(['message' => 'Check-in berhasil!']);
    }

    public function clockOut(Request $request)
    {

        // Cek apakah sudah ada izin atau clockIn hari ini
        $presensi = Presensi::where('user_id', Auth::id())
            ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
            ->first();

        if (!$presensi || $presensi->clock_out || $presensi->status !== 'Hadir') {
            // Jika belum clockIn, sudah clockOut, atau status bukan 'Hadir', tolak clockOut
            return redirect()->route('dashboard')->with('error', 'Anda tidak dapat melakukan clock out saat ini.');
        }

        $presensi->clock_out = Carbon::now('Asia/Jakarta');
        $presensi->save();

        return redirect()->route('dashboard')->with('success', 'Clock-Out berhasil.');
    }

    public function izinForm()
    {
        return view('presensi.izin');
    }

   public function izinSubmit(Request $request)
{
    // Cek apakah sudah ada presensi atau izin hari ini (Logika ini sudah benar)
    $existingPresensi = Presensi::where('user_id', Auth::id())
        ->whereDate('created_at', Carbon::today('Asia/Jakarta'))
        ->first();

    if ($existingPresensi) {
        return redirect()->route('dashboard')->with('error', 'Anda sudah melakukan presensi atau izin hari ini.');
    }

    // 1. Tambahkan validasi untuk file foto
    $request->validate([
        'keterangan' => 'required|string',
        'foto_lokasi' => 'nullable|image|max:2048', // Opsional, harus gambar, maks 2MB
        'catatan' => 'nullable|string',
    ]);

    // 2. Siapkan data untuk disimpan
    $dataToSave = [
        'user_id' => Auth::id(),
        'status' => $request->keterangan,
        'catatan' => $request->catatan,
        // Set default value untuk kolom lain agar tidak null
        'clock_in' => null, 
    ];

    // 3. Cek jika ada file foto yang di-upload
    if ($request->hasFile('foto_lokasi')) {
        // Simpan foto ke folder 'izin_photos' di dalam 'storage/app/public'
        // dan dapatkan path-nya untuk disimpan di database
        $path = $request->file('foto_lokasi')->store('izin_photos', 'public');
        $dataToSave['foto_lokasi'] = $path;
    }

    // 4. Simpan semua data ke database menggunakan create
    Presensi::create($dataToSave);

    return redirect()->route('dashboard')->with('success', 'Laporan izin berhasil dikirim.');
}

public function generateRekapPDF()
    {
        // 1. Ambil ID dan nama pengguna yang sedang login
        $userId = Auth::id();
        $user = Auth::user();

        // 2. Ambil SEMUA data presensi HANYA untuk user ini
        $presensi = Presensi::where('user_id', $userId)
                            ->with('user')
                            ->latest()
                            ->get();

        // 3. Siapkan data untuk dikirim ke view PDF
        $data = [
            'presensi' => $presensi,
            'titleDate' => 'Rekap Semua Periode' // Judul untuk PDF
        ];

        // 4. Load view PDF dengan data yang sudah difilter
        $pdf = PDF::loadView('presensi_rekap_pdf', $data);

        // 5. Buat nama file yang dipersonalisasi
        $fileName = 'rekap-presensi-' . str_replace(' ', '-', strtolower($user->name)) . '-' . date('Y-m-d') . '.pdf';

        // 6. Download PDF
        return $pdf->download($fileName);
    }   
}
