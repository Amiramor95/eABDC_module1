<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CessationFimmApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'CESSATION_FIMM_APPROVAL';

    protected $primaryKey = 'CESSATION_FIMM_APPROVAL_ID';

    public $timestamps = false;
}