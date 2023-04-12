<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTypeRegistrationApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL';

    protected $primaryKey = 'DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL_ID';

    public $timestamps = false;
}