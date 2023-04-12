<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DivestmentConsTemp extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DIVESTMENT_CONS_TEMP';

    protected $primaryKey = 'DIVE_CONS_TEMP_ID';

    public $timestamps = false;
}