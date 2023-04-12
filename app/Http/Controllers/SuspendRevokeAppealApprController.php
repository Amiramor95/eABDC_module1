<?php

namespace App\Http\Controllers;

use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use GuzzleHttp\Exception\RequestException;
use App\Models\SuspendRevokeAppealAppr;
use App\Models\SuspendRevokeAppeal;
use App\Models\DistributorType;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Illuminate\Support\Str;

class SuspendRevokeAppealApprController extends Controller
{

public function getAppealAuditLogByID(Request $request)
{
    try {

        $record = DB::table('distributor_management.SUSPEND_REVOKE_APPEAL_APPR AS SRA')
        ->select('SRA.SUSPEND_REVOKE_APPEAL_APPR_ID','SRA.CREATE_TIMESTAMP','GROUP.GROUP_NAME','USR.USER_NAME','TASK.TS_PARAM',
        'SRA.LATEST_UPDATE','SRA.APPR_REMARK AS APPR_REMARK','SRA.APPR_REMARK AS APPR_FULL')
        ->leftJoin('admin_management.USER AS USR','USR.USER_ID', '=', 'SRA.CREATE_BY')
        ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'SRA.TS_ID')
        ->leftJoin('admin_management.MANAGE_GROUP AS GROUP','GROUP.MANAGE_GROUP_ID', '=', 'SRA.APPR_GROUP_ID')
        ->where('SRA.SUSPEND_REVOKE_APPEAL_ID', $request->SUSPEND_REVOKE_APPEAL_ID)
        ->orderBy('SRA.SUSPEND_REVOKE_APPEAL_APPR_ID', 'ASC')
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

public function AppealUpdateApproval(Request $request)
{
    try {
        $appr= SuspendRevokeAppealAppr::find($request->SUSPEND_REVOKE_APPEAL_APPR_ID);
        $appr->APPR_REMARK = $request->APPR_REMARK;
        $appr->CREATE_BY = $request->CREATE_BY;
        $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
        $appr->TS_ID = $request->TS_ID;
        $appr->save();


        if($request->APPR_PUBLISH_STATUS == "1"){


                if($request->TS_ID == "18" ){
                $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                if($request->FIMM_ISSUBMIT == 1){
                $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                $detail->FIMM_TS_ID = $request->FIMM_TS_ID;
                }
                $query = $detail->save();

                foreach(json_decode($request->APPR_LIST) as $item){

                    $dataApproval = new SuspendRevokeAppealAppr;
                    $dataApproval->SUSPEND_REVOKE_APPEAL_ID = $request->SUSPEND_REVOKE_APPEAL_ID;
                    $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                    $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                    $dataApproval->TS_ID = 15;
                    $dataApproval->save();

                $notification = new ManageNotification();
                $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                }

                }
                //if return
                if($request->TS_ID == "9" ){

                    $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                    $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                    $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                    $detail->FIMM_TS_ID = $request->TS_ID;
                    if($request->FIMM_ISSUBMIT == 1){
                        $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                    }
                    $query = $detail->save();

                    //dashboard noti to RD
                    foreach(json_decode($request->APPR_LIST) as $item){

                    $dataApproval = new SuspendRevokeAppealAppr;
                    $dataApproval->SUSPEND_REVOKE_APPEAL_ID = $request->SUSPEND_REVOKE_APPEAL_ID;
                    $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                    $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                    $dataApproval->TS_ID = 15;
                    $dataApproval->save();

                    $notification = new ManageNotification();
                    $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                    }

                    //dashboard noti to hod
                    if($request->NOTIDASH2 == 1){
                    foreach(json_decode($request->APPR_LIST2) as $item2){
                        $notification = new ManageNotification();
                        $add = $notification->add($item2->APPR_GROUP_ID,$item2->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK2,$request->NOTI_LOCATION2);
                        }
                    }

                }

                //if reject
                if($request->TS_ID == "5" ){

                    $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                    $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                    $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                    $detail->FIMM_TS_ID = $request->TS_ID;
                    $detail->FIMM_REMARK = $request->APPR_REMARK;
                    $detail->TS_ID = $request->TS_ID;
                    if($request->FIMM_ISSUBMIT == 1){
                        $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                    }
                    $query = $detail->save();


                    $appr= SuspendRevokeAppealAppr::find($request->SUSPEND_REVOKE_APPEAL_APPR_ID);
                    $appr->APPR_REMARK = $request->APPR_REMARK;
                    $appr->CREATE_BY = $request->CREATE_BY;
                    $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
                    $appr->TS_ID = $request->TS_ID;
                    $appr->save();


                    //dashboard noti to RD
                    foreach(json_decode($request->APPR_LIST) as $item){
                    $notification = new ManageNotification();
                    $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                    }

                    //dashboard noti to distributor

                        //send noti to admin
                        $infoAdm= DB::table('distributor_management.USER AS U')
                        ->select('U.USER_NAME','U.USER_EMAIL','U.USER_GROUP')
                        ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                        ->where('U.USER_ISADMIN','=',1)
                        ->get();

                        foreach($infoAdm as $itemInfo){
                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($itemInfo->USER_GROUP,4,$request->DISTRIBUTOR_ID,$request->DIST_NOTI_REMARK,$request->DIST_NOTI_LOCATION);


                        }

                }




        }



        http_response_code(200);
        return response([
            'message' => 'Data successfully saved.'
        ]);
    } catch (RequestException $r) {
        http_response_code(400);
        return response([
            'message' => 'Data failed to be save.',
            'errorCode' => 0
        ]);
    }
}

public function AppealUpdateReview(Request $request)
{
    try {

        $getID= DB::table('SUSPEND_REVOKE_APPEAL_APPR')
        ->select('SUSPEND_REVOKE_APPEAL_APPR_ID')
        ->where('SUSPEND_REVOKE_APPEAL_ID','=',$request->SUSPEND_REVOKE_APPEAL_ID)
        ->where('APPR_GROUP_ID','=',$request->APPR_GROUP_ID)
        ->orderby('SUSPEND_REVOKE_APPEAL_APPR_ID','DESC')
        ->first();

            //count record
            $getCount= DB::table('SUSPEND_REVOKE_APPEAL_APPR')
            ->select( DB::raw('COUNT(SUSPEND_REVOKE_APPEAL_APPR_ID) AS TOTAL'))
            ->where('SUSPEND_REVOKE_APPEAL_ID','=',$request->SUSPEND_REVOKE_APPEAL_ID)
            ->where('APPR_GROUP_ID','=',$request->APPR_GROUP_ID)
            ->orderby('SUSPEND_REVOKE_APPEAL_APPR_ID','DESC')
            ->first();


        if($request->APPR_PUBLISH_STATUS == "0"){
            $appr= SuspendRevokeAppealAppr::find($getID->SUSPEND_REVOKE_APPEAL_APPR_ID);
            $appr->APPR_REMARK = $request->APPR_REMARK;
            $appr->CREATE_BY = $request->CREATE_BY;
            $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            $appr->TS_ID = $request->TS_ID;
            $appr->save();

            $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
            $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
            $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
            $detail->FIMM_TS_ID = $request->TS_ID;
            $query = $detail->save();
        }


        if($request->APPR_PUBLISH_STATUS == "1"){

            //REVIEWED
                if($request->TS_ID == "17" ){

                $appr= SuspendRevokeAppealAppr::find($getID->SUSPEND_REVOKE_APPEAL_APPR_ID);
                $appr->APPR_REMARK = $request->APPR_REMARK;
                $appr->CREATE_BY = $request->CREATE_BY;
                $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
                if($getCount->TOTAL > 1){
                    $appr->TS_ID = 37;
                }else {
                    $appr->TS_ID = $request->TS_ID;
                }
                $appr->save();

                $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                if($getCount->TOTAL > 1){
                    $detail->FIMM_TS_ID = 37;
                }else {
                    $detail->FIMM_TS_ID = $request->FIMM_TS_ID;
                }

                $detail->FIMM_REMARK = "";
                if($request->FIMM_ISSUBMIT == 1){
                    $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                }
                $query = $detail->save();

                foreach(json_decode($request->APPR_LIST) as $item){

                $dataApproval = new SuspendRevokeAppealAppr;
                $dataApproval->SUSPEND_REVOKE_APPEAL_ID = $request->SUSPEND_REVOKE_APPEAL_ID;
                $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                $dataApproval->TS_ID = 15;
                $dataApproval->save();

                $notification = new ManageNotification();
                $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                }

                }
                //if return to distributor
                if($request->TS_ID == "7" || $request->TS_ID == "5" ){

                    $appr= SuspendRevokeAppealAppr::find($getID->SUSPEND_REVOKE_APPEAL_APPR_ID);
                    $appr->APPR_REMARK = $request->APPR_REMARK;
                    $appr->CREATE_BY = $request->CREATE_BY;
                    $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
                    $appr->TS_ID = $request->TS_ID;
                    $appr->save();

                    $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                    $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                    $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                    $detail->FIMM_TS_ID = $request->TS_ID;
                    $detail->FIMM_REMARK = $request->APPR_REMARK;
                    $detail->TS_ID = $request->TS_ID;
                    if($request->FIMM_ISSUBMIT == 1){
                        $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                    }
                    $query = $detail->save();

                    $detailAppr= SuspendRevokeAppealAppr::find($getID->SUSPEND_REVOKE_APPEAL_APPR_ID);
                    $detailAppr->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                    $detailAppr->TS_ID =  $request->TS_ID;
                    $detailAppr->APPR_PUBLISH_STATUS =  $request-> APPR_PUBLISH_STATUS;
                    $queryAppr = $detailAppr->save();

                    //send noti to admin
                    $infoAdm= DB::table('distributor_management.USER AS U')
                    ->select('U.USER_NAME','U.USER_EMAIL','U.USER_GROUP')
                    ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                    ->where('U.USER_ISADMIN','=',1)
                    ->get();

                    foreach($infoAdm as $itemInfo){
                    $notification = new ManageDistributorNotification();
                    $add = $notification->add($itemInfo->USER_GROUP,4,$request->DISTRIBUTOR_ID,$request->DIST_NOTI_REMARK,$request->DIST_NOTI_LOCATION);


                }


                }

        }



        http_response_code(200);
        return response([
            'message' => 'Data successfully saved.'
        ]);
    } catch (RequestException $r) {
        http_response_code(400);
        return response([
            'message' => 'Data failed to be save.',
            'errorCode' => 0
        ]);
    }
}

public function getAppealComment(Request $request){
    try {

        $getData= DB::table('SUSPEND_REVOKE_APPEAL_APPR')
        ->select('APPR_REMARK')
        ->where('SUSPEND_REVOKE_APPEAL_ID','=',$request->SUSPEND_REVOKE_APPEAL_ID)
        ->where('APPR_GROUP_ID','=',$request->APPR_GROUP_ID)
        ->orderby('SUSPEND_REVOKE_APPEAL_APPR_ID','DESC')
        ->first();

        http_response_code(200);
        return response([
            'message' => 'Data successfully retrieved.',
            'data' => $getData
        ]);
    } catch (RequestException $r) {

        http_response_code(400);
        return response([
            'message' => 'Failed to retrieve data.',
            'errorCode' => 4103
        ],400);
    }
}

public function getAppealApprRecordByGroupID(Request $request)
{
    try {
        $data = DB::table('distributor_management.SUSPEND_REVOKE_APPEAL_APPR AS SRAA')
        ->select('SRAA.SUSPEND_REVOKE_APPEAL_APPR_ID','SRAA.TS_ID AS APPR_TS_ID','T.TS_PARAM AS APPR_STATUS',
        'SRA.SUSPEND_REVOKE_APPEAL_ID','SRA.FIMM_TS_ID','SRA.CREATE_BY','SRA.ISSUBMIT','SRA.JUSTIFICATION','SRA.SUSPEND_REVOKE_APPEAL_ID',
        'SRA.CREATE_TIMESTAMP AS DATE_APPEALED','TASK.TS_PARAM AS APPROVAL_STATUS','SRA.LATEST_UPDATE_BY','SRA.LATEST_UPDATE','USR.USER_NAME',
        'SR.SUSPEND_REVOKE_ID','SR.DISTRIBUTOR_ID','SR.SUBMISSION_TYPE','SR.DATE_START','SR.DATE_END',
        'SR.EFFECTIVE_DATE','SR.REASON','SR.CREATE_TIMESTAMP','SR.DIST_ACTION',
        'D.DIST_NAME','D.DIST_REGI_NUM1','D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL','TS.TS_PARAM AS DIST_STATUS',
        'DA.DIST_ID','DA.DIST_COUNTRY','DA.DIST_STATE','DA.DIST_CITY','DA.DIST_POSTAL','DA.DIST_STATE2','DA.DIST_CITY2','DA.DIST_POSTAL2',
        'DA.DIST_ADDR_1','DA.DIST_ADDR_2','DA.DIST_ADDR_3',
        'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME','COUNTRYNAME.SET_CODE AS SET_CODE',
        'STATENAME.SET_PARAM AS STATE_NAME','CITYNAME.SET_CITY_NAME','POSTAL.POSTCODE_NO'

        )

        ->leftJoin('distributor_management.SUSPEND_REVOKE_APPEAL AS SRA','SRA.SUSPEND_REVOKE_APPEAL_ID','=','SRAA.SUSPEND_REVOKE_APPEAL_ID')
        ->leftJoin('admin_management.TASK_STATUS AS T','T.TS_ID', '=', 'SRAA.TS_ID')

        ->leftJoin('distributor_management.SUSPEND_REVOKE AS SR','SR.SUSPEND_REVOKE_ID','=','SRA.SUSPEND_REVOKE_ID')
        ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'SRA.FIMM_TS_ID')
        ->leftJoin('admin_management.USER AS USR','USR.USER_ID', '=', 'SRA.LATEST_UPDATE_BY')

        ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID','=','SR.DISTRIBUTOR_ID')
        ->leftJoin('distributor_management.DISTRIBUTOR_ADDRESS AS DA','DA.DIST_ID','=','D.DISTRIBUTOR_ID')
        ->leftJoin('distributor_management.DISTRIBUTOR_TYPE AS DT','DT.DIST_ID','=','D.DISTRIBUTOR_ID')
        ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID','=','DT.ISACTIVE')

        ->leftJoin('admin_management.SETTING_GENERAL AS COUNTRYNAME','COUNTRYNAME.SETTING_GENERAL_ID','=','DA.DIST_COUNTRY')
        ->leftJoin('admin_management.SETTING_GENERAL AS STATENAME','STATENAME.SETTING_GENERAL_ID','=','DA.DIST_STATE')
        ->leftJoin('admin_management.SETTING_CITY AS CITYNAME','CITYNAME.SETTING_CITY_ID','=','DA.DIST_CITY')
        ->leftJoin('admin_management.SETTING_POSTAL AS POSTAL','POSTAL.SETTING_POSTCODE_ID','=','DA.DIST_POSTAL')


        ->where('SRAA.APPR_GROUP_ID', $request->APPR_GROUP_ID)
        ->where('SR.SUBMISSION_TYPE', $request->SUBMISSION_TYPE)
        ->where('SRAA.APPR_PUBLISH_STATUS', "0")

        ->groupBy('SRA.SUSPEND_REVOKE_APPEAL_ID')
        ->orderBy('SRA.CREATE_TIMESTAMP','DESC')
        ->get();


        foreach($data as $item){
            if($item->DATE_APPEALED != null || $item->DATE_APPEALED != ""){
                $item->DATE_APPEALED = date('d-M-Y', strtotime($item->DATE_APPEALED));
            }else{
            $item->DATE_APPEALED = '-';
            }
            if($item->SUBMISSION_TYPE == 1){
                $item->SUBMISSION_TYPE = 'SUSPENSION';
            }elseif($item->SUBMISSION_TYPE == 2){
            $item->SUBMISSION_TYPE = 'REVOCATION';
            }else {
                $item->SUBMISSION_TYPE ='-';
            }

            if ($item->LATEST_UPDATE == null){
                $item->LATEST_UPDATE = '-';
            }else{
            $item->LATEST_UPDATE =  $item->LATEST_UPDATE ?? '-';
            $item->LATEST_UPDATE = date('d-M-Y H:i:s', strtotime($item->LATEST_UPDATE));
            }



            if ($item->USER_NAME == null || $item->USER_NAME =="" ){
                $item->USER_NAME = '-';
            }

            if($item->DATE_START != null || $item->DATE_START != ""){
                $item->DATE_START = date('d-M-Y', strtotime($item->DATE_START));
            }else{
                $item->DATE_START = "-";
            }
            if($item->DATE_END != null || $item->DATE_END != ""){
                $item->DATE_END = date('d-M-Y', strtotime($item->DATE_END));
            }else{
                $item->DATE_END ="-";
            }
            if($item->EFFECTIVE_DATE != null || $item->EFFECTIVE_DATE  != ""){
                $item->EFFECTIVE_DATE  = date('d-M-Y', strtotime($item->EFFECTIVE_DATE ));
            }else{
                $item->EFFECTIVE_DATE="-";
            }

        }
        http_response_code(200);

        return response([
            'message' => 'Data successfully retrieved.',
            'data' => $data,
        ]);
    } catch (RequestException $r) {

        http_response_code(400);
        return response([
            'message' => 'Failed to retrieve data.',
            'errorCode' => 4103,
        ], 400);
    }
}

public function AppealUpdateApprovalCeo(Request $request)
{
    try {
        $url = env('URL_SERVER') . '/api/module0/send_suspendRevokeCeo_Email';

        $appr= SuspendRevokeAppealAppr::find($request->SUSPEND_REVOKE_APPEAL_APPR_ID);
        $appr->APPR_REMARK = $request->APPR_REMARK;
        $appr->CREATE_BY = $request->CREATE_BY;
        $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
        $appr->TS_ID = $request->TS_ID;
        $appr->save();


        if($request->APPR_PUBLISH_STATUS == "1"){

            //gm recommended to ceo
            if($request->TS_ID == "18" ){
                $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                if($request->FIMM_ISSUBMIT == 1){
                $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                }
                $query = $detail->save();

                foreach(json_decode($request->APPR_LIST) as $item){

                    $dataApproval = new SuspendRevokeAppealAppr;
                    $dataApproval->SUSPEND_REVOKE_APPEAL_ID = $request->SUSPEND_REVOKE_APPEAL_ID;
                    $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                    $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                    $dataApproval->TS_ID = 15;
                    $dataApproval->save();

                $notification = new ManageNotification();
                $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);

                    $info= DB::table('admin_management.USER AS U')
                    ->select('U.USER_NAME','U.USER_EMAIL')
                    ->where('U.USER_GROUP','=',$item->APPR_GROUP_ID)
                    ->first();

                    //$email = $info->USER_EMAIL;
                    //$email = 'khairinaizzah@gmail.com';
                    $email = 'nurul.mdshariff@gmail.com';
                    $name = $info->USER_NAME;
                    $distName = $request->DISTRIBUTOR_NAME;
                    $distRegNo = $request->REGISTRATION_NUMBER;
                    $distNewRegNo = $request->NEW_REGISTRATION_NUMBER;
                    $submissionType = $request->SUBMISSION_TYPE;
                    if($request->DATE_START != null || $request->DATE_START !=""){
                    $dateStart = $request->DATE_START;
                    }else{
                        $dateStart = "";
                    }
                    if($request->DATE_END != null || $request->DATE_END !=""){
                    $dateEnd = $request->DATE_END;
                    }else{
                        $dateEnd = "-";
                    }
                    if($request->EFFECTIVE_DATE != null || $request->EFFECTIVE_DATE !=""){
                        $effectiveDate = $request->EFFECTIVE_DATE;
                    }else{
                        $effectiveDate = " ";
                    }
                    if($request->REASON != null || $request->REASON !=""){
                        $reason = $request->REASON;
                    }else{
                        $reason = "-";
                    }

                    $title = $request->NOTI_EMAIL;

                        $response = Curl::to($url)
                        ->withData(['email' => $email,'name' => $name,'distName' => $distName,'distRegNo' => $distRegNo,'distNewRegNo' => $distNewRegNo,
                        'submissionType' => $submissionType, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'effectiveDate' => $effectiveDate,
                        'reason' => $reason, 'title' => $title])
                        ->returnResponseObject()
                        ->post();

                    $content = json_decode($response->content);

                    if ($response->status != 200) {
                        http_response_code(400);
                        return response([
                            'message' => 'Failed to send email.',
                            'errorCode' => 4100
                        ], 400);
                    }else{
                        return response([
                            'message' => 'Email notification has been sent to CEO',
                        ]);

                    }

                }

            }

            //ceo approved
            if($request->TS_ID == "3" ){
                $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                $detail->TS_ID = $request->TS_ID;
                $detail->FIMM_TS_ID = $request->TS_ID;
                if($request->FIMM_ISSUBMIT == 1){
                $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                }
                $query = $detail->save();


                foreach(json_decode($request->APPR_LIST) as $item){

                    //send noti to staff RD
                    $notification = new ManageNotification();
                    $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                }


                    $distName = $request->DISTRIBUTOR_NAME;
                    $distRegNo = $request->REGISTRATION_NUMBER;
                    $distNewRegNo = $request->NEW_REGISTRATION_NUMBER;
                    $submissionType = $request->SUBMISSION_TYPE;
                    if($request->DATE_START != null || $request->DATE_START !=""){
                    $dateStart = $request->DATE_START;
                    }else{
                        $dateStart = "";
                    }
                    if($request->DATE_END != null || $request->DATE_END !=""){
                    $dateEnd = $request->DATE_END;
                    }else{
                        $dateEnd = "-";
                    }
                    if($request->EFFECTIVE_DATE != null || $request->EFFECTIVE_DATE !=""){
                        $effectiveDate = $request->EFFECTIVE_DATE;
                    }else{
                        $effectiveDate = " ";
                    }
                    if($request->REASON != null || $request->REASON !=""){
                    $reason = $request->REASON;
                    }else{
                        $reason = "-";
                    }
                    $title = $request->NOTI_EMAIL;



                    //send noti to admin
                    $infoAdm= DB::table('distributor_management.USER AS U')
                    ->select('U.USER_NAME','U.USER_EMAIL','U.USER_GROUP')
                    ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                    ->where('U.USER_ISADMIN','=',1)
                    ->get();

                    foreach($infoAdm as $itemInfo){
                    $notification = new ManageDistributorNotification();
                    $add = $notification->add($itemInfo->USER_GROUP,4,$request->DISTRIBUTOR_ID,$request->DIST_NOTI_REMARK,$request->DIST_NOTI_LOCATION);


                    //Send email to admin
                    $email1 = $itemInfo->USER_EMAIL;
                    $name1 = $itemInfo->USER_NAME;

                        $response1 = Curl::to($url)
                        ->withData(['email' => $email1,'name' => $name1,'distName' => $distName,'distRegNo' => $distRegNo,'distNewRegNo' => $distNewRegNo,
                        'submissionType' => $submissionType, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'effectiveDate' => $effectiveDate,
                        'reason' => $reason, 'title' => $title])
                        ->returnResponseObject()
                        ->post();

                    $content1 = json_decode($response1->content);
                }

                    //send email to AR
                    $infoAr= DB::table('distributor_management.DISTRIBUTOR_REPRESENTATIVE AS R')
                    ->select('R.REPR_NAME','R.REPR_EMAIL')
                    ->where('R.DIST_ID','=',$request->DISTRIBUTOR_ID)
                    ->where('R.REPR_TYPE','=','AR')
                    ->first();

                    if($infoAr){
                    //Send email to ar
                    $email2 = $infoAr->REPR_EMAIL;
                    $name2 = $infoAr->REPR_NAME;

                        $response = Curl::to($url)
                        ->withData(['email' => $email2,'name' => $name2,'distName' => $distName,'distRegNo' => $distRegNo,'distNewRegNo' => $distNewRegNo,
                        'submissionType' => $submissionType, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'effectiveDate' => $effectiveDate,
                        'reason' => $reason, 'title' => $title])
                        ->returnResponseObject()
                        ->post();

                    $content = json_decode($response->content);

                    if ($response->status != 200) {
                        http_response_code(400);
                        return response([
                            'message' => 'Failed to send email.',
                            'errorCode' => 4100
                        ], 400);
                    }else{
                        return response([
                            'message' => 'Email notification has been sent to Distributor Admin and AR',
                        ]);

                    }
                }else{

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

            }

        }


        http_response_code(200);
        return response([
            'message' => 'Data successfully saved.'
        ]);
    } catch (RequestException $r) {
        http_response_code(400);
        return response([
            'message' => 'Data failed to be save.',
            'errorCode' => 0
        ]);
    }
}

public function actionUnsuspend(Request $request)
{
    try {

         //1: accept 2:appeal
        $data = DB::table('distributor_management.SUSPEND_REVOKE')
        ->where('SUSPEND_REVOKE_ID',$request->SUSPEND_REVOKE_ID)
        ->update(['APPEAL_END' => ""]);

        //1.unsuspend distributor
        //22-ACTIVE , 23-SUSPEND (task_status)
        $data = DistributorType::where('DIST_ID',$request->DISTRIBUTOR_ID)
        ->where('ISACTIVE','=','23')
        ->get();

        foreach($data as $itemData){
                $itemData->ISACTIVE = 22;
                $itemData->save();
        }

        //2. get id for ts_code AC- Active

        $code= DB::table('admin_management.SETTING_GENERAL')
        ->select('SETTING_GENERAL_ID')
        ->where('SET_CODE','=','AC')
        ->where('SET_TYPE','=','CONSULTANTSTATUS')
        ->first();

        //unsuspend consultant distributor with status suspend only

        $consultant = DB::table('consultant_management.CONSULTANT_LICENSE AS CL')
        ->select('CONSULTANT_LICENSE_ID')
        ->leftJoin('admin_management.SETTING_GENERAL AS SET','SET.SETTING_GENERAL_ID','=','CL.CONSULTANT_STATUS')
        ->where('CL.DISTRIBUTOR_ID','=',$request->DISTRIBUTOR_ID)
        ->where('SET.SET_CODE','=','SSO')
        ->get();

        foreach($consultant as $itemConsultant){

            $status = DB::table('consultant_management.CONSULTANT_LICENSE')
            ->where('CONSULTANT_LICENSE_ID',$itemConsultant->CONSULTANT_LICENSE_ID)
            ->update(['CONSULTANT_STATUS' => $code->SETTING_GENERAL_ID]);
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

public function actionRevoke(Request $request)
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

                //4.1 RD
                if($request->RD_NOTI == 1){
                    foreach(json_decode($request->APPR_LIST) as $item){
                        $notification = new ManageNotification();
                        $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);
                    }
                }
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

public function AppealUpdateRejectRevoke(Request $request)
{
    try {

        $getID= DB::table('SUSPEND_REVOKE_APPEAL_APPR')
        ->select('SUSPEND_REVOKE_APPEAL_APPR_ID')
        ->where('SUSPEND_REVOKE_APPEAL_ID','=',$request->SUSPEND_REVOKE_APPEAL_ID)
        ->where('APPR_GROUP_ID','=',$request->APPR_GROUP_ID)
        ->orderby('SUSPEND_REVOKE_APPEAL_APPR_ID','DESC')
        ->first();

            //count record
            $getCount= DB::table('SUSPEND_REVOKE_APPEAL_APPR')
            ->select( DB::raw('COUNT(SUSPEND_REVOKE_APPEAL_APPR_ID) AS TOTAL'))
            ->where('SUSPEND_REVOKE_APPEAL_ID','=',$request->SUSPEND_REVOKE_APPEAL_ID)
            ->where('APPR_GROUP_ID','=',$request->APPR_GROUP_ID)
            ->orderby('SUSPEND_REVOKE_APPEAL_APPR_ID','DESC')
            ->first();


        if($request->APPR_PUBLISH_STATUS == "1"){


                    $appr= SuspendRevokeAppealAppr::find($getID->SUSPEND_REVOKE_APPEAL_APPR_ID);
                    $appr->APPR_REMARK = $request->APPR_REMARK;
                    $appr->CREATE_BY = $request->CREATE_BY;
                    $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
                    $appr->TS_ID = $request->TS_ID;
                    $appr->save();

                    $detail= SuspendRevokeAppeal::find($request->SUSPEND_REVOKE_APPEAL_ID);
                    $detail->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                    $detail->LATEST_UPDATE_BY = $appr->CREATE_BY;
                    $detail->FIMM_TS_ID = $request->TS_ID;
                    $detail->FIMM_REMARK = $request->APPR_REMARK;
                    $detail->TS_ID = $request->TS_ID;
                    if($request->FIMM_ISSUBMIT == 1){
                        $detail->FIMM_ISSUBMIT = $request->FIMM_ISSUBMIT;
                    }
                    $query = $detail->save();

                    $detailAppr= SuspendRevokeAppealAppr::find($getID->SUSPEND_REVOKE_APPEAL_APPR_ID);
                    $detailAppr->LATEST_UPDATE = $appr->CREATE_TIMESTAMP;
                    $detailAppr->TS_ID =  $request->TS_ID;
                    $detailAppr->APPR_PUBLISH_STATUS =  $request-> APPR_PUBLISH_STATUS;
                    $queryAppr = $detailAppr->save();

                    $distName = $request->DISTRIBUTOR_NAME;
                    $distRegNo = $request->REGISTRATION_NUMBER;
                    $distNewRegNo = $request->NEW_REGISTRATION_NUMBER;
                    $submissionType = $request->SUBMISSION_TYPE;
                    if($request->DATE_START != null || $request->DATE_START !=""){
                    $dateStart = $request->DATE_START;
                    }else{
                        $dateStart = "";
                    }
                    if($request->DATE_END != null || $request->DATE_END !=""){
                    $dateEnd = $request->DATE_END;
                    }else{
                        $dateEnd = "-";
                    }
                    if($request->EFFECTIVE_DATE != null || $request->EFFECTIVE_DATE !=""){
                        $effectiveDate = $request->EFFECTIVE_DATE;
                    }else{
                        $effectiveDate = " ";
                    }
                    if($request->REASON != null || $request->REASON !=""){
                        $reason = $request->REASON;
                    }else{
                        $reason = "-";
                    }
                    $title = $request->NOTI_EMAIL;

                    //send noti to admin
                    $infoAdm= DB::table('distributor_management.USER AS U')
                    ->select('U.USER_NAME','U.USER_EMAIL','U.USER_GROUP')
                    ->where('U.USER_DIST_ID','=',$request->DISTRIBUTOR_ID)
                    ->where('U.USER_ISADMIN','=',1)
                    ->get();

                    foreach($infoAdm as $itemInfo){
                    $notification = new ManageDistributorNotification();
                    $add = $notification->add($itemInfo->USER_GROUP,4,$request->DISTRIBUTOR_ID,$request->DIST_NOTI_REMARK,$request->DIST_NOTI_LOCATION);


                    //Send email to admin
                    $email1 = $itemInfo->USER_EMAIL;
                    $name1 = $itemInfo->USER_NAME;

                        $response1 = Curl::to($url)
                        ->withData(['email' => $email1,'name' => $name1,'distName' => $distName,'distRegNo' => $distRegNo,'distNewRegNo' => $distNewRegNo,
                        'submissionType' => $submissionType, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'effectiveDate' => $effectiveDate,
                        'reason' => $reason, 'title' => $title])
                        ->returnResponseObject()
                        ->post();

                    $content1 = json_decode($response1->content);
                }

                    //send email to AR
                    $infoAr= DB::table('distributor_management.DISTRIBUTOR_REPRESENTATIVE AS R')
                    ->select('R.REPR_NAME','R.REPR_EMAIL')
                    ->where('R.DIST_ID','=',$request->DISTRIBUTOR_ID)
                    ->where('R.REPR_TYPE','=','AR')
                    ->first();

                    if($infoAr){
                    //Send email to ar
                    $email2 = $infoAr->REPR_EMAIL;
                    $name2 = $infoAr->REPR_NAME;

                        $response = Curl::to($url)
                        ->withData(['email' => $email2,'name' => $name2,'distName' => $distName,'distRegNo' => $distRegNo,'distNewRegNo' => $distNewRegNo,
                        'submissionType' => $submissionType, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'effectiveDate' => $effectiveDate,
                        'reason' => $reason, 'title' => $title])
                        ->returnResponseObject()
                        ->post();

                    $content = json_decode($response->content);

                    if ($response->status != 200) {
                        http_response_code(400);
                        return response([
                            'message' => 'Failed to send email.',
                            'errorCode' => 4100
                        ], 400);
                    }else{
                        return response([
                            'message' => 'Email notification has been sent to Distributor Admin and AR',
                        ]);

                    }
                }else{

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


                }





        http_response_code(200);
        return response([
            'message' => 'Data successfully saved.'
        ]);
    } catch (RequestException $r) {
        http_response_code(400);
        return response([
            'message' => 'Data failed to be save.',
            'errorCode' => 0
        ]);
    }
}



}
