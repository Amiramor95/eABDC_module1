<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;

class CommandController extends Controller
{
    public function seedData()
    {
        Artisan::call('migrate:fresh --seed --database="mysql"');
        Artisan::call('migrate:fresh --seed --database="mysql2"');

        return json_encode("data migration successful");
    }

    public function generateDoc()
    {
        Artisan::call('generate:doc');

        return json_encode("doc generated successful");
    }
}
