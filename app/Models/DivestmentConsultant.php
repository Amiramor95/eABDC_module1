<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DivestmentConsultant extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DIVESTMENT_CONSULTANT';

    protected $primaryKey = 'DIVE_CONS_ID';

    public $timestamps = false;
}