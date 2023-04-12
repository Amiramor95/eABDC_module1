<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorRepresentative extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_REPRESENTATIVE';

    protected $primaryKey = 'DIST_REPR_ID';

    public $timestamps = false;
}