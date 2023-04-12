<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DivestmentFund extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DIVESTMENT_FUND';

    protected $primaryKey = 'DIVE_FUND_ID';

    public $timestamps = false;
}