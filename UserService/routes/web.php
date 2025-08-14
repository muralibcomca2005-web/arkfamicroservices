<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('user-service')->group(function () {
    Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::post('/students', [StudentController::class, 'addStudent'])->name('students.register');

    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/logout', 'logout');
        Route::get('/profile', 'profile')->middleware('auth:sanctum');
    });

    Route::controller(TeacherController::class)->prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', 'fetchTeachers')->name('index');
        Route::post('/', 'addTeacher')->name('store');
        Route::get('/{teacher}/courses', 'fetchAssignedCourses')->name('courses');
        Route::get('/{teacher}/students', 'fetchAssignedStudents')->name('students');
    });
    
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('/', 'fetchUsers')->name('index');
        Route::post('/', 'addAdmin')->name('store');
    });
});
