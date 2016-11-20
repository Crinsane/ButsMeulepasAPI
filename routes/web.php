<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/inventory', 'InventoryController@store');

Route::get('/test', function () {
    \Illuminate\Support\Facades\Storage::disk('local')->put('file.txt', print_r(request()->all(), true));
});