<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/users')->group(function () {
  Route::get('/', [UserController::class, 'index']);
  Route::get('/{id}', [UserController::class, 'show'])->name('user.show');
});