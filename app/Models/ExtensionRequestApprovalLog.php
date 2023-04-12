<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ExtensionRequestApprovalLog extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'EXTENSION_REQUEST_APPROVAL_LOGS';

    protected $primaryKey = 'EXTENSION_REQUEST_APPROVAL_LOG_ID';

    protected $fillable = [
        'EXTENSION_REQUEST_APPROVAL_ID',
        'APPROVAL_GROUP_ID',
        'USER_ID',
        'APPROVAL_ACTIVITY',
        'APPROVAL_REMARK',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(FimmUser::class, 'USER_ID', 'USER_ID');
    }

    public function approval()
    {
        return $this->belongsTo(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_APPROVAL_ID', 'EXTENSION_REQUEST_APPROVAL_ID');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
