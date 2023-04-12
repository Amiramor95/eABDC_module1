<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Consultant extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'CONSULTANT';

    protected $connection = 'consultant_management';

    protected $primaryKey = 'CONSULTANT_ID';

    public $timestamps = false;
}