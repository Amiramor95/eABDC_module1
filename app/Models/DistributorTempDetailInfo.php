<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempDetailInfo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_DETAIL_INFO';

    protected $primaryKey = 'DIST_TEMP_INFO_ID';

    public $timestamps = false;
}