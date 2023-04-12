<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SuspendRevokeAppeal extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'SUSPEND_REVOKE_APPEAL';

    protected $primaryKey = 'SUSPEND_REVOKE_APPEAL_ID';

    public $timestamps = false;
}