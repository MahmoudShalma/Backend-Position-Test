<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CourseController::class, 'index'])->name('courses.index');
Route::post('/fetch', [CourseController::class, 'fetch'])->name('courses.fetch');
Route::post('/stop', [CourseController::class, 'stop'])->name('courses.stop');
