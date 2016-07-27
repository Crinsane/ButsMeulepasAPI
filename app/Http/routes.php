<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function (\Illuminate\Filesystem\Filesystem $file) {
    $xml = $file->get(storage_path('app/inventory.xml'));

    dd(simplexml_load_string($xml));
});

$app->post('/inventory', function (\Illuminate\Http\Request $request, \Illuminate\Filesystem\Filesystem $file) {
    $file->put(storage_path('app/' . time() . '.xml'), $request->getContent());

    return 1;
});