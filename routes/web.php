<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('interface-accueil');
});

Route::get('/pull-data', [App\Http\Controllers\AccueilProjectController::class, 'pullDataFromRedCap'])
    ->name('pullDataFromRedCap');
