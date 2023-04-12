<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ExtensionRequestApproval extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'EXTENSION_REQUEST_APPROVAL';

    protected $primaryKey = 'EXTENSION_REQUEST_APPROVAL_ID';

    protected $fillable = [
        'APPROVAL_GROUP_ID',
        'APPROVAL_LEVEL_ID',
        'EXTENSION_REQUEST_ID',
        'APPROVAL_REMARK',
        'TS_ID',
        'CREATED_BY',
        'APPROVAL_PUBLISH_STATUS',
        'APPROVAL_DATE',
        'IS_FIMM',
        'IS_SUBSEQUENT'
    ];

    protected $casts = [
        'IS_FIMM' => 'boolean'
    ];

    public $timestamps = true;

    public function documents()
    {
        return $this->hasMany(ExtensionRequestApprovalDocument::class, 'EXTENSION_REQUEST_APPROVAL_ID', 'EXTENSION_REQUEST_APPROVAL_ID');
    }

    public function extensionRequest()
    {
        return $this->belongsTo(ExtensionRequest::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID');
    }

    public function subsequentExtensionRequest()
    {
        return $this->belongsTo(SubsequentExtensionRequest::class, 'EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID');
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'TS_ID', 'TS_ID');
    }

    public function user()
    {
        return $this->belongsTo(FimmUser::class, 'CREATED_BY', 'USER_ID');
    }

    public function group()
    {
        return $this->belongsTo(FimmUserGroup::class, 'APPROVAL_GROUP_ID', 'MANAGE_GROUP_ID');
    }

}
