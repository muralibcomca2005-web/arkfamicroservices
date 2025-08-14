<?php

use App\Http\Controllers\LiveClassController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('live-class-service')->controller(LiveClassController::class)->middleware(['auth:sanctum'])->name('live-classes.')->group(function () {
    Route::prefix('live-classes')->group(function () {
        Route::get('/', 'fetchAllClasses')->name('index');
        Route::post('/', 'addClass')->name('store');
        Route::post('/upcoming', 'fetchClasses')->name('upcoming');
        Route::patch('/{liveClass}/end', 'endClass')->name('end');
    });
});
