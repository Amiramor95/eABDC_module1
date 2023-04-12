<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AcceptanceDetails extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'ACCEPTANCE_DETAILS';

    protected $primaryKey = 'ACCEPTANCE_DETAILS_ID';

    public $timestamps = false;

    protected $fillable = [
      'CANDIDATE_ACCEPTANCE_ID',
      'CANDIDATE_NAME',
      'CANDIDATE_NRIC',
      'CANDIDATE_PASSPORT_NO',
      'CANDIDATE_EMAIL',
      'CANDIDATE_PHONENO',
      'LICENSE_TYPE',
      'STAFF_OR_AGENT',
      'CA_CLASSIFICATION',
      'TS_ID'
        
    ];
}