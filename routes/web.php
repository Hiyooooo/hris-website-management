<?php

use App\Http\Controllers\SiteContentController;
use App\Http\Controllers\SiteKeyController;
use App\Http\Controllers\SiteSeoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('sites.index'));

// TODO: bungkus dengan middleware auth setelah login selesai.
Route::get('/sites', [SiteContentController::class, 'index'])->name('sites.index');
Route::get('/sites/create', [SiteContentController::class, 'create'])->name('sites.create');
Route::post('/sites', [SiteContentController::class, 'store'])->name('sites.store');

// Workspace: Tab Konten & Tampilan
Route::get('/sites/{site}/content', [SiteContentController::class, 'edit'])->name('sites.content.edit');
Route::put('/sites/{site}/content', [SiteContentController::class, 'update'])->name('sites.content.update');

// Workspace: Tab SEO & OpenGraph
Route::get('/sites/{site}/seo', [SiteSeoController::class, 'edit'])->name('sites.seo.edit');
Route::put('/sites/{site}/seo', [SiteSeoController::class, 'update'])->name('sites.seo.update');

// Workspace: Tab Kunci Akses (API Credentials & Rotation)
Route::get('/sites/{site}/keys', [SiteKeyController::class, 'show'])->name('sites.keys.show');
Route::post('/sites/{site}/keys/regenerate', [SiteKeyController::class, 'regenerate'])->name('sites.keys.regenerate');
