<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CessationFimmDoc extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'CESSATION_FIMM_DOC';

    protected $primaryKey = 'CFD_DOCUMENT_ID';

    public $timestamps = false;
}