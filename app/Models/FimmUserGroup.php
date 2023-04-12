<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FimmUserGroup extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'MANAGE_GROUP';

    protected $primaryKey = 'MANAGE_GROUP_ID';

    protected $connection = 'module0';

    public $timestamps = false;
}
