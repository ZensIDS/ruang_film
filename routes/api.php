<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

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

// Laravolt
Route::get('/wilayah/kabupaten/{code}', function ($code) {
    return City::where('province_code', $code)->orderBy('name')->get(['code', 'name']);
});

Route::get('/wilayah/kecamatan/{code}', function ($code) {
    return District::where('city_code', $code)->orderBy('name')->get(['code', 'name']);
});

Route::get('/wilayah/desa/{code}', function ($code) {
    return Village::where('district_code', $code)->orderBy('name')->get(['code', 'name']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
