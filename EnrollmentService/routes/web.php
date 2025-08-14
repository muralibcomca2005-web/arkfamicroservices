<?php

use App\Http\Controllers\EnrollmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(EnrollmentController::class)->middleware(['auth:sanctum'])->name('enrollments.')->group(function () {
    Route::post('/courses/{course}/students/{student}', 'enrollRequest')->name('store');
    Route::patch('/{id}/approve', 'enrollStudent')->name('approve');
    Route::get('/students/{student}/courses', 'fetchEnrolledCourse')->name('student-courses');
    Route::get('/students/{student}/courses/pending', 'fetchPendingRequest')->name('pending-courses');
    Route::get('/courses/pending', 'fetchAllPendingRequest')->name('all-pending-courses');
    Route::post('/pending/{id}/reject', 'rejectPendingRequest')->name('reject-pending-courses');
});
