<?php

namespace App\Http\Resources\Distributor_Module;

use Illuminate\Http\Resources\Json\JsonResource;

class SubsequentExtensionRequest extends JsonResource
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
            'SUBSEQUENT_EXTENSION_REQUEST_ID' => $this->SUBSEQUENT_EXTENSION_REQUEST_ID,
            'EXTENSION_REQUEST_ID' => $this->EXTENSION_REQUEST_ID,
            'DISTRIBUTOR_ID' => $this->DISTRIBUTOR_ID,
            'EXTENSION_TYPE' => $this->EXTENSION_TYPE,
            'OTHER_EXTENSION_TYPE' => $this->OTHER_EXTENSION_TYPE,
            'JUSTIFICATION' => $this->JUSTIFICATION,
            'EXTENSION_END_DATE' => $this->EXTENSION_END_DATE,
            'TS_ID' => $this->TS_ID,
            'EXTENSION_APPROVAL_DATE' => $this->EXTENSION_APPROVAL_DATE,
            'SUBMISSION_DATE' => $this->SUBMISSION_DATE,
            'EXTENSION_REQUEST' => new ExtensionRequest($this->whenLoaded('extensionRequest')),
            'DOCUMENTS' => SubsequentExtensionRequestDocuments::collection($this->whenLoaded('documents')),
            'MANAGER_APPROVAL' => new ManagerApproval($this->whenLoaded('managerApproval')),
            'RD_APPROVAL' => new RDApproval($this->whenLoaded('rdApproval')),
            'RD_HOD_APPROVAL' => new RDApproval($this->whenLoaded('rdHodApproval')),
            'GM_APPROVAL' => new RDApproval($this->whenLoaded('gmApproval')),
            'CEO_APPROVAL' => new RDApproval($this->whenLoaded('ceoApproval'))
        ];
    }
}
