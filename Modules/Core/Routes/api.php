<?php
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/plugins', function (Request $request) {
    // return $request->plugins();
})->middleware('auth:api');


Route::get('/themes', function (Request $request) {
    // return $request->themes();
})->middleware('auth:api');