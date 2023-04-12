<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempRepresentative extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_REPRESENTATIVE';

    protected $primaryKey = 'DIST_TEMP_REPR_ID';

    public $timestamps = false;
}