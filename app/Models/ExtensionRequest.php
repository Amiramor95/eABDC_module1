<?php

namespace App\Models;

use App\Http\Controllers\response;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ExtensionRequest extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'EXTENSION_REQUEST';

    protected $primaryKey = 'EXTENSION_REQUEST_ID';

    protected $fillable = [
        'EXTENSION_REQUEST_ID',
        'SUBMISSION_DATE',
        'DISTRIBUTOR_ID',
        'CREATED_BY',
        'EXTENSION_TYPE',
        'OTHER_EXTENSION_TYPE',
        'JUSTIFICATION',
        'EXTENSION_END_DATE',
        'EXTENSION_STATUS_ID',
        'RETURN_DATELINE',
        'EXTENSION_APPROVAL_DATE',
        'FIRST_NOTIFICATION',
        'SECOND_NOTIFICATION',
        'FINAL_NOTIFICATION',
    ];

    public $timestamps = true;

    public function author()
    {
        return $this->belongsTo(User::class, 'CREATED_BY', 'USER_ID');
    }

    public function documents()
    {
        return $this->hasMany(ExtensionRequestDocument::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID');
    }

    public function subsequentRequests()
    {
        return $this->hasMany(SubsequentExtensionRequest::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID');
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'DISTRIBUTOR_ID', 'DISTRIBUTOR_ID');
    }

    public function approvals()
    {
        return $this->hasMany(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_SUBSEQUENT' => false]);
    }

    public function managerApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where('IS_FIMM', false);
    }

    public function rdApproval()
    {
        // return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 85]);
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 85, 'IS_SUBSEQUENT' => false]);
    }

    public function rdHodApproval()
    {
        // return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 86]);
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 86, 'IS_SUBSEQUENT' => false]);
    }

    public function gmApproval()
    {
        // return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 87]);
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 87, 'IS_SUBSEQUENT' => false]);
    }

    public function ceoApproval()
    {
        // return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 88]);
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 88, 'IS_SUBSEQUENT' => false]);
    }

    public function fimmApproval()
    {
        return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where('IS_FIMM', true);
    }

    public function approvalLogs()
    {
        return $this->hasManyThrough(ExtensionRequestApprovalLog::class, ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_APPROVAL_ID', 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_APPROVAL_ID');
    }

    // public function subsequentManagerApproval()
    // {
    //     return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => false, 'IS_SUBSEQUENT' => true]);
    // }

    // public function subsequentRdApproval()
    // {
    //     return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 85, 'IS_SUBSEQUENT' => true]);
    // }

    // public function subsequentRdHodApproval()
    // {
    //     return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 86, 'IS_SUBSEQUENT' => true]);
    // }

    // public function subsequentGmApproval()
    // {
    //     return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 87, 'IS_SUBSEQUENT' => true]);
    // }

    // public function subsequentCeoApproval()
    // {
    //     return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'APPROVAL_LEVEL_ID' => 88, 'IS_SUBSEQUENT' => true]);
    // }

    // public function subsequentFimmApproval()
    // {
    //     return $this->hasOne(ExtensionRequestApproval::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID')->where(['IS_FIMM' => true, 'IS_SUBSEQUENT' => true]);
    // }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'EXTENSION_STATUS_ID', 'TS_ID');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
