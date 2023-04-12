<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SuspendRevoke extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'SUSPEND_REVOKE';

    protected $primaryKey = 'SUSPEND_REVOKE_ID';

    public $timestamps = false;
}