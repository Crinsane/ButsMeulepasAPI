<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('extract', function (\App\Extractors\XmlExtractor $extractor) {
    $xml = \Illuminate\Support\Facades\File::get(storage_path('app/inventory2.xml'));

    $extractor->process($xml);
});