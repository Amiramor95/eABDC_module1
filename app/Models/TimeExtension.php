<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TimeExtension extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'TIME_EXTENSION';

    protected $primaryKey = 'TIME_EXTENSION_ID';

    public $timestamps = false;
}