<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistRunno extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DIST_RUNNO';

    protected $primaryKey = 'DIST_RUNNO_ID';

    public $timestamps = false;
}