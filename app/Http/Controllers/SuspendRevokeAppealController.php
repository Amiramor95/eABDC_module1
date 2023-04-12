<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\SuspendRevokeAppeal;
use App\Models\SuspendRevoke;
use App\Models\SuspendRevokeAppealAppr;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Helpers\ManageNotification;

class SuspendRevokeAppealController extends Controller
{
    public function updateAppeal(Request $request)
    {
        try{

            $searchAppealRecord = DB::table('distributor_management.SUSPEND_REVOKE_APPEAL')
            ->select('SUSPEND_REVOKE_APPEAL_ID')
            ->where('SUSPEND_REVOKE_ID','=', $request->SUSPEND_REVOKE_ID)
            ->first();

            if($searchAppealRecord){

                $data= SuspendRevokeAppeal::find($searchAppealRecord->SUSPEND_REVOKE_APPEAL_ID);
                $data->JUSTIFICATION = $request->JUSTIFICATION;
                $data->PUBLISH_STATUS = $request->PUBLISH_STATUS;
                $data->TS_ID = $request->TS_ID;
                $data->CREATE_BY = $request->CREATE_BY ;
                $data->save();

            }else{
                $dataNew = new SuspendRevokeAppeal;
                $dataNew->SUSPEND_REVOKE_ID = $request->SUSPEND_REVOKE_ID;
                $dataNew->JUSTIFICATION = $request->JUSTIFICATION;
                $dataNew->PUBLISH_STATUS = $request->PUBLISH_STATUS;
                $dataNew->TS_ID = $request->TS_ID;
                $dataNew->CREATE_BY = $request->CREATE_BY ;
                $dataNew->save();
            }

            if($request->PUBLISH_STATUS == "1"){

                $recordID = DB::table('distributor_management.SUSPEND_REVOKE_APPEAL')
                ->select('SUSPEND_REVOKE_APPEAL_ID')
                ->where('SUSPEND_REVOKE_ID','=', $request->SUSPEND_REVOKE_ID)
                ->first();

                $data= SuspendRevokeAppeal::find($recordID->SUSPEND_REVOKE_APPEAL_ID);
                $data->ISSUBMIT = $request->ISSUBMIT;
                $data->FIMM_TS_ID = 2;
                $data->save();

                $main= SuspendRevoke::find($request->SUSPEND_REVOKE_ID);
                $main->DIST_ACTION = 2;
                $main->save();

            foreach(json_decode($request->APPR_LIST) as $item){
                $dataApproval = new SuspendRevokeAppealAppr;
                $dataApproval->SUSPEND_REVOKE_APPEAL_ID = $recordID->SUSPEND_REVOKE_APPEAL_ID;
                $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                $dataApproval->TS_ID = $request->TS_ID;
                $dataApproval->save();

                $notification = new ManageNotification();
                $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);

            }
        }

        http_response_code(200);
        return response([
            'message' => 'Data successfully save'
        ]);


            } catch (RequestException $r) {

                http_response_code(400);
                return response([
                    'message' => $r,
                    'errorCode' => 4103,
                ], 400);
            }


    }

    public function AppealSuspendRevokeRecord()
    {
        try {
            $data = DB::table('distributor_management.SUSPEND_REVOKE_APPEAL AS SRA')
            ->select('SRA.SUSPEND_REVOKE_APPEAL_ID','SRA.FIMM_TS_ID','SRA.CREATE_BY','SRA.ISSUBMIT','SRA.JUSTIFICATION','SRA.SUSPEND_REVOKE_APPEAL_ID',
            'SRA.CREATE_TIMESTAMP AS DATE_APPEALED','TASK.TS_PARAM AS APPROVAL_STATUS','SRA.LATEST_UPDATE_BY','SRA.LATEST_UPDATE','USR.USER_NAME',
            'SR.SUSPEND_REVOKE_ID','SR.DISTRIBUTOR_ID','SR.SUBMISSION_TYPE','SR.DATE_START','SR.DATE_END',
            'SR.EFFECTIVE_DATE','SR.REASON','SR.CREATE_TIMESTAMP','SR.DIST_ACTION',
            'D.DIST_NAME','D.DIST_REGI_NUM1','D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL','TS.TS_PARAM AS DIST_STATUS',
            'DA.DIST_ID','DA.DIST_COUNTRY','DA.DIST_STATE','DA.DIST_CITY','DA.DIST_POSTAL','DA.DIST_STATE2','DA.DIST_CITY2','DA.DIST_POSTAL2',
            'DA.DIST_ADDR_1','DA.DIST_ADDR_2','DA.DIST_ADDR_3',
            'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME','COUNTRYNAME.SET_CODE AS SET_CODE',
            'STATENAME.SET_PARAM AS STATE_NAME','CITYNAME.SET_CITY_NAME','POSTAL.POSTCODE_NO'

            )

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


            ->orderBy('SRA.CREATE_TIMESTAMP','DESC')
            ->groupBy('SRA.SUSPEND_REVOKE_APPEAL_ID')
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

    public function filterAppeal(Request $request)
    {
        try {
            $query = DB::table('distributor_management.SUSPEND_REVOKE_APPEAL AS SRA')
            ->select('SRA.SUSPEND_REVOKE_APPEAL_ID','SRA.FIMM_TS_ID','SRA.CREATE_BY','SRA.ISSUBMIT','SRA.JUSTIFICATION','SRA.SUSPEND_REVOKE_APPEAL_ID',
            'SRA.CREATE_TIMESTAMP AS DATE_APPEALED','TASK.TS_PARAM AS APPROVAL_STATUS','SRA.LATEST_UPDATE_BY','SRA.LATEST_UPDATE','USR.USER_NAME',
            'SR.SUSPEND_REVOKE_ID','SR.DISTRIBUTOR_ID','SR.SUBMISSION_TYPE','SR.DATE_START','SR.DATE_END',
            'SR.EFFECTIVE_DATE','SR.REASON','SR.CREATE_TIMESTAMP','SR.DIST_ACTION',
            'D.DIST_NAME','D.DIST_REGI_NUM1','D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL','TS.TS_PARAM AS DIST_STATUS',
            'DA.DIST_ID','DA.DIST_COUNTRY','DA.DIST_STATE','DA.DIST_CITY','DA.DIST_POSTAL','DA.DIST_STATE2','DA.DIST_CITY2','DA.DIST_POSTAL2',
            'DA.DIST_ADDR_1','DA.DIST_ADDR_2','DA.DIST_ADDR_3',
            'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME','COUNTRYNAME.SET_CODE AS SET_CODE',
            'STATENAME.SET_PARAM AS STATE_NAME','CITYNAME.SET_CITY_NAME','POSTAL.POSTCODE_NO'

            )

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
            ->leftJoin('admin_management.SETTING_POSTAL AS POSTAL','POSTAL.SETTING_POSTCODE_ID','=','DA.DIST_POSTAL');


            if ($request->DIST_NAME != "") {
                $query->where('D.DIST_NAME', 'like', '%' . $request->DIST_NAME . '%');
            }
            if ($request->DIST_REGI_NUM1 != "") {
                $query->where('D.DIST_REGI_NUM1', 'like', '%' . $request->DIST_REGI_NUM1 . '%');
            }
            if ($request->DIST_REGI_NUM2 != "") {
                $query->where('D.DIST_REGI_NUM2', 'like', '%' . $request->DIST_REGI_NUM2 . '%');
            }


            $query->orderBy('SRA.CREATE_TIMESTAMP','DESC')
            ->groupBy('SRA.SUSPEND_REVOKE_APPEAL_ID')
            ->get();

            $data=$query->get();

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

}
