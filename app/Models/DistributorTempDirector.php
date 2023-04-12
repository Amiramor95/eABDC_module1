<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempDirector extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_DIRECTOR';

    protected $primaryKey = 'DISTRIBUTOR_TEMP_DIRECTOR_ID';

    public $timestamps = false;
}