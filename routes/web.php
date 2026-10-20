<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/assessment/start', [AssessmentController::class, 'start'])->name('assessment.start');
Route::get('/assessment', [AssessmentController::class, 'show'])->name('assessment.show');
Route::post('/assessment', [AssessmentController::class, 'store'])->name('assessment.store');

Route::get('/results/{token}', [ResultController::class, 'show'])->name('results.show')->where('token', '^[a-z0-9]{16}$');

Route::get('/compare/{tokenA}', [CompareController::class, 'start'])->name('compare.start')->where('tokenA', '^[a-z0-9]{16}$');
Route::get('/compare/{tokenA}/{tokenB}', [CompareController::class, 'show'])->name('compare.show')->where(['tokenA' => '^[a-z0-9]{16}$', 'tokenB' => '^[a-z0-9]{16}$']);
Route::post('/results/{token}/feedback', [ResultController::class, 'storeFeedback'])->name('results.feedback')->where('token', '^[a-z0-9]{16}$')->middleware('throttle:5,1');
