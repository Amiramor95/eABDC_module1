<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CessationDistributorApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'CESSATION_DISTRIBUTOR_APPROVAL';

    protected $primaryKey = 'CESSATION_DISTRIBUTOR_APPROVAL_ID';

    public $timestamps = false;
}