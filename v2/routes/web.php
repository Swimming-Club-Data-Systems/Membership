<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Central/Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/laravel', function () {
    return Inertia::render('Central/Laravel', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('laravel');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth:central', 'verified'])->name('dashboard');

Route::get('/dev/form-components', function () {
    return Inertia::render('Dev/FormComponents');
});

Route::get('/go', function () {
    ddd("Hey");
})->middleware(['auth:central', 'verified']);

require __DIR__.'/central/auth.php';
