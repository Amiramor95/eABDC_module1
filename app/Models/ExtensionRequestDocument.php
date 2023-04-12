<?php

namespace App\Models;

use App\Http\Controllers\response;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ExtensionRequestDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'EXTENSION_REQUEST_DOCUMENTS';

    protected $primaryKey = 'EXTENSION_REQUEST_DOCUMENT_ID';

    protected $fillable = [
        'EXTENSION_REQUEST_DOCUMENT_ID',
        'EXTENSION_REQUEST_ID',
        'DOCUMENT_NAME',
        'DOCUMENT_BLOB',
        'DOCUMENT_TYPE',
        'DOCUMENT_SIZE',
        'IS_ACTION_PLAN'
    ];

    // protected $casts = [
    //     'EXTENSION_REQUEST_DOCUMENT_ID' => 'integer',
    //     'EXTENSION_REQUEST_ID' => 'integer',
    //     'DOCUMENT_NAME' => 'string',
    //     'DOCUMENT_TYPE' => 'string',
    //     'IS_ACTION_PLAN' => 'boolean'
    // ];

    public $timestamps = true;

    public function extensionRequest()
    {
        return $this->belongsTo(ExtensionRequest::class, 'EXTENSION_REQUEST_ID', 'EXTENSION_REQUEST_ID');
    }
}
