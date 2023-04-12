<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Cessation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'CESSATION';

    protected $primaryKey = 'CESSATION_ID';

    public $timestamps = false;
}