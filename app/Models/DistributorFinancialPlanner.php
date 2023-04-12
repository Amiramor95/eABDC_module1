<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorFinancialPlanner extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_FINANCIAL_PLANNER';

    protected $primaryKey = 'DIST_FP_ID';

    public $timestamps = false;
}