<?php

namespace App\Http\Resources\Distributor_Module;

use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\This;

class ExtensionRequestDocument extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'EXTENSION_REQUEST_DOCUMENT_ID' => $this->EXTENSION_REQUEST_DOCUMENT_ID,
            // 'EXTENSION_REQUEST_ID' => $this->EXTENSION_REQUEST_ID,
            'DOCUMENT_NAME' => $this->DOCUMENT_NAME,
            'DOCUMENT_BLOB' => base64_encode($this->DOCUMENT_BLOB),
            'DOCUMENT_TYPE' => $this->DOCUMENT_TYPE,
            'DOCUMENT_SIZE' => $this->DOCUMENT_SIZE,
            'IS_ACTION_PLAN' => (bool) $this->IS_ACTION_PLAN
        ];
    }
}
