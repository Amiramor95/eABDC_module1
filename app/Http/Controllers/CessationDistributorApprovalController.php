<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\CessationDistributorApproval;
use App\Models\CessationDistributor;
use App\Models\CessationFimmApproval;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use Illuminate\Support\Str;

class CessationDistributorApprovalController extends Controller
{
    public function getCessationLogByID(Request $request)
    {
        try {

            $record = DB::table('distributor_management.CESSATION_DISTRIBUTOR_APPROVAL AS CDA')
            ->select('CDA.CESSATION_DISTRIBUTOR_APPROVAL_ID','CDA.CREATE_TIMESTAMP',
            'GROUP.DISTRIBUTOR_MANAGE_GROUP_NAME AS GROUP_NAME','USR.USER_NAME','TASK.TS_PARAM',
            'CDA.LATEST_UPDATE','CDA.APPR_REMARK AS APPR_REMARK','CDA.APPR_REMARK AS APPR_FULL')
            ->leftJoin('distributor_management.USER AS USR','USR.USER_ID', '=', 'CDA.CREATE_BY')
            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'CDA.TS_ID')
            ->leftJoin('admin_management.DISTRIBUTOR_MANAGE_GROUP AS GROUP','GROUP.DISTRIBUTOR_MANAGE_GROUP_ID', '=', 'CDA.APPR_GROUP_ID')
            ->where('CDA.CESSATION_ID', $request->CESSATION_ID)
            ->orderBy('CDA.CESSATION_DISTRIBUTOR_APPROVAL_ID', 'ASC')
            ->get();

            foreach($record as $item){
                $item->CREATE_TIMESTAMP =  $item->CREATE_TIMESTAMP ?? '-';
                $item->CREATE_TIMESTAMP = date('d-M-Y ', strtotime($item->CREATE_TIMESTAMP));
                if ($item->LATEST_UPDATE == null){
                    $item->LATEST_UPDATE = '-';
                }else{
                $item->LATEST_UPDATE =  $item->LATEST_UPDATE ?? '-';
                $item->LATEST_UPDATE = date('d-M-Y H:i:s', strtotime($item->LATEST_UPDATE));
                }

                if ($item->APPR_REMARK == null || $item->APPR_REMARK == "" || $item->APPR_REMARK == 'null'  ) {
                    $item->APPR_REMARK ="-";
                }else {
                    $item->APPR_REMARK = Str::limit($item->APPR_REMARK,50);
                }
               $item->USER_NAME =  $item->USER_NAME ?? '-';
             }

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => $record
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ],400);
        }
    }

    public function getCessationListByGroupId(Request $request)
    {
        try {
            $data = DB::table('distributor_management.CESSATION_DISTRIBUTOR_APPROVAL AS CDA')
            ->select('CDA.CESSATION_ID','CDA.CESSATION_DISTRIBUTOR_APPROVAL_ID','CDA.APPR_GROUP_ID','CDA.APPR_REMARK',
            'CD.DISTRIBUTOR_ID','CD.CESSATION_TYPE','CD.CESSATION_DATE','CD.LEGAL_DATE',
            'CD.DISTRIBUTOR_MERGER','CD.OTHER_REMARK','CD.RECIPIENT_NAME','CD.BANK_ID','CD.ACCOUNT_NO','CD.LATEST_UPDATE',
            'CDA.TS_ID','CD.LATEST_UPDATE_BY','CD.ISSUBMIT','CD.CREATE_TIMESTAMP','CD.SUBMISSION_DATE',
            'TASK.TS_PARAM','USR.USER_NAME','D.DIST_NAME AS DISTRIBUTOR_NAME','TYPE.SET_PARAM AS CESSATION_NAME',
            'BANK.SET_PARAM AS BANK_NAME','DM.DIST_NAME AS DISTRIBUTOR_MERGER_NAME','CD.FIMM_TS_ID','TS.TS_PARAM AS FIMM_STATUS')

            ->leftJoin('distributor_management.CESSATION_DISTRIBUTOR AS CD','CD.CESSATION_ID','=','CDA.CESSATION_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'CDA.TS_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TS','TS.TS_ID', '=', 'CD.FIMM_TS_ID')
            ->leftJoin('distributor_management.USER AS USR','USR.USER_ID', '=', 'CD.LATEST_UPDATE_BY')
            ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR AS DM','DM.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_MERGER')
            ->leftJoin('admin_management.SETTING_GENERAL AS TYPE','TYPE.SETTING_GENERAL_ID', '=', 'CD.CESSATION_TYPE')
            ->leftJoin('admin_management.SETTING_GENERAL AS BANK','BANK.SETTING_GENERAL_ID', '=', 'CD.BANK_ID')

            ->where('CD.DISTRIBUTOR_ID','=', $request->DISTRIBUTOR_ID)
            ->where('CDA.APPR_GROUP_ID','=', $request->APPR_GROUP_ID)

            ->orderBy('CDA.CESSATION_DISTRIBUTOR_APPROVAL_ID','DESC')
            ->limit(1)
            ->get();

            foreach($data as $item){
                if($item->CREATE_TIMESTAMP != null || $item->CREATE_TIMESTAMP != ""){
                    $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));
                }else{
                $item->CREATE_TIMESTAMP = '-';
                }

                if($item->SUBMISSION_DATE != null || $item->SUBMISSION_DATE != ""){
                    $item->SUBMISSION_DATE = date('d-M-Y', strtotime($item->SUBMISSION_DATE));
                }else{
                $item->SUBMISSION_DATE = '-';
                }

                if($item->CESSATION_NAME == "" ||$item->CESSATION_NAME == null ){
                    $item->CESSATION_NAME ='-';
                }

                if($item->CESSATION_DATE != null || $item->CESSATION_DATE != ""){
                    $item->CESSATION_DATE = date('d-M-Y', strtotime($item->CESSATION_DATE));
                }else{
                    $item->CESSATION_DATE ="-";
                }
                if($item->LEGAL_DATE != null || $item->LEGAL_DATE != ""){
                    $item->LEGAL_DATE = date('d-M-Y', strtotime($item->LEGAL_DATE));
                }else{
                    $item->LEGAL_DATE ="-";
                }

                if($item->DISTRIBUTOR_MERGER_NAME == null || $item->DISTRIBUTOR_MERGER_NAME == "" ){
                    $item->DISTRIBUTOR_MERGER_NAME = "-";
                }

                if($item->OTHER_REMARK == null || $item->OTHER_REMARK == "" || $item->OTHER_REMARK == "null" ){
                    $item->OTHER_REMARK = "-";
                }
                if($item->RECIPIENT_NAME == null || $item->RECIPIENT_NAME == "" ){
                    $item->RECIPIENT_NAME = "-";
                }
                if($item->ACCOUNT_NO == null || $item->ACCOUNT_NO == "" ){
                    $item->ACCOUNT_NO = "-";
                }
                if($item->FIMM_STATUS == null || $item->FIMM_STATUS == "" ){
                    $item->FIMM_STATUS = "-";
                }

            }


            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => $data
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ],400);
        }
    }

    public function updateManagerApproval(Request $request)
    {
        try {
            $appr= CessationDistributorApproval::find($request->CESSATION_DISTRIBUTOR_APPROVAL_ID);
            $appr->APPR_REMARK = $request->APPR_REMARK;
            $appr->CREATE_BY = $request->CREATE_BY;
            $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            $appr->TS_ID = $request->TS_ID;
            $appr->save();

            if($request->APPR_PUBLISH_STATUS == "0"){
                $detail= CessationDistributor::find($request->CESSATION_ID);
                $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                $query = $detail->save();
            }


            if($request->APPR_PUBLISH_STATUS == "1"){


                  if($request->TS_ID == "3" ){
                    $detail= CessationDistributor::find($request->CESSATION_ID);
                    $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                    $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                    $detail->TS_ID = 3;
                    $detail->FIMM_TS_ID = 15;
                    $detail->SUBMISSION_DATE = $request->SUBMISSION_DATE;
                    $query = $detail->save();

                    foreach(json_decode($request->APPR_LIST) as $item){

                        $dataApproval = new CessationFimmApproval;
                        $dataApproval->CESSATION_ID = $request->CESSATION_ID;
                        $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                        $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                        $dataApproval->TS_ID = 15;
                        $dataApproval->save();

                    $notification = new ManageNotification();
                    $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                    }

                  }
                    //if return to distributor admin
                    if($request->TS_ID == "7" ){
                        $detail= CessationDistributor::find($request->CESSATION_ID);
                        $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                        $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                        $detail->TS_ID = 7;
                        $query = $detail->save();

                        //dashboard noti to admin
                        foreach(json_decode($request->APPR_LIST) as $item){
                            $dataApproval = new CessationDistributorApproval;
                            $dataApproval->CESSATION_ID = $request->CESSATION_ID;
                            $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            $dataApproval->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                            $dataApproval->TS_ID = 15;
                            $dataApproval->save();

                        }


                        //dashboard noti to distributor

                        //send noti to admin
                        $itemInfo= DB::table('distributor_management.USER AS U')
                        ->select('U.USER_NAME','U.USER_EMAIL','U.USER_GROUP')
                        ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                        ->where('U.USER_ISADMIN','=',1)
                        ->first();

                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($itemInfo->USER_GROUP,3,$request->DISTRIBUTOR_ID,$request->DIST_NOTI_REMARK,$request->DIST_NOTI_LOCATION);




                    }

            }



            http_response_code(200);
            if($request->APPR_PUBLISH_STATUS == "0"){
                return response([ 'message' => 'Data successfully saved' ]);
            }else if($request->APPR_PUBLISH_STATUS == "1"){
                if($request->TS_ID == "7"){
                    return response([ 'message' => 'Data successfully returned' ]);
                }else if($request->TS_ID == "3"){
                    return response([ 'message' => 'Data successfully submitted' ]);
                }
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be save.',
                'errorCode' => 0
            ]);
        }
    }
}
