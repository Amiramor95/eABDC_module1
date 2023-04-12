<?php

namespace App\Console\Commands;
use App\Models\DistributorType;
use Illuminate\Console\Command;
use App\Helpers\ManageNotification;
use DB;

class cessationAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:cease';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate distributor & consultant based on cessation date';

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
        $cease= DB::table('distributor_management.CESSATION_DISTRIBUTOR')
        ->whereDate('CESSATION_DATE', '=', date('Y-m-d'))
        ->get();

        foreach($cease as $item) {
            $getDistName = DB::table('distributor_management.DISTRIBUTOR')
            ->where('DISTRIBUTOR_ID',$item->DISTRIBUTOR_ID)
            ->first();

            $notiRemark2 = "(REFUND) This Distributor has been terminated by FIMM";
            $notiRemark3 = "(FUND) This Distributor has been terminated by FIMM";

            //1.TERMINATE distributor
                //22-ACTIVE , 25-INACTIVE (task_status)
                $data = DistributorType::where('DIST_ID',$item->DISTRIBUTOR_ID)
                ->get();

                    foreach($data as $itemData){
                            $itemData->ISACTIVE = 25;
                            $itemData->save();
                    }

                //get id for ts_code TO- TERMINATE-OTHERS

                $code= DB::table('admin_management.SETTING_GENERAL')
                ->select('SETTING_GENERAL_ID')
                ->where('SET_CODE','=','TO')
                ->where('SET_TYPE','=','CONSULTANTSTATUS')
                ->first();

                //2. terminate consultant

                $consultant = DB::table('consultant_management.CONSULTANT_LICENSE AS CL')
                ->select('CONSULTANT_LICENSE_ID')
                ->leftJoin('admin_management.SETTING_GENERAL AS SET','SET.SETTING_GENERAL_ID','=','CL.CONSULTANT_STATUS')
                ->where('CL.DISTRIBUTOR_ID','=',$item->DISTRIBUTOR_ID)
                ->get();

                    foreach($consultant as $itemConsultant){

                        $status = DB::table('consultant_management.CONSULTANT_LICENSE')
                        ->where('CONSULTANT_LICENSE_ID',$itemConsultant->CONSULTANT_LICENSE_ID)
                        ->update(['CONSULTANT_STATUS' => $code->SETTING_GENERAL_ID]);
                    }

                //3. inactive kan user

                        $user = DB::table('distributor_management.USER')
                        ->where('USER_DIST_ID',$item->DISTRIBUTOR_ID)
                        ->update(['USER_STATUS' => 2]);


                 //4. send dahboard notification

                 $appr1 = DB::table('admin_management.APPROVAL_LEVEL')
                 ->where('APPR_PROCESSFLOW_ID',21)
                 ->where('APPR_INDEX',1)
                 ->get();

                    //4.2 Finance refund
                    foreach($appr1 as $item2){
                        $notification = new ManageNotification();
                        $add = $notification->add($item2->APPR_GROUP_ID,$item2->APPR_PROCESSFLOW_ID,$notiRemark2,"PrepaymentRefund-DistributorTermination-list");
                    }
                    //4.3 ID Funds send dahboard notification
                 $appr2 = DB::table('admin_management.APPROVAL_LEVEL')
                 ->where('APPR_PROCESSFLOW_ID',24)
                 ->where('APPR_INDEX',1)
                 ->get();
                    foreach($appr2 as $item3){
                        $notification = new ManageNotification();
                        $add = $notification->add($item3->APPR_GROUP_ID,$item3->APPR_PROCESSFLOW_ID,$notiRemark3,"fundDisLodgementSubmissionList");
                    }
        }




    }
}
