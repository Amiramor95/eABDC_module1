<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class suspendRevokeDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'SUSPEND_REVOKE_DOCUMENT';

    protected $primaryKey = 'SR_DOCUMENT_ID';

    public $timestamps = false;
}