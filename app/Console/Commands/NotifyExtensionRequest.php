<?php

namespace App\Console\Commands;

use App\Models\ExtensionRequest;
use App\Models\SubsequentExtensionRequest;
use App\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotifyExtensionRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extension:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send dashboard notification for Distributor Extension Period';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $extensionRequestType = [new ExtensionRequest(), new SubsequentExtensionRequest()];
        $merge = collect([]);

        foreach($extensionRequestType as $extensionRequest) {

            $output = $extensionRequest->where(function($query){
                return $query->where('FIRST_NOTIFICATION', false)->orWhere('SECOND_NOTIFICATION', false)->orWhere('FINAL_NOTIFICATION', false);
            })
            ->whereNotNull('EXTENSION_APPROVAL_DATE')
            ->where(function($query) {
                $query->where('EXTENSION_APPROVAL_DATE', '<=', now()->subMonths(3))
                    ->orWhere('EXTENSION_APPROVAL_DATE', '<=', now()->subMonths(4))
                    ->orWhere('EXTENSION_END_DATE', '<=', now());
            })
            ->where($extensionRequest instanceof ExtensionRequest
                    ? 'EXTENSION_STATUS_ID'
                    : 'TS_ID'
                    , TaskStatus::firstWhere('TS_PARAM', 'APPROVED')->TS_ID)
            ->latest('EXTENSION_APPROVAL_DATE')
            ->get();

            foreach($output as $item) {$merge->push($item);}

        }

        $merge = $merge->groupBy('DISTRIBUTOR_ID');

        $requests = collect([]);

        foreach($merge as $key => $items) {

            $max = Carbon::createFromDate(2021, 6, 30, 'Asia/Kuala_Lumpur')->toDate();
            $i = 0;

            foreach($items as $item) {

                $approvalDate = Carbon::parse($item->EXTENSION_APPROVAL_DATE)->toDate();

                if($approvalDate > $max) {

                    $max = $approvalDate;
                    $requests[$i] = $item;
                }

            }

            $i += 1;

        }

        $requests = collect($requests);

        DB::beginTransaction();

        if($requests->count() > 0) {

            foreach($requests as $item) {

                $data['LOCATION'] = (isset($item->SUBSEQUENT_EXTENSION_REQUEST_ID) ? "subsequent-" : "")."extension-request";
                $module = "(".(isset($item->SUBSEQUENT_EXTENSION_REQUEST_ID) ? "SUB " : "" )."EXT TIME)";

                if(Carbon::parse($item->EXTENSION_END_DATE) <= now()) {

                    Log::info(Carbon::parse($item->EXTENSION_APPROVAL_DATE)->diffInMonths(now()));

                    if(intval(Carbon::parse($item->EXTENSION_APPROVAL_DATE)->diffInMonths(Carbon::today())) == 3) {

                        $data['UPDATE_OPTION'] = ['FIRST_NOTIFICATION' => true];

                    }elseif(intval(Carbon::parse($item->EXTENSION_APPROVAL_DATE)->diffInMonths(Carbon::today())) >= 4) {

                        Log::info($item);

                        // Check if last updated has reached 1 month
                        if(Carbon::parse($item->updated_at)->diffInMonths(now()) == 1) {

                            // Check if extention end date has 1 month remaining
                            if(Carbon::parse($item->EXTENSION_END_DATE)->diffInMonths(now()) == 1) {
                                $data['UPDATE_OPTION'] = ['SECOND_NOTIFICATION' => true];
                            }

                            // Notify to RD
                            DB::connection('module0')->table('NOTIFICATION')->insert([
                                'NOTIFICATION_GROUP_ID' => 5,
                                'PROCESS_FLOW_ID' => 58,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "{$module} Application for {$item->distributor->DIST_CODE} has reached ".intval(Carbon::parse($item->EXTENSION_APPROVAL_DATE)->diffInMonths(Carbon::today()))." months.",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => $data['LOCATION']
                            ]);

                        }

                        $data['REMARK'] = $module.' Your application has reached '. intval(Carbon::parse($item->EXTENSION_APPROVAL_DATE)->diffInMonths(Carbon::today())) .' months.';

                        // Notify Dist Admin
                        DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => 3,
                            'DISTRIBUTOR_ID' => $item->DISTRIBUTOR_ID,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => $data['REMARK'],
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => $data['LOCATION']
                        ]);

                        $item->update(['updated_at' => now()]);

                    }

                }else{

                    $data['UPDATE_OPTION'] = ['FINAL_NOTIFICATION' => true];

                     // Notify to RD
                     DB::connection('module0')->table('NOTIFICATION')->insert([
                        'NOTIFICATION_GROUP_ID' => 5,
                        'PROCESS_FLOW_ID' => 58,
                        'NOTIFICATION_STATUS' => intval(0),
                        'REMARK' => "{$module} Application for {$item->distributor->DIST_CODE} has ended.",
                        'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                        'LOCATION' => "distributor-records"
                    ]);

                }

                if(array_key_exists('UPDATE_OPTION', $data)) {
                    tap($item)->update($data['UPDATE_OPTION']);
                }

            }

        }

        DB::commit();

        return 0;

    }
}
