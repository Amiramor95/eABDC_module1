<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SubsequentExtensionRequestDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'SUBSEQUENT_EXTENSION_REQUEST_DOCUMENT';

    protected $primaryKey = 'SUBSEQUENT_EXTENSION_REQUEST_DOCUMENT_ID';

    protected $fillable = [
        'SUBSEQUENT_EXTENSION_REQUEST_DOCUMENT_ID',
        'SUBSEQUENT_EXTENSION_REQUEST_ID',
        'DOCUMENT_NAME',
        'DOCUMENT_BLOB',
        'DOCUMENT_TYPE',
        'DOCUMENT_SIZE',
        'IS_ACTION_PLAN'
    ];

    public $timestamps = false;

    public function subsequentExtensionRequest()
    {
        return $this->belongsTo(SubsequentExtensionRequest::class, 'SUBSEQUENT_EXTENSION_REQUEST_ID', 'SUBSEQUENT_EXTENSION_REQUEST_ID');
    }
}
