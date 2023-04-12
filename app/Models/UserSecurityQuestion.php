<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserSecurityQuestion extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'USER_SECURITY_QUESTION';

    protected $primaryKey = 'USER_SECURITY_QUESTION_ID';

    public $timestamps = false;
}