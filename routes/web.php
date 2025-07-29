<?php




// Route::middleware(['auth'])->group(function () {
//     Route::get('/dashboard', [PresensiController::class, 'index'])->name('dashboard');
//     Route::post('/clock-in', [PresensiController::class, 'clockIn'])->name('presensi.clockIn');
//     Route::get('/clock-out', [PresensiController::class, 'clockOut'])->name('presensi.clockOut');
//     Route::get('/presensi/izin', [PresensiController::class, 'izinForm'])->name('presensi.izinForm');
//     Route::post('/presensi/izin', [PresensiController::class, 'izinSubmit'])->name('presensi.izinSubmit');
//     Route::get('/presensi/riwayat', [PresensiController::class, 'riwayat'])->name('presensi.riwayat');
//     Route::get('/presensi/rekap/pdf', [PresensiController::class, 'generateRekapPDF'])->name('presensi.rekap.pdf');

// });


// Route::post('/upload-profile-picture', [UserController::class, 'uploadProfilePicture'])->name('user.uploadProfilePicture');

// Route::view('/', 'home');

// Route::middleware(['auth', 'admin'])->group(function () {
//     // Ganti Route::view dengan Route::get yang mengarah ke controller
//     Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

 
// });

// Route::view('dashboard', [PresensiController::class, 'presensi'])
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

// Route::post('/logout', function () {
//     Auth::logout();
//     return redirect('/');
// })->name('logout');


// require __DIR__.'/auth.php';

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminController;

// Halaman utama yang bisa diakses semua orang
Route::view('/', 'home');

// --- ROUTE UNTUK MAHASISWA ---
// Semua route di dalam grup ini hanya bisa diakses oleh user dengan role 'mahasiswa'
Route::middleware(['auth', 'verified', 'role:mahasiswa'])->group(function () {
    Route::get('/dashboard', [PresensiController::class, 'index'])->name('dashboard');
    Route::post('/clock-in', [PresensiController::class, 'clockIn'])->name('presensi.clockIn');
    Route::get('/clock-out', [PresensiController::class, 'clockOut'])->name('presensi.clockOut');
    Route::get('/presensi/izin', [PresensiController::class, 'izinForm'])->name('presensi.izinForm');
    Route::post('/presensi/izin', [PresensiController::class, 'izinSubmit'])->name('presensi.izinSubmit');
    Route::get('/presensi/riwayat', [PresensiController::class, 'riwayat'])->name('presensi.riwayat');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function() {
    Route::get('admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    // Ganti PresensiController menjadi AdminController untuk route ini
Route::get('/admin/presensi/rekap/pdf', [AdminController::class, 'generateRekapPDF'])->name('admin.presensi.rekap.pdf');
    Route::delete('/admin/presensi/{id}', [AdminController::class, 'deletePresensi'])->name('admin.presensi.delete');
});

// --- ROUTE UMUM UNTUK SEMUA USER YANG LOGIN ---
Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
    Route::post('/upload-profile-picture', [UserController::class, 'uploadProfilePicture'])->name('user.uploadProfilePicture');
    Route::get('/presensi/rekap/pdf', [PresensiController::class, 'generateRekapPDF'])->name('presensi.rekap.pdf');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// Route untuk otentikasi (login, register, dll)
require __DIR__.'/auth.php';
