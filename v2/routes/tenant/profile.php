<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/my-account', [ProfileController::class, 'index'])->name('myaccount.index');
Route::put('/my-account', [ProfileController::class, 'update'])->name('myaccount.update');
