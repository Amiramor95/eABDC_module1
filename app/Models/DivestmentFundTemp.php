<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DivestmentFundTemp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DIVESTMENT_FUND_TEMP';

    protected $primaryKey = 'DIVE_FUND_TEMP_ID';

    public $timestamps = false;
}