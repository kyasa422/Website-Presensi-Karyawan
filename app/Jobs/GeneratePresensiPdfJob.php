<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Import semua yang kita butuhkan
use App\Models\Presensi;
use App\Models\User; // Pastikan Anda punya model User
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GeneratePresensiPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filterDate;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct($filterDate, User $user)
    {
        $this->filterDate = $filterDate;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // =======================================================
        // SEMUA LOGIKA BERAT ADA DI SINI
        // =======================================================

        // 1. Ambil data presensi berdasarkan filter
        $query = Presensi::with('user');
        $titleDate = "Semua Periode";
        if ($this->filterDate) {
            $query->whereDate('created_at', $this->filterDate);
            $titleDate = "Tanggal " . \Carbon\Carbon::parse($this->filterDate)->translatedFormat('d F Y');
        }
        $presensi = $query->latest()->get();

        // 2. Proses gambar ke Base64 (logika cerdas yang sudah kita buat)
        foreach ($presensi as $item) {
            $item->foto_base64 = null;
            if (!$item->foto_lokasi)
                continue;

            $rawPath = $item->foto_lokasi;
            $cleanPath = (strpos($rawPath, 'public/') === 0) ? substr($rawPath, strlen('public/')) : $rawPath;
            $fullPath = storage_path('app/public/' . $cleanPath);

            if (file_exists($fullPath)) {
                $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                $data = file_get_contents($fullPath);
                $item->foto_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // 3. Generate PDF
        $pdf = PDF::loadView('admin.rekap_presensi', [
            'presensi' => $presensi,
            'titleDate' => $titleDate
        ]);


        $pdf->download();


        // // 4. Simpan PDF ke storage, jangan di-download
        // $fileName = 'rekap-presensi-' . ($this->filterDate ?? 'semua') . '-' . time() . '.pdf';
        // Storage::disk('public')->put('rekap-pdf/' . $fileName, $pdf->output());

        // // 5. Beri tahu pengguna (Contoh: kirim notifikasi atau email)
        // // Untuk sekarang, kita bisa log saja sebagai bukti
        // \Illuminate\Support\Facades\Log::info("PDF '{$fileName}' telah berhasil dibuat untuk user: {$this->user->name}");

        // // Di aplikasi nyata, Anda akan mengirim notifikasi ke database atau email
        // // $this->user->notify(new \App\Notifications\PdfReadyNotification($fileName));



    }
}