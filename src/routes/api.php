<?php

use App\Http\Controllers\Central\Api\Internal;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth.internal')->prefix('internal')->group(function () {
    Route::get('/application-menu/{id}', [Internal::class, 'getMenu'])->withoutMiddleware('throttle:api');

    Route::post('/notify/email', [Internal::class, 'triggerEmailSend'])->withoutMiddleware('throttle:api');
});

Route::get('/tenants', function () {
    return new \App\Http\Resources\Central\TenantCollection(Tenant::paginate());
});
Route::get('/tenants/{id}', function (int $id) {
    return new \App\Http\Resources\Central\TenantResource(Tenant::findOrFail($id));
});
