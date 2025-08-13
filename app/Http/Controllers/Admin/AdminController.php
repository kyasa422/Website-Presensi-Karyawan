<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <-- Jangan lupa import facade PDF
use App\Jobs\GeneratePresensiPdfJob;

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

    /*
|--------------------------------------------------------------------------
| File: generateRekapPDF (Controller Anda)
|--------------------------------------------------------------------------
*/

// di AdminController.php

public function generateRekapPDF(Request $request)
{
    // Ambil filter tanggal dan user yang sedang login
    $filterDate = $request->input('filter_date');
    $user = auth()->user();

    // Kirim pekerjaan ke antrean
    GeneratePresensiPdfJob::dispatch($filterDate, $user);

    // Langsung kembalikan respons ke pengguna
    return back()->with('success', 'Laporan PDF sedang diproses. Anda akan diberitahu jika sudah siap.');
}
    // public function generateRekapPDF(Request $request)
    // {
    //     // ... (kode filter Anda dari no 1-3 sudah bagus) ...
    //     $query = Presensi::with('user');
    //     $titleDate = "Semua Periode";

    //     if ($request->input('filter_date')) {
    //         $filterDate = $request->input('filter_date');
    //         $query->whereDate('created_at', $filterDate);
    //         $titleDate = "Tanggal " . \Carbon\Carbon::parse($filterDate)->translatedFormat('d F Y');
    //     }

    //     $presensi = $query->latest()->get();

    //     // --- TAMBAHAN: Proses gambar menjadi Base64 ---
    //     // --- GANTI BLOK FOREACH ANDA DENGAN INI UNTUK DEBUGGING ---
    //     foreach ($presensi as $item) {
    //         $item->foto_base64 = null; // Set nilai default

    //         // Lewati item ini jika kolom foto_lokasi kosong
    //         if (!$item->foto_lokasi) {
    //             continue;
    //         }

    //         // --- LOGIKA CERDAS UNTUK MENANGANI PATH ---

    //         // 1. Ambil path mentah dari database
    //         $rawPath = $item->foto_lokasi;

    //         // 2. "Bersihkan" path: Hapus awalan 'public/' jika ada.
    //         $cleanPath = $rawPath;
    //         if (strpos($rawPath, 'public/') === 0) {
    //             // substr() lebih aman daripada str_replace() dalam kasus ini
    //             $cleanPath = substr($rawPath, strlen('public/'));
    //         }

    //         // 3. Bangun path absolut yang sekarang sudah pasti konsisten
    //         $fullPath = storage_path('app/public/' . $cleanPath);

    //         // --- AKHIR LOGIKA CERDAS ---


    //         // 4. Lanjutkan proses konversi Base64 seperti biasa
    //         if (file_exists($fullPath)) {
    //             $type = pathinfo($fullPath, PATHINFO_EXTENSION);
    //             $data = file_get_contents($fullPath);
    //             $item->foto_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    //         }
    //     }
    //     $pdf = PDF::loadView('admin.rekap_presensi', [
    //         'presensi' => $presensi,
    //         'titleDate' => $titleDate
    //     ]);

    //     // ... (kode generate nama file dan download sudah bagus) ...
    //     $fileName = 'rekap-presensi-';
    //     $fileName .= $request->input('filter_date') ? $request->input('filter_date') : 'semua';
    //     $fileName .= '.pdf';

    //     return $pdf->download($fileName);
    // }

    // /**
    //  * Membuat dan mengunduh file PDF untuk rekap presensi.
    //  * Bisa memfilter berdasarkan tanggal.
    //  */
    // public function generateRekapPDF(Request $request)
    // {
    //     // 1. Ambil tanggal filter dari request
    //     $filterDate = $request->input('filter_date');

    //     // 2. Siapkan query dasar
    //     $query = Presensi::with('user');
    //     $titleDate = "Semua Periode"; // Judul default

    //     // 3. Jika ada tanggal filter, tambahkan kondisi ke query
    //     if ($filterDate) {
    //         $query->whereDate('created_at', $filterDate);
    //         // Ubah judul untuk PDF
    //         $titleDate = "Tanggal " . Carbon::parse($filterDate)->translatedFormat('d F Y');
    //     }

    //     // 4. Eksekusi query
    //     $presensi = $query->latest()->get();

    //     // 5. Load view PDF dan teruskan data yang relevan
    //     $pdf = PDF::loadView('admin.rekap_presensi', [
    //         'presensi' => $presensi,
    //         'titleDate' => $titleDate
    //     ]);

    //     // 6. Buat nama file yang dinamis
    //     $fileName = 'rekap-presensi-';
    //     $fileName .= $filterDate ? $filterDate : 'semua';
    //     $fileName .= '.pdf';

    //     // 7. Download PDF
    //     return $pdf->download($fileName);
    // }


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
