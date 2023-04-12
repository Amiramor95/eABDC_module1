<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DivestmentFundSelectionTemp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DIVESTMENT_FUND_SELECTION_TEMP';

    protected $primaryKey = 'DIVE_FUND_SELECTION_ID';

    public $timestamps = false;
}