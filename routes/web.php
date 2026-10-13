<?php

use App\Http\Controllers\AssessmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/assessment/start', [AssessmentController::class, 'start'])->name('assessment.start');
Route::get('/assessment', [AssessmentController::class, 'show'])->name('assessment.show');
