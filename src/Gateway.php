<?php

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
Route::macro('attResource', function ($prefix, $controller) {
    Route::prefix($prefix)->group(function () use ($controller) {
        Route::get('/', [$controller, 'index']);
        Route::get('datatables', [$controller, 'datatables']);
        Route::get('lov', [$controller, 'lov']);
        Route::post('table', [$controller, 'table']);
        Route::get('{id}', [$controller, 'show']);
        Route::post('/',[$controller, 'store']);
        Route::put('{id}',[$controller,'update']);
        Route::delete('{id}',[$controller,'destroy']);
        Route::get('detail/dictionary', [$controller, 'dictionary']);
    });
});
