<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserSalutation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_SALUTATION';

    protected $primaryKey = 'USER_SALUTATION_ID';

    public $timestamps = false;
}