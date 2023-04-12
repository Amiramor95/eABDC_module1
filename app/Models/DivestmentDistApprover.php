<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DivestmentDistApprover extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'DIVESTMENT_DIST_APPROVAL';

    protected $primaryKey = 'DIVE_DIST_APPR_ID';

    public $timestamps = false;
}
