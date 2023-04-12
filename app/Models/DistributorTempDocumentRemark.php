<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempDocumentRemark extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_DOCUMENT_REMARK';

    protected $primaryKey = 'DISTRIBUTOR_TEMP_DOCUMENT_REMARK_ID';

    public $timestamps = false;
}