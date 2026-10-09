<?php

use App\Http\Controllers\Api\V1\SiteConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['verify.site.secret', 'throttle:120,1'])
    ->group(function () {
        Route::get('/site/config', [SiteConfigController::class, 'show']);
    });