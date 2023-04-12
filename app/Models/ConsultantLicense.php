<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ConsultantLicense extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'CONSULTANT_LICENSE';

    protected $connection = 'consultant_management';

    protected $primaryKey = 'CONSULTANT_LICENSE_ID';

    public $timestamps = false;
}