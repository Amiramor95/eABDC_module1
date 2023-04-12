<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserRegistrationDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_REGISTRATION_DOCUMENT';

    protected $primaryKey = 'USER_REGISTRATION_DOCUMENT_ID';

    public $timestamps = false;
}