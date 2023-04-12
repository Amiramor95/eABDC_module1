<?php

namespace App\Models;

use App\Http\Controllers\response;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SubsequentExtensionRequest extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'SUBSEQUENT_EXTENSION_REQUEST';

    protected $primaryKey = 'SUBSEQUENT_EXTENSION_REQUEST_ID';

    protected $fillable = [
        'EXTENSION_REQUEST_ID',
        'DISTRIBUTOR_ID',
        'CREATED_BY',
        'EXTENSION_TYPE',
        'OTHER_EXTENSION_TYPE',
        'JUSTIFICATION',
        'EXTENSION_END_DATE',
        'TS_ID',
        'RETURN_DATELINE',
        'EXTENSION_APPROVAL_DATE',
        'SUBMISSION_DATE',
        'FIRST_NOTIFICATION',
        'SECOND_NOTIFICATION',
        'FINAL_NOTIFICATION',
        'IS_NOTIFIED'
    ];

    public $timestamps = true;

    public function author()
    {
        return $this->belongsTo(User::class, 'CREATED_BY', 'USER_ID');
    }

    public function extensionRequest()
    {
        return $this->belongsTo(ExtensionRequest::class , 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID');
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'DISTRIBUTOR_ID', 'DISTRIBUTOR_ID');
    }

    public function documents()
    {
        return $this->hasMany(SubsequentExtensionRequestDocument::class, 'SUBSEQUENT_EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID');
    }

    public function managerApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID')->where(['IS_FIMM' => false, 'IS_SUBSEQUENT' => true]);
    }

    public function rdApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 85, 'IS_SUBSEQUENT' => true]);
    }

    public function rdHodApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 86, 'IS_SUBSEQUENT' => true]);
    }

    public function gmApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 87, 'IS_SUBSEQUENT' => true]);
    }

    public function ceoApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 88, 'IS_SUBSEQUENT' => true]);
    }

    public function fimmApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'IS_SUBSEQUENT' => true]);
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'TS_ID', 'TS_ID');
    }

    public function approvalLogs()
    {
        return $this->hasManyThrough(ExtensionRequestApprovalLog::class, ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_APPROVAL_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_APPROVAL_ID');
    }
}
