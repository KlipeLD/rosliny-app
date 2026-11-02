<?php

use App\Http\Controllers\PlantController;
use App\Http\Controllers\PlantEntryController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('plants.index'))->name('home');

Route::resource('plants', PlantController::class);
Route::get('plants/{plant}/entries', [PlantEntryController::class, 'index'])->name('plants.entries.index');
Route::get('entries/{entry}/edit', [PlantEntryController::class, 'edit'])->name('entries.edit');
Route::patch('entries/{entry}', [PlantEntryController::class, 'update'])->name('entries.update');
Route::delete('entries/{entry}', [PlantEntryController::class, 'destroy'])->name('entries.destroy');

Route::post('plants/{plant}/entries/fetch', [PlantEntryController::class, 'fetchFromApi'])->name('plants.entries.fetch');
Route::post('plants/{plant}/entries/watering', [PlantEntryController::class, 'storeWatering'])->name('plants.entries.watering');
