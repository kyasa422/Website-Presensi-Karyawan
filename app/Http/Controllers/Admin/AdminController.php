<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <-- Jangan lupa import facade PDF

class AdminController extends Controller
{
    /**
     * Menampilkan halaman dashboard admin.
     */
    public function dashboard(Request $request)
    {
        $filterDate = $request->input('filter_date');
        $query = Presensi::with('user');

        if ($filterDate) {
            // Filter berdasarkan created_at agar Izin/Sakit tetap masuk
            $query->whereDate('created_at', $filterDate);
        }

        $riwayatPresensi = $query->latest()->get();
        $presensiHariIni = Presensi::whereDate('created_at', Carbon::today('Asia/Jakarta'))->count();

        return view('admin.dashboard', [
            'presensiHariIni' => $presensiHariIni,
            'presensi' => $riwayatPresensi,
            'filterDate' => $filterDate,
        ]);
    }

    /**
     * Membuat dan mengunduh file PDF untuk rekap presensi.
     * Bisa memfilter berdasarkan tanggal.
     */
    public function generateRekapPDF(Request $request)
    {
        // 1. Ambil tanggal filter dari request
        $filterDate = $request->input('filter_date');

        // 2. Siapkan query dasar
        $query = Presensi::with('user');
        $titleDate = "Semua Periode"; // Judul default

        // 3. Jika ada tanggal filter, tambahkan kondisi ke query
        if ($filterDate) {
            $query->whereDate('created_at', $filterDate);
            // Ubah judul untuk PDF
            $titleDate = "Tanggal " . Carbon::parse($filterDate)->translatedFormat('d F Y');
        }

        // 4. Eksekusi query
        $presensi = $query->latest()->get();

        // 5. Load view PDF dan teruskan data yang relevan
        $pdf = PDF::loadView('admin.rekap_presensi', [
            'presensi' => $presensi,
            'titleDate' => $titleDate
        ]);

        // 6. Buat nama file yang dinamis
        $fileName = 'rekap-presensi-';
        $fileName .= $filterDate ? $filterDate : 'semua';
        $fileName .= '.pdf';

        // 7. Download PDF
        return $pdf->download($fileName);
    }


    /**
     * Menghapus data presensi berdasarkan ID.
     */
    public function deletePresensi($id)
    {
        // 1. Temukan data presensi berdasarkan ID
        $presensi = Presensi::findOrFail($id);
        // 2. Hapus data presensi
        $presensi->delete();
        // 3. Redirect kembali ke halaman dashboard dengan pesan sukses
        return redirect()->route('admin.dashboard')->with('success', 'Presensi berhasil dihapus');
    
        
    
    }
}
