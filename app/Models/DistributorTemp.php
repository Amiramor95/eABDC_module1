<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTemp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'DISTRIBUTOR_TEMP';

    protected $primaryKey = 'DIST_TEMP_ID';

    public $timestamps = false;
}
