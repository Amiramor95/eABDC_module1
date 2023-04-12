<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorApprovalDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_APPROVAL_DOCUMENT';

    protected $primaryKey = 'DIST_APPR_DOC_ID';

    public $timestamps = false;
}