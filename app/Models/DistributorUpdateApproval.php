<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class DistributorUpdateApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'DISTRIBUTOR_UPDATE_APPROVAL';

    protected $primaryKey = 'DISTRIBUTOR_UPDATE_APPROVAL_ID';

    public $timestamps = false;

    protected $fillable = [
        'DIST_TEMP_ID',
        'DIST_ID',
        'APPR_GROUP_ID',
        'APPROVAL_LEVEL_ID',
        'APPROVAL_INDEX',
        'APPROVAL_STATUS',
        'APPROVAL_USER',
        'APPROVAL_REMARK_PROFILE',
        'APPROVAL_REMARK_DETAILINFO',
        'APPROVAL_REMARK_CEOnDIR',
        'APPROVAL_REMARK_ARnAAR',
        'APPROVAL_REMARK_PAYMENT',
    ];
}