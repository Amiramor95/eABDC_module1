<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FimmUser extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'USER';

    protected $primaryKey = 'USER_ID';

    protected $connection = 'module0';

    public $timestamps = false;
}
