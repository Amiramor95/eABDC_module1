<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER';

    protected $primaryKey = 'USER_ID';

    public $timestamps = false;
}