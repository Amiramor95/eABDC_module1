<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SuspendRevokeAppealDoc extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'SUSPEND_REVOKE_APPEAL_DOC';

    protected $primaryKey = 'SR_APPEAL_DOC_ID';

    public $timestamps = false;
}