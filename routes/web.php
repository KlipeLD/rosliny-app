<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\PlantEntryController;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canRegister' => Features::enabled(Features::registration()),
//     ]);
// })->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// require __DIR__.'/settings.php';


Route::get('/', fn() => redirect()->route('plants.index'));

Route::resource('plants', PlantController::class);
// Route::get('plants', [PlantController::class, 'index'])->name('plants.index');
// Route::get('plants/{plant}', [PlantController::class, 'show'])->name('plants.show');
Route::get('plants/{plant}/entries', [PlantEntryController::class, 'index'])->name('plants.entries.index');
Route::get('entries/{entry}/edit', [PlantEntryController::class, 'edit'])->name('entries.edit');
Route::patch('entries/{entry}', [PlantEntryController::class, 'update'])->name('entries.update');
Route::delete('entries/{entry}', [PlantEntryController::class, 'destroy'])->name('entries.destroy');

Route::post('plants/{plant}/entries/fetch', [PlantEntryController::class, 'fetchFromApi'])->name('plants.entries.fetch');
Route::get('entries/{entry}/edit', [PlantEntryController::class, 'edit'])->name('entries.edit');
Route::patch('entries/{entry}', [PlantEntryController::class, 'update'])->name('entries.update');