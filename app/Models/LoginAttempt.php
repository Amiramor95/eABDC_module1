<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = "login_attempt";

    protected $fillable = [
		'limitAttempt','blockDuration'
	];
}
