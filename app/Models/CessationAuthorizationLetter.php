<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class CessationAuthorizationLetter extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'CESSATION_AUTHORIZATION_LETTER';

    protected $primaryKey = 'CAL_DOCUMENT_ID';

    public $timestamps = false;
}