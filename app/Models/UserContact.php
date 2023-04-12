<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserContact extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_CONTACT';

    protected $primaryKey = 'USER_CONTACT_ID';

    public $timestamps = false;
}