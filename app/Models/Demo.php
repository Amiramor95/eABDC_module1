<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demo extends Model
{
    protected $table = "demo";
    public $timestamps = true;

    protected $fillable = [
		'make','model'
	];
}
