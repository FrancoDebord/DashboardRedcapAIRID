<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('interface-accueil');
});

Route::get('/pull-data', [App\Http\Controllers\AccueilProjectController::class, 'pullDataFromRedCap'])
    ->name('pullDataFromRedCap');

Route::get('/pull-data-2', [App\Http\Controllers\AccueilProjectController::class, 'pullDataFromRedCapAnGambiaeFINAL'])
    ->name('pullDataFromRedCapAnGambiaeFINAL');


Route::get('/pull-data-all-mosquitoes', [App\Http\Controllers\AccueilProjectController::class, 'pullDataFromRedCapAllMosquitoesFINAL'])
    ->name('pullDataFromRedCapAllMosquitoesFINAL');

Route::get('/pull-queries-data-redcap-baseline', [App\Http\Controllers\AccueilProjectController::class, 'pullQueriesDataREDCapBaseline'])
    ->name('pullQueriesDataREDCapBaseline');
