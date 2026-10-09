<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

require __DIR__.'/publico.php';

// Hosts sin cron nativo (p. ej. Render free tier) disparan el scheduler de
// Laravel pegándole a esta ruta con un cron externo gratuito. Protegida por
// un token para que nadie más pueda forzar la ejecución de jobs programados.
Route::get('/cron/{token}', function (string $token) {
    abort_unless(
        config('app.cron_token') && hash_equals(config('app.cron_token'), $token),
        404
    );

    Artisan::call('schedule:run');

    return response('OK');
})->name('cron.run');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/arbitro.php';
require __DIR__.'/delegado.php';
