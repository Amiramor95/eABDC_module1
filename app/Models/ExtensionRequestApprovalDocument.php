<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ExtensionRequestApprovalDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'EXTENSION_REQUEST_APPROVAL_DOCUMENTS';

    protected $primaryKey = 'EXTENSION_REQUEST_APPROVAL_DOCUMENT_ID';

    protected $fillable = [
        'EXTENSION_REQUEST_APPROVAL_ID',
        'DOCUMENT_NAME',
        'DOCUMENT_BLOB',
        'DOCUMENT_TYPE',
        'DOCUMENT_SIZE',
    ];

    public $timestamps = true;
}
