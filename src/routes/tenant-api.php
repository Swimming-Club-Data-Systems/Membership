<?php

/*
|--------------------------------------------------------------------------
| Tenant Api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register api routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the 'api' middleware group. Now create something great!
|
*/

use App\Models\Central\Tenant;

Route::get('/tenant', function () {
    return new \App\Http\Resources\Central\TenantResource(tenant());
});

Route::middleware('auth:api')->group(function () {
    Route::get('/userinfo', function () {
        return new \App\Http\Resources\Tenant\UserResource(Auth::user());
    })->middleware('scope:profile');
});
