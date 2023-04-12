<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempAddress extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_ADDRESS';

    protected $primaryKey = 'DIST_TEMP_ADDR_ID';

    public $timestamps = false;
}