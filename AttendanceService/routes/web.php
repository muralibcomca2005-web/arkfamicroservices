<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('attendance-service')->controller(AttendanceController::class)->middleware(['auth:sanctum'])->name('attendance.')->group(function () {
    Route::post('/students/{student}/classes/{liveClass}', 'autoAttendance')->name('mark');
    Route::get('/classes/{liveClass}', 'getAttendanceList')->name('list');
    Route::patch('/{attendance}/review/{teacher}', 'reviewAttendance')->name('review');
    Route::get('/students/{student}/total-classes', 'totalClassesAttended')->name('total-classes');
    Route::get('/students/{student}/total-time', 'totalHrs')->name('total-time');
});
