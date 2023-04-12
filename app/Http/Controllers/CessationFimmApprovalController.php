<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\CessationFimmApproval;
use App\Models\CessationDistributor;
use App\Models\CessationDistributorApproval;
use App\Models\DistributorType;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use Illuminate\Support\Str;

class CessationFimmApprovalController extends Controller
{
    public function getCessationListByFimmGroupId(Request $request)
    {
        try {
            $data = DB::table('distributor_management.CESSATION_FIMM_APPROVAL AS CDA')
            ->select('CDA.CESSATION_ID','CDA.CESSATION_FIMM_APPROVAL_ID','CDA.APPR_GROUP_ID','CDA.APPR_REMARK',
            'CD.DISTRIBUTOR_ID','CD.CESSATION_TYPE','CD.CESSATION_DATE','CD.LEGAL_DATE',
            'CD.DISTRIBUTOR_MERGER','CD.OTHER_REMARK','CD.RECIPIENT_NAME','CD.BANK_ID','CD.ACCOUNT_NO','CD.FIMM_LATEST_UPDATE AS LATEST_UPDATE',
            'CDA.TS_ID','CD.FIMM_LATEST_UPDATE_BY AS LATEST_UPDATE_BY ','CD.ISSUBMIT','CD.CREATE_TIMESTAMP','CD.SUBMISSION_DATE',
            'TASK.TS_PARAM','USR.USER_NAME','D.DIST_NAME AS DISTRIBUTOR_NAME','D.DIST_REGI_NUM1',
            'D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL',
            'TYPE.SET_PARAM AS CESSATION_NAME',
            'BANK.SET_PARAM AS BANK_NAME','DM.DIST_NAME AS DISTRIBUTOR_MERGER_NAME','CD.FIMM_TS_ID','TS.TS_PARAM AS FIMM_STATUS','TASK.TS_PARAM AS APPR_STATUS')

            ->leftJoin('distributor_management.CESSATION_DISTRIBUTOR AS CD','CD.CESSATION_ID','=','CDA.CESSATION_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'CDA.TS_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TS','TS.TS_ID', '=', 'CD.FIMM_TS_ID')
            ->leftJoin('admin_management.USER AS USR','USR.USER_ID', '=', 'CD.FIMM_LATEST_UPDATE_BY')
            ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR AS DM','DM.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_MERGER')
            ->leftJoin('admin_management.SETTING_GENERAL AS TYPE','TYPE.SETTING_GENERAL_ID', '=', 'CD.CESSATION_TYPE')
            ->leftJoin('admin_management.SETTING_GENERAL AS BANK','BANK.SETTING_GENERAL_ID', '=', 'CD.BANK_ID')

            ->where('CDA.APPR_GROUP_ID','=', $request->APPR_GROUP_ID)
            ->where('CDA.APPR_PUBLISH_STATUS','=', 0)
            ->orderBy('CDA.CESSATION_FIMM_APPROVAL_ID','DESC')

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
                if($item->DIST_REGI_NUM1 == null || $item->DIST_REGI_NUM1 == "" ){
                    $item->DIST_REGI_NUM1 = "-";
                }
                if($item->DIST_REGI_NUM2 == null || $item->DIST_REGI_NUM2 == "" ){
                    $item->DIST_REGI_NUM2 = "-";
                }
                if($item->DIST_PHONE_NUMBER == null || $item->DIST_PHONE_NUMBER == "" ){
                    $item->DIST_PHONE_NUMBER = "-";
                }
                if($item->DIST_EMAIL == null || $item->DIST_EMAIL == "" ){
                    $item->DIST_EMAIL = "-";
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

    public function getCessationOverviewList(Request $request)
    {
        try {
            $data = DB::table('distributor_management.CESSATION_FIMM_APPROVAL AS CDA')
            ->select('CDA.CESSATION_ID','CDA.CESSATION_FIMM_APPROVAL_ID','CDA.APPR_GROUP_ID','CDA.APPR_REMARK',
            'CD.DISTRIBUTOR_ID','CD.CESSATION_TYPE','CD.CESSATION_DATE','CD.LEGAL_DATE',
            'CD.DISTRIBUTOR_MERGER','CD.OTHER_REMARK','CD.RECIPIENT_NAME','CD.BANK_ID','CD.ACCOUNT_NO','CD.FIMM_LATEST_UPDATE AS LATEST_UPDATE',
            'CDA.TS_ID','CD.FIMM_LATEST_UPDATE_BY AS LATEST_UPDATE_BY ','CD.ISSUBMIT','CD.CREATE_TIMESTAMP','CD.SUBMISSION_DATE',
            'TASK.TS_PARAM','USR.USER_NAME','D.DIST_NAME AS DISTRIBUTOR_NAME','D.DIST_REGI_NUM1',
            'D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL',
            'TYPE.SET_PARAM AS CESSATION_NAME',
            'BANK.SET_PARAM AS BANK_NAME','DM.DIST_NAME AS DISTRIBUTOR_MERGER_NAME','CD.FIMM_TS_ID','TS.TS_PARAM AS FIMM_STATUS')

            ->leftJoin('distributor_management.CESSATION_DISTRIBUTOR AS CD','CD.CESSATION_ID','=','CDA.CESSATION_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'CDA.TS_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TS','TS.TS_ID', '=', 'CD.FIMM_TS_ID')
            ->leftJoin('admin_management.USER AS USR','USR.USER_ID', '=', 'CD.FIMM_LATEST_UPDATE_BY')
            ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR AS DM','DM.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_MERGER')
            ->leftJoin('admin_management.SETTING_GENERAL AS TYPE','TYPE.SETTING_GENERAL_ID', '=', 'CD.CESSATION_TYPE')
            ->leftJoin('admin_management.SETTING_GENERAL AS BANK','BANK.SETTING_GENERAL_ID', '=', 'CD.BANK_ID')

            ->groupBy('CDA.CESSATION_ID')
            ->orderBy('CDA.CESSATION_FIMM_APPROVAL_ID','DESC')

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
                if($item->DIST_REGI_NUM1 == null || $item->DIST_REGI_NUM1 == "" ){
                    $item->DIST_REGI_NUM1 = "-";
                }
                if($item->DIST_REGI_NUM2 == null || $item->DIST_REGI_NUM2 == "" ){
                    $item->DIST_REGI_NUM2 = "-";
                }
                if($item->DIST_PHONE_NUMBER == null || $item->DIST_PHONE_NUMBER == "" ){
                    $item->DIST_PHONE_NUMBER = "-";
                }
                if($item->DIST_EMAIL == null || $item->DIST_EMAIL == "" ){
                    $item->DIST_EMAIL = "-";
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

    public function getFimmCessationLogByID(Request $request)
    {
        try {

            $record = DB::table('distributor_management.CESSATION_FIMM_APPROVAL AS CDA')
            ->select('CDA.CESSATION_FIMM_APPROVAL_ID','CDA.CREATE_TIMESTAMP',
            'GROUP.GROUP_NAME','USR.USER_NAME','TASK.TS_PARAM',
            'CDA.LATEST_UPDATE','CDA.APPR_REMARK AS APPR_REMARK','CDA.APPR_REMARK AS APPR_FULL')
            ->leftJoin('admin_management.USER AS USR','USR.USER_ID', '=', 'CDA.CREATE_BY')
            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'CDA.TS_ID')
            ->leftJoin('admin_management.MANAGE_GROUP AS GROUP','GROUP.MANAGE_GROUP_ID', '=', 'CDA.APPR_GROUP_ID')
            ->where('CDA.CESSATION_ID', $request->CESSATION_ID)
            ->orderBy('CDA.CESSATION_FIMM_APPROVAL_ID', 'ASC')
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

    public function updateFimmApproval(Request $request)
    {
        try {
            $url = env('URL_SERVER') . '/api/module0/send_cease_email';

            $appr= CessationFimmApproval::find($request->CESSATION_FIMM_APPROVAL_ID);
            $appr->APPR_REMARK = $request->APPR_REMARK;
            $appr->CREATE_BY = $request->CREATE_BY;
            $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            $appr->TS_ID = $request->FIMM_TS_ID;
            $appr->save();

            if($request->APPR_PUBLISH_STATUS == "0"){
                $detail= CessationDistributor::find($request->CESSATION_ID);
                $detail->FIMM_LATEST_UPDATE_BY = $request->CREATE_BY;
                $detail->FIMM_TS_ID = $request->FIMM_TS_ID;
                $detail->save();
            }


            if($request->APPR_PUBLISH_STATUS == "1"){

                  //Return to distributor admin
                  if($request->FIMM_TS_ID == "7" ){
                    //1. update master data
                      $detail= CessationDistributor::find($request->CESSATION_ID);
                      $detail->FIMM_LATEST_UPDATE_BY = $request->CREATE_BY;
                      $detail->FIMM_TS_ID = $request->FIMM_TS_ID;
                      $detail->TS_ID = $request->TS_ID;
                      $detail->FIMM_REMARK = $request->APPR_REMARK;
                      $detail->save();

                      //2.insert data approval for dist admin
                      foreach(json_decode($request->DIST_APPR_LIST) as $item){
                          $dataApproval = new CessationDistributorApproval;
                          $dataApproval->CESSATION_ID = $request->CESSATION_ID;
                          $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                          $dataApproval->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                          $dataApproval->TS_ID = 15;
                          $dataApproval->APPR_REMARK = $request->APPR_REMARK;
                          $dataApproval->save();

                      }

                       //3.dashboard noti to distributor
                       $getAdm= DB::table('distributor_management.USER AS U')
                        ->select('U.USER_GROUP')
                        ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                        ->where('U.USER_ISADMIN','=',1)
                        ->first();

                       $notification = new ManageDistributorNotification();
                       $add = $notification->add($getAdm->USER_GROUP,3,$request->DISTRIBUTOR_ID,$request->DIST_NOTI_REMARK,$request->DIST_NOTI_LOCATION);


                     //Send email to admin
                        $infoAdm= DB::table('distributor_management.USER AS U')
                        ->select('U.USER_NAME','U.USER_EMAIL','U.USER_GROUP')
                        ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                        ->where('U.USER_ISADMIN','=',1)
                        ->get();

                    foreach($infoAdm as $itemInfo){

                    // $email = $itemInfo->USER_EMAIL;
                    $email= "nurul.mdshariff@gmail.com";
                    $name = $itemInfo->USER_NAME;
                    $distName = $request->DISTRIBUTOR_NAME;
                    $distRegNo = $request->REGISTRATION_NUMBER;
                    $distNewRegNo = $request->NEW_REGISTRATION_NUMBER;
                    $cessationName = $request->CESSATION_NAME;
                    $cessationDate = $request->CESSATION_DATE;
                    $title = $request->NOTI_EMAIL;



                    $response1 =  Curl::to($url)
                    ->withData(['email' => $email,'name' => $name,'distName' => $distName,'distRegNo' => $distRegNo,'distNewRegNo' => $distNewRegNo,
                    'cessationName' => $cessationName, 'cessationDate' => $cessationDate,'title' => $title])
                    ->returnResponseObject()
                    ->post();

                    $content1 = json_decode($response1->content);
                }

                        if ($response1->status != 200) {
                            http_response_code(400);
                            return response([
                                'message' => 'Failed to send email.',
                                'errorCode' => 4100
                            ], 400);
                        }else{
                            return response([
                                'message' => 'Email notification has been sent to Distributor Admin',
                            ]);

                        }

                  }

                  //submit for hod approval
                  if($request->TS_ID == "3" ){
                    $detail= CessationDistributor::find($request->CESSATION_ID);
                    $detail->FIMM_LATEST_UPDATE_BY = $request->CREATE_BY;
                    $detail->FIMM_TS_ID = $request->FIMM_TS_ID;
                    $detail->TS_ID = 15;
                    $query = $detail->save();

                    $detailAppr= CessationFimmApproval::find($request->CESSATION_FIMM_APPROVAL_ID);
                    $detailAppr->CREATE_BY = $request->CREATE_BY;
                    $detailAppr->APPR_REMARK = $request->APPR_REMARK;
                    $detailAppr->TS_ID = 3;
                    $query = $detailAppr->save();

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

                  //hod return to RD
                  if($request->TS_ID == "9" ){
                    $detail= CessationDistributor::find($request->CESSATION_ID);
                    $detail->FIMM_LATEST_UPDATE_BY = $request->CREATE_BY;
                    $detail->FIMM_TS_ID = $request->FIMM_TS_ID;
                    $detail->TS_ID = 15;
                    $query = $detail->save();

                    $detailAppr= CessationFimmApproval::find($request->CESSATION_FIMM_APPROVAL_ID);
                    $detailAppr->CREATE_BY = $request->CREATE_BY;
                    $detailAppr->APPR_REMARK = $request->APPR_REMARK;
                    $detailAppr->TS_ID = $request->TS_ID;
                    $query = $detailAppr->save();

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

          }


            http_response_code(200);
            if($request->APPR_PUBLISH_STATUS == "0"){
                return response([ 'message' => 'Data successfully saved' ]);
            }else if($request->APPR_PUBLISH_STATUS == "1"){
                if($request->FIMM_TS_ID == "7"){
                    return response([ 'message' => 'Data successfully returned to distributor' ]);
                }else if($request->TS_ID == "3"){
                    return response([ 'message' => 'Data successfully submitted for approval' ]);
                }else if($request->TS_ID == "9"){
                    return response([ 'message' => 'Data successfully returned' ]);
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

    public function updateFimmHodApproval(Request $request)
    {
        try {
            $url = env('URL_SERVER') . '/api/module0/send_cease_email';

            $appr= CessationFimmApproval::find($request->CESSATION_FIMM_APPROVAL_ID);
            $appr->APPR_REMARK = $request->APPR_REMARK;
            $appr->CREATE_BY = $request->CREATE_BY;
            $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            $appr->TS_ID = $request->FIMM_TS_ID;
            $appr->save();

            if($request->APPR_PUBLISH_STATUS == "0"){
                $detail= CessationDistributor::find($request->CESSATION_ID);
                $detail->FIMM_LATEST_UPDATE_BY = $request->CREATE_BY;
                $detail->FIMM_TS_ID = $request->FIMM_TS_ID;
                $detail->save();
            }

            if($request->APPR_PUBLISH_STATUS == "1"){
                  //approve by HOD

                    $detail= CessationDistributor::find($request->CESSATION_ID);
                    $detail->FIMM_LATEST_UPDATE_BY = $request->CREATE_BY;
                    $detail->FIMM_TS_ID = 3;
                    $detail->TS_ID = 3;
                    if($request->CEASE_ACTION == "FALSE"){
                        $detail->CEASE_NOTIFICATION = $request->CEASE_NOTIFICATION ;
                    }
                    $query = $detail->save();

                    $detailAppr= CessationFimmApproval::find($request->CESSATION_FIMM_APPROVAL_ID);
                    $detailAppr->CREATE_BY = $request->CREATE_BY;
                    $detailAppr->APPR_REMARK = $request->APPR_REMARK;
                    $detailAppr->TS_ID = 3;
                    $query = $detailAppr->save();

                    //1. send noti to staff rd
                    foreach(json_decode($request->APPR_LIST) as $item){
                        $notification = new ManageNotification();
                        $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                    }

                    //send noti to admin
                    $infoAdm= DB::table('distributor_management.USER AS U')
                    ->select('U.USER_NAME','U.USER_EMAIL','U.USER_GROUP')
                    ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                    ->where('U.USER_ISADMIN','=',1)
                    ->get();

                    foreach($infoAdm as $itemInfo){

                    //Send email to admin
                    // $email = $itemInfo->USER_EMAIL;
                    $email= "nurul.mdshariff@gmail.com";
                    $name = $itemInfo->USER_NAME;
                    $distName = $request->DISTRIBUTOR_NAME;
                    $distRegNo = $request->REGISTRATION_NUMBER;
                    $distNewRegNo = $request->NEW_REGISTRATION_NUMBER;
                    $cessationName = $request->CESSATION_NAME;
                    $cessationDate = $request->CESSATION_DATE;
                    $title = $request->NOTI_EMAIL;



                    $response1 =  Curl::to($url)
                    ->withData(['email' => $email,'name' => $name,'distName' => $distName,'distRegNo' => $distRegNo,'distNewRegNo' => $distNewRegNo,
                    'cessationName' => $cessationName, 'cessationDate' => $cessationDate,'title' => $title])
                    ->returnResponseObject()
                    ->post();

                    $content1 = json_decode($response1->content);
                }

                        if ($response1->status != 200) {
                            http_response_code(400);
                            return response([
                                'message' => 'Failed to send email.',
                                'errorCode' => 4100
                            ], 400);
                        }else{
                            return response([
                                'message' => 'Email notification has been sent to Distributor Admin',
                            ]);

                        }
                 }


            http_response_code(200);
            if($request->APPR_PUBLISH_STATUS == "0"){
                return response([ 'message' => 'Data successfully saved' ]);
            }else if($request->APPR_PUBLISH_STATUS == "1"){
                return response([ 'message' => 'Data successfully Approved' ]);
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be save.',
                'errorCode' => 0
            ]);
        }
    }

    public function actionCease(Request $request)
    {
        try {
                //1.TERMINATE distributor
                    //22-ACTIVE , 25-INACTIVE (task_status)
                $data = DistributorType::where('DIST_ID',$request->DISTRIBUTOR_ID)
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
                ->where('CL.DISTRIBUTOR_ID','=',$request->DISTRIBUTOR_ID)
                ->get();

                    foreach($consultant as $itemConsultant){

                        $status = DB::table('consultant_management.CONSULTANT_LICENSE')
                        ->where('CONSULTANT_LICENSE_ID',$itemConsultant->CONSULTANT_LICENSE_ID)
                        ->update(['CONSULTANT_STATUS' => $code->SETTING_GENERAL_ID]);
                    }

                //3. inactive kan user

                        $user = DB::table('distributor_management.USER')
                        ->where('USER_DIST_ID',$request->DISTRIBUTOR_ID)
                        ->update(['USER_STATUS' => 2]);


                 //4. send dahboard notification


                    //4.2 Finance refund
                    foreach(json_decode($request->APPR_LIST2) as $item2){
                        $notification = new ManageNotification();
                        $add = $notification->add($item2->APPR_GROUP_ID,$item2->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK2,$request->NOTI_LOCATION2);
                    }
                    //4.3 ID Funds
                    foreach(json_decode($request->APPR_LIST3) as $item3){
                        $notification = new ManageNotification();
                        $add = $notification->add($item3->APPR_GROUP_ID,$item3->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK3,$request->NOTI_LOCATION3);
                    }




            http_response_code(200);
            return response([
                'message' => 'Data successfully updated.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be update.',
                'errorCode' => 0
            ]);
        }
    }

}
