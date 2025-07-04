<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LogQrController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/log-qr', [LogQrController::class, 'store'])->middleware('auth');

Route::get('/ambil-data', [ExportController::class, 'index'])->name('export');
Route::get('/ambil-data/all', [ExportController::class, 'exportAll'])->name('export.all');
Route::get('/ambil-data/range', [ExportController::class, 'exportRange'])->name('export.qrlog.range');
Route::get('/ambil-data/preset', [ExportController::class, 'exportPreset'])->name('export.qrlog.preset');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
