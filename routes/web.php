<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return view("dashboard2");
    // return redirect()->route("pullDataFromRedCapAnGambiaeFINAL",["project_id"=>38]);
});

Route::get('/pull-data', [App\Http\Controllers\AccueilProjectController::class, 'pullDataFromRedCap'])
    ->name('pullDataFromRedCap');

Route::get('/pull-data-2', [App\Http\Controllers\AccueilProjectController::class, 'pullDataFromRedCapAnGambiaeFINAL'])
    ->name('pullDataFromRedCapAnGambiaeFINAL');


Route::get('/pull-data-all-mosquitoes', [App\Http\Controllers\AccueilProjectController::class, 'pullDataFromRedCapAllMosquitoesFINAL'])
    ->name('pullDataFromRedCapAllMosquitoesFINAL');

Route::get('/pull-queries-data-redcap-baseline', [App\Http\Controllers\AccueilProjectController::class, 'pullQueriesDataREDCapBaseline'])
    ->name('pullQueriesDataREDCapBaseline');

Route::get('/pull-queries-data-redcap-an-gambiae-final', [App\Http\Controllers\AccueilProjectController::class, 'pullQueriesDataREDCapAnGambiaeFINAL'])
    ->name('pullQueriesDataREDCapAnGambiaeFINAL');

Route::get('/pull-queries-data-redcap-all-mosquitoes-final', [App\Http\Controllers\AccueilProjectController::class, 'pullQueriesDataREDCapALlMosquitoesFINAL'])
    ->name('pullQueriesDataREDCapALlMosquitoesFINAL');

Route::get('/pull-queries-ajax', [App\Http\Controllers\AccueilProjectController::class, 'getQueriesAjax'])
    ->name('getQueriesAjax');


Route::get('/page-imprimer-queries', [App\Http\Controllers\AccueilProjectController::class, 'exporterQueries'])
    ->name('exporterQueries');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
