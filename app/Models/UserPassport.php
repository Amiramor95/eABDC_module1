<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserPassport extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_PASSPORT';

    protected $primaryKey = 'USER_PASSPORT_ID';

    public $timestamps = false;
}