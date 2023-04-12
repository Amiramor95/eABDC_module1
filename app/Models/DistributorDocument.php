<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorDocument extends Model// implements Auditable
{
    // use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_DOCUMENT';

    protected $primaryKey = 'DIST_DOCU_ID';

    public $timestamps = false;
}