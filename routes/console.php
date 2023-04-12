<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
// Artisan::command('migrate', function () {
//     $this->comment('You are not allowed to do this command for safety purpose!');
// })->describe('Override default command in production.');

// Artisan::command('migrate:fresh', function () {
//     $this->comment('You are not allowed to do this command for safety purpose!');
// })->describe('Override default command in production.');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
