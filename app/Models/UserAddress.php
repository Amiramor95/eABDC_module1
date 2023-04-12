<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserAddress extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_ADDRESS';

    protected $primaryKey = 'USER_ADDRESS_ID';

    public $timestamps = false;
}