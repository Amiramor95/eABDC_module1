<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserDivision extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_DIVISION';

    protected $primaryKey = 'USER_DIVISION_ID';

    public $timestamps = false;
}