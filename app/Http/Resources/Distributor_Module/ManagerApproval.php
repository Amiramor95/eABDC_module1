<?php

namespace App\Http\Resources\Distributor_Module;

use Illuminate\Http\Resources\Json\JsonResource;

class ManagerApproval extends JsonResource
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
            'APPROVAL_GROUP_ID' => $this->APPROVAL_GROUP_ID,
            'APPROVAL_LEVEL_ID' => $this->APPROVAL_LEVEL_ID,
            'EXTENSION_REQUEST_ID' => $this->EXTENSION_REQUEST_ID,
            'APPROVAL_REMARK' => $this->APPROVAL_REMARK,
            'TS_ID' => $this->TS_ID,
            'CREATED_BY' => $this->CREATED_BY,
            'APPROVAL_PUBLISH_STATUS' => boolval($this->APPROVAL_PUBLISH_STATUS),
            'IS_FIMM' => boolval($this->IS_FIMM),
            'IS_SUBSEQUENT' => boolval($this->IS_SUBSEQUENT),
            'DOCUMENTS' => ExtensionRequestApprovalDocument::collection($this->whenLoaded('documents'))
        ];
    }
}
