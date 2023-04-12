<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_APPROVAL';

    protected $primaryKey = 'DIST_APPROVAL_ID';

    public $timestamps = false;
}