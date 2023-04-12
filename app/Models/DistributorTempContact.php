<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorTempContact extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_TEMP_CONTACT';

    protected $primaryKey = 'DISTRIBUTOR_TEMP_CONTACT_ID';

    public $timestamps = false;
}