<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorDocumentRemark extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_DOCUMENT_REMARK';

    protected $primaryKey = 'DIST_DOCU_REMARK_ID';

    public $timestamps = false;
}