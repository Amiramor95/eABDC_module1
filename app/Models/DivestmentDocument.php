<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DivestmentDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DIVESTMENT_DOCUMENT';

    protected $primaryKey = 'DIVE_DOCU_ID';

    public $timestamps = false;
}