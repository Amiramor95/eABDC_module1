<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempAdditionalInfo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_ADDITIONAL_INFO';

    protected $primaryKey = 'DIST_TEMP_ADDITIONAL_INFO_ID';

    public $timestamps = false;
}