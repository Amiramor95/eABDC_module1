<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorAdditionalInfo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_ADDITIONAL_INFO';

    protected $primaryKey = 'DISTRIBUTOR_ADDITIONAL_INFO_ID';

    public $timestamps = false;
}