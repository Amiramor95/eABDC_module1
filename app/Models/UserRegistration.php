<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserRegistration extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_REGISTRATION';

    protected $primaryKey = 'USER_REGISTRATION_ID';

    public $timestamps = false;
}