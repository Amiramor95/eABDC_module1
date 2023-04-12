<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SuspendRevokeAppealAppr extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'SUSPEND_REVOKE_APPEAL_APPR';

    protected $primaryKey = 'SUSPEND_REVOKE_APPEAL_APPR_ID';

    public $timestamps = false;
}