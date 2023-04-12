<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AcceptanceDetailsRejected extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'ACCEPTANCE_DETAILS_REJECTED';

    protected $primaryKey = 'ACCEPTANCE_DETAILS_REJECTED_ID';

    public $timestamps = false;

    protected $fillable = [
        'ACCEPTANCE_DETAILS_REJECTED_ID',
        'CANDIDATE_ACCEPTANCE_ID',
        'CANDIDATE_NAME',
        'CANDIDATE_NRIC',
        'CANDIDATE_PASSPORT_NO',
        'CANDIDATE_EMAIL',
        'CANDIDATE_PHONENO',
        'LICENSE_TYPE',
        'STAFF_OR_AGENT',
        'CA_CLASSIFIFCATION',
        'REASON'
          
      ];
}