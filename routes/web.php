<?php

use App\Http\Controllers\SiteContentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('sites.index'));

// TODO: bungkus dengan middleware auth setelah login selesai.
Route::get('/sites', [SiteContentController::class, 'index'])->name('sites.index');
Route::get('/sites/{site}/content', [SiteContentController::class, 'edit'])->name('sites.content.edit');
Route::put('/sites/{site}/content', [SiteContentController::class, 'update'])->name('sites.content.update');
