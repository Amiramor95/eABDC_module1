<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempLedger extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_LEDGER';

    protected $primaryKey = 'DISTRIBUTOR_TEMP_LEDGER_ID';

    public $timestamps = false;
}