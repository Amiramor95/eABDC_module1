<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SuspendRevokeAppealApprDoc extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'SUSPEND_REVOKE_APPEAL_APPRDOC';

    protected $primaryKey = 'SR_APPRD_ID';

    public $timestamps = false;
}