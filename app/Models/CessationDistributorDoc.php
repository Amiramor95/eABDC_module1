<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CessationDistributorDoc extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'CESSATION_DISTRIBUTOR_DOC';

    protected $primaryKey = 'CD_DOCUMENT_ID';

    public $timestamps = false;
}