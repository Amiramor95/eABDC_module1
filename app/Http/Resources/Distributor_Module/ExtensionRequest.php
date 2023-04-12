<?php

namespace App\Http\Resources\Distributor_Module;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtensionRequest extends JsonResource
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
            'EXTENSION_REQUEST_ID' => $this->EXTENSION_REQUEST_ID,
            'SUBMISSION_DATE' => $this->SUBMISSION_DATE,
            'DISTRIBUTOR_ID' => $this->DISTRIBUTOR_ID,
            'EXTENSION_TYPE' => $this->EXTENSION_TYPE,
            'OTHER_EXTENSION_TYPE' => $this->OTHER_EXTENSION_TYPE,
            'JUSTIFICATION' => $this->JUSTIFICATION,
            'EXTENSION_END_DATE' => $this->EXTENSION_END_DATE,
            'EXTENSION_STATUS_ID' => $this->EXTENSION_STATUS_ID,
            'EXTENSION_APPROVAL_DATE' => $this->EXTENSION_APPROVAL_DATE,
            'RETURN_DATELINE' => $this->RETURN_DATELINE,
            'DATE_CREATED' => $this->created_at,
            'DOCUMENTS' => ExtensionRequestDocument::collection($this->whenLoaded('documents')),
            'MANAGER_APPROVAL' => new ManagerApproval($this->whenLoaded('managerApproval')),
            'RD_APPROVAL' => new RDApproval($this->whenLoaded('rdApproval')),
            'RD_HOD_APPROVAL' => new RDApproval($this->whenLoaded('rdHodApproval')),
            'GM_APPROVAL' => new RDApproval($this->whenLoaded('gmApproval')),
            'CEO_APPROVAL' => new RDApproval($this->whenLoaded('ceoApproval')),
            // 'APPROVAL_LOGS' => ApprovalLogResource::collection($this->whenLoaded('approvalLogs')),
            'APPROVAL_LOGS' => $this->approvalLogs,
        ];
    }
}
