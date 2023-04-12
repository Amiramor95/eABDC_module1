<?php

namespace App\Console\Commands;
use App\Models\DistributorType;
use Illuminate\Console\Command;
use DB;

class unsuspendAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:unsuspend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'unsuspend distributor & consultant based on end date';

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
        $unsuspend= DB::table('distributor_management.SUSPEND_REVOKE')
        ->whereDate('END_DATE', '<', date('Y-m-d'))
        ->get();

        foreach($suspend as $item) {
            $getDistName = DB::table('distributor_management.DISTRIBUTOR')
            ->where('DISTRIBUTOR_ID',$item->DISTRIBUTOR_ID)
            ->first();

            //1.suspend distributor with status active only
            //22-ACTIVE , 23-SUSPEND (task_status)
            $data = DistributorType::where('DIST_ID',$item->DISTRIBUTOR_ID)
            ->where('ISACTIVE','=','22')
            ->get();

            foreach($data as $itemData){
                    $itemData->ISACTIVE = 23;
                    $itemData->save();
            }

            //2. get id for ts_code SSO- suspend-OTHERS

            $code= DB::table('admin_management.SETTING_GENERAL')
            ->select('SETTING_GENERAL_ID')
            ->where('SET_CODE','=','SSO')
            ->where('SET_TYPE','=','CONSULTANTSTATUS')
            ->first();

            //suspend consultant distributor with status active only

            $consultant = DB::table('consultant_management.CONSULTANT_LICENSE AS CL')
            ->select('CONSULTANT_LICENSE_ID')
            ->leftJoin('admin_management.SETTING_GENERAL AS SET','SET.SETTING_GENERAL_ID','=','CL.CONSULTANT_STATUS')
            ->where('CL.DISTRIBUTOR_ID','=',$item->DISTRIBUTOR_ID)
            ->where('SET.SET_CODE','=','AC')
            ->get();

            foreach($consultant as $itemConsultant){

                $status = DB::table('consultant_management.CONSULTANT_LICENSE')
                ->where('CONSULTANT_LICENSE_ID',$itemConsultant->CONSULTANT_LICENSE_ID)
                ->update(['CONSULTANT_STATUS' => $code->SETTING_GENERAL_ID]);
        }

        }




    }
}
