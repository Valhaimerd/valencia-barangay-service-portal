<?php

use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
