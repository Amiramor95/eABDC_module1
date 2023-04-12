<?php

namespace App\Observers;

use App\Models\ExtensionRequestApproval;
use App\Models\ExtensionRequestApprovalLog;

class ExtensionRequestApprovalObserver
{
    /**
     * Handle the ExtensionRequestApproval "created" event.
     *
     * @param  \App\Models\ExtensionRequestApproval  $extensionRequestApproval
     * @return void
     */
    public function created(ExtensionRequestApproval $extensionRequestApproval)
    {
        //
    }

    /**
     * Handle the ExtensionRequestApproval "updated" event.
     *
     * @param  \App\Models\ExtensionRequestApproval  $extensionRequestApproval
     * @return void
     */
    public function updated(ExtensionRequestApproval $extensionRequestApproval)
    {

        $input = $extensionRequestApproval->only([
            'EXTENSION_REQUEST_APPROVAL_ID',
            'APPROVAL_GROUP_ID',
            'APPROVAL_REMARK',
        ]);

        $input['APPROVAL_ACTIVITY'] = $extensionRequestApproval->taskStatus->TS_PARAM;

        ExtensionRequestApprovalLog::create($input);

    }

    /**
     * Handle the ExtensionRequestApproval "deleted" event.
     *
     * @param  \App\Models\ExtensionRequestApproval  $extensionRequestApproval
     * @return void
     */
    public function deleted(ExtensionRequestApproval $extensionRequestApproval)
    {
        //
    }

    /**
     * Handle the ExtensionRequestApproval "restored" event.
     *
     * @param  \App\Models\ExtensionRequestApproval  $extensionRequestApproval
     * @return void
     */
    public function restored(ExtensionRequestApproval $extensionRequestApproval)
    {
        //
    }

    /**
     * Handle the ExtensionRequestApproval "force deleted" event.
     *
     * @param  \App\Models\ExtensionRequestApproval  $extensionRequestApproval
     * @return void
     */
    public function forceDeleted(ExtensionRequestApproval $extensionRequestApproval)
    {
        //
    }
}
