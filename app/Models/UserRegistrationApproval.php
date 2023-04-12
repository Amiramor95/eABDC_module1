<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserRegistrationApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'USER_REGISTRATION_APPROVAL';

    protected $primaryKey = 'USER_REGI_APPR_ID';

    public $timestamps = false;
}
