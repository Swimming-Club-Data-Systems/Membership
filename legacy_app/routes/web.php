<?php

use Illuminate\Support\Facades\Route;

/**
 * SUPPORTED METHODS
 * 
 * Route::get($uri, $callback);
 * Route::post($uri, $callback);
 * Route::patch($uri, $callback);
 * Route::delete($uri, $callback);
 * Route::options($uri, $callback);
 * 
 * Route::match(['get', 'post'], '/', function () {
 *   //
 * });
 * 
 * Route::any('/', function () {
 *   //
 * });
 * 
 * DEPENDENCY INJECTION
 * 
 * use Illuminate\Http\Request;
 * 
 * Route::get('/users', function (Request $request) {
 *   // ...
 * });
 * 
 * POST, PUT, PATCH and DELETE require a CSRF value
 * 
 * Route::redirect('/here', '/there', 301); (response code optional)
 * 
 */

Route::get('/', function () {

});