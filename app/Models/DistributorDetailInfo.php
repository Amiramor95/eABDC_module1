<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorDetailInfo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_DETAIL_INFO';

    protected $primaryKey = 'DIST_INFO_ID';

    public $timestamps = false;
}