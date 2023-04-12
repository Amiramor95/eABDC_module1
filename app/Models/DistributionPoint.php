<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributionPoint extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTION_POINT';

    protected $primaryKey = 'DIST_POINT_ID';

    public $timestamps = false;

    protected $fillable = [
        'DISTRIBUTOR_ID',
        'DIST_POINT_CODE',
        'DIST_POINT_NAME',
        'PHONE_NUMBER',
        'DIST_ADDR_1',
        'DIST_ADDR_2',
        'DIST_ADDE_3',
        'DIST_POSTAL',
        'DIST_CITY',
        'DIST_COUNTRY',
        'DIST_STATE',
        'OTHER_STATE',
        'OTHER_CITY',
        'OTHER_POSTAL',
        'TS_ID',
        'CREATE_BY',
       
        
    ];
}