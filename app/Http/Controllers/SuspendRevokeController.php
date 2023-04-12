<?php

namespace App\Http\Controllers;

use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use GuzzleHttp\Exception\RequestException;
use App\Models\SuspendRevoke;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use App\Models\suspendRevokeDocument;
use App\Models\SuspendRevokeApproval;
use Illuminate\Http\Request;
use Validator;
use DB;
use Illuminate\Support\Str;



class SuspendRevokeController extends Controller
{
    public function getDistributorRecord()
    {
        try {
			$data = DB::table('distributor_management.DISTRIBUTOR AS D')
            ->select('D.DISTRIBUTOR_ID','D.DIST_NAME','D.DIST_REGI_NUM1','D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL','TS.TS_PARAM',
            'DA.DIST_COUNTRY','DA.DIST_STATE','DA.DIST_CITY','DA.DIST_POSTAL','DA.DIST_STATE2','DA.DIST_CITY2','DA.DIST_POSTAL2',
            'DA.DIST_ADDR_1','DA.DIST_ADDR_2','DA.DIST_ADDR_3',
            'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME','COUNTRYNAME.SET_CODE AS SET_CODE',
            'STATENAME.SET_PARAM AS STATE_NAME','CITYNAME.SET_CITY_NAME','POSTAL.POSTCODE_NO')

            ->leftJoin('distributor_management.DISTRIBUTOR_ADDRESS AS DA','DA.DIST_ID','=','D.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR_TYPE AS DT','DT.DIST_ID','=','D.DISTRIBUTOR_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID','=','DT.ISACTIVE')
            ->leftJoin('admin_management.SETTING_GENERAL AS COUNTRYNAME','COUNTRYNAME.SETTING_GENERAL_ID','=','DA.DIST_COUNTRY')
            ->leftJoin('admin_management.SETTING_GENERAL AS STATENAME','STATENAME.SETTING_GENERAL_ID','=','DA.DIST_STATE')
            ->leftJoin('admin_management.SETTING_CITY AS CITYNAME','CITYNAME.SETTING_CITY_ID','=','DA.DIST_CITY')
            ->leftJoin('admin_management.SETTING_POSTAL AS POSTAL','POSTAL.SETTING_POSTCODE_ID','=','DA.DIST_POSTAL')

            ->where('DT.ISACTIVE','!=', null)
            ->where('DT.ISACTIVE','=', 22)

            ->groupBy('D.DISTRIBUTOR_ID')
            ->orderBy('D.DIST_NAME','ASC')

            ->get();

            foreach($data as $item){

                $item->CREATE_TIMESTAMP =  $item->CREATE_TIMESTAMP ?? '-';
                $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));

                if ($item->DIST_PHONE_NUMBER != null) {
                    $item->DIST_PHONE_NUMBER = substr($item->DIST_PHONE_NUMBER, 0, 2).'-'.substr($item->DIST_PHONE_NUMBER, 2,8);
                }else
                {
                    $item->DIST_PHONE_NUMBER  =  '-';
                }

                if($item->DIST_ADDR_2 != null){
                    $item->DIST_ADDR_2 = $item->DIST_ADDR_2." " ;
                }else{
                    $item->DIST_ADDR_2 = '';
                }

                if($item->DIST_ADDR_3 != null){
                    $item->DIST_ADDR_3 = $item->DIST_ADDR_3." " ;
                }else{
                    $item->DIST_ADDR_3 = '';
                }

                if($item->DIST_EMAIL == null || $item->DIST_EMAIL == ""){
                    $item->DIST_EMAIL = '-';
                }

                if($item->DIST_REGI_NUM1 == null || $item->DIST_REGI_NUM1 == ""){
                    $item->DIST_REGI_NUM1 = '-';
                }
                if($item->DIST_REGI_NUM2 == null || $item->DIST_REGI_NUM2 == ""){
                    $item->DIST_REGI_NUM2 = '-';
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

    public function createSubmission(Request $request)
    {
        try{

            $data = new SuspendRevoke;
            $data->SUBMISSION_TYPE = $request->SUBMISSION_TYPE;
            $data->DISTRIBUTOR_ID = $request->DISTRIBUTOR_ID;
            $data->DATE_START = $request->DATE_START;
            $data->DATE_END = $request->DATE_END;
            $data->EFFECTIVE_DATE = $request->EFFECTIVE_DATE;
            $data->REASON = $request->REASON;
            $data->PUBLISH_STATUS = $request->PUBLISH_STATUS;
            $data->TS_ID = $request->TS_ID;
            $data->CREATE_BY = $request->CREATE_BY ;
            $data->LATEST_UPDATE_BY = $request->CREATE_BY ;
            $data->save();

            if($request->PUBLISH_STATUS == "1"){

                //first entry
                foreach(json_decode($request->ENTRY_LIST) as $item){
                   $dataEntry = new SuspendRevokeApproval;
                   $dataEntry->SUSPEND_REVOKE_ID = $data->SUSPEND_REVOKE_ID;
                   $dataEntry->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                   $dataEntry->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                   $dataEntry->TS_ID = 2;
                   $dataEntry->CREATE_BY = $request->CREATE_BY;
                   $dataEntry->APPR_PUBLISH_STATUS = $request->PUBLISH_STATUS;
                   $dataEntry->save();
               }

               foreach(json_decode($request->APPR_LIST) as $item){
                   $dataApproval = new SuspendRevokeApproval;
                   $dataApproval->SUSPEND_REVOKE_ID = $data->SUSPEND_REVOKE_ID;
                   $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                   $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                   $dataApproval->TS_ID = $request->TS_ID;
                   $dataApproval->save();

                   $notification = new ManageNotification();
                   $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);


               }
           }

            $file = $request->file;
            if ($file != null){
            foreach($file as $item){
                $itemFile = $item;
                $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                $upFile = new suspendRevokeDocument;
                $upFile->DOC_BLOB = $blob;
                $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                $upFile->DOC_FILESIZE = $itemFile->getSize();
                $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                $upFile->CREATE_BY = $request->CREATE_BY;
                $upFile->SUSPEND_REVOKE_ID = $data->SUSPEND_REVOKE_ID;
                $upFile->save();
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

    public function suspendRevokeRecord()
    {
        try {
			$data = DB::table('distributor_management.SUSPEND_REVOKE AS SR')
            ->select('SR.SUSPEND_REVOKE_ID','SR.DISTRIBUTOR_ID','SR.SUBMISSION_TYPE','SR.DATE_START','SR.DATE_END','SR.EFFECTIVE_DATE',
            'SR.REASON','SR.TS_ID','SR.LATEST_UPDATE','SR.LATEST_UPDATE_BY','SR.CREATE_TIMESTAMP','DA.DIST_ID',
            'D.DIST_NAME','D.DIST_REGI_NUM1','D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL','TS.TS_PARAM',
            'DA.DIST_COUNTRY','DA.DIST_STATE','DA.DIST_CITY','DA.DIST_POSTAL','DA.DIST_STATE2','DA.DIST_CITY2','DA.DIST_POSTAL2',
            'DA.DIST_ADDR_1','DA.DIST_ADDR_2','DA.DIST_ADDR_3',
            'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME','COUNTRYNAME.SET_CODE AS SET_CODE',
            'STATENAME.SET_PARAM AS STATE_NAME','CITYNAME.SET_CITY_NAME','POSTAL.POSTCODE_NO',
            'TASK.TS_PARAM AS STATUS','USR.USER_NAME','SR.ISSUBMIT')

            ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID','=','SR.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR_ADDRESS AS DA','DA.DIST_ID','=','D.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR_TYPE AS DT','DT.DIST_ID','=','D.DISTRIBUTOR_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID','=','DT.ISACTIVE')

            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'SR.TS_ID')
            ->leftJoin('admin_management.USER AS USR','USR.USER_ID', '=', 'SR.LATEST_UPDATE_BY')

            ->leftJoin('admin_management.SETTING_GENERAL AS COUNTRYNAME','COUNTRYNAME.SETTING_GENERAL_ID','=','DA.DIST_COUNTRY')
            ->leftJoin('admin_management.SETTING_GENERAL AS STATENAME','STATENAME.SETTING_GENERAL_ID','=','DA.DIST_STATE')
            ->leftJoin('admin_management.SETTING_CITY AS CITYNAME','CITYNAME.SETTING_CITY_ID','=','DA.DIST_CITY')
            ->leftJoin('admin_management.SETTING_POSTAL AS POSTAL','POSTAL.SETTING_POSTCODE_ID','=','DA.DIST_POSTAL')

            ->groupBy('SR.SUSPEND_REVOKE_ID')
            ->orderBy('SR.CREATE_TIMESTAMP','DESC')
            ->orderBy('SR.LATEST_UPDATE','DESC')


            ->get();

            foreach($data as $item){
                if($item->CREATE_TIMESTAMP != null || $item->CREATE_TIMESTAMP != ""){
                    $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));
                }else{
                $item->CREATE_TIMESTAMP = '-';
                }

                if ($item->LATEST_UPDATE == null){
                    $item->LATEST_UPDATE = '-';
                }else{
                $item->LATEST_UPDATE =  $item->LATEST_UPDATE ?? '-';
                $item->LATEST_UPDATE = date('d-M-Y H:i:s', strtotime($item->LATEST_UPDATE));
                }

                if($item->SUBMISSION_TYPE == 1){
                    $item->SUBMISSION_TYPE = 'SUSPENSION';
                }elseif($item->SUBMISSION_TYPE == 2){
                $item->SUBMISSION_TYPE = 'REVOCATION';
                }else {
                    $item->SUBMISSION_TYPE ='-';
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
                    $item->DATE_END = "-";
                }
                if($item->EFFECTIVE_DATE != null || $item->EFFECTIVE_DATE  != ""){
                    $item->EFFECTIVE_DATE  = date('d-M-Y', strtotime($item->EFFECTIVE_DATE ));
                }else{
                    $item->EFFECTIVE_DATE = "-";
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

    public function updateSubmission(Request $request)
    {
        try{

            $data= SuspendRevoke::find($request->SUSPEND_REVOKE_ID);
            $data->SUBMISSION_TYPE = $request->SUBMISSION_TYPE;
            $data->DISTRIBUTOR_ID = $request->DISTRIBUTOR_ID;
            $data->DATE_START = $request->DATE_START;
            $data->DATE_END = $request->DATE_END;
            $data->EFFECTIVE_DATE = $request->EFFECTIVE_DATE;
            $data->REASON = $request->REASON;
            $data->PUBLISH_STATUS = $request->PUBLISH_STATUS;
            $data->TS_ID = $request->TS_ID;
            $data->CREATE_BY = $request->CREATE_BY ;
            $data->LATEST_UPDATE_BY = $request->CREATE_BY ;
            $data->save();

            if($request->PUBLISH_STATUS == "1"){

                //first entry
                foreach(json_decode($request->ENTRY_LIST) as $item){

                   $dataEntry = new SuspendRevokeApproval;
                   $dataEntry->SUSPEND_REVOKE_ID = $data->SUSPEND_REVOKE_ID;
                   $dataEntry->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                   $dataEntry->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                   if($request->ISSUBMIT == 0){
                   $dataEntry->TS_ID = 2;
                   }else {
                    $dataEntry->TS_ID = 37;
                   }
                   $dataEntry->CREATE_BY = $request->CREATE_BY;
                   $dataEntry->APPR_PUBLISH_STATUS = $request->PUBLISH_STATUS;
                   $query = $dataEntry->save();
               }

               foreach(json_decode($request->APPR_LIST) as $item){
                   $dataApproval = new SuspendRevokeApproval;
                   $dataApproval->SUSPEND_REVOKE_ID = $data->SUSPEND_REVOKE_ID;
                   $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                   $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                   $dataApproval->TS_ID = $request->TS_ID;
                   $dataApproval->save();

                   $notification = new ManageNotification();
                   $add = $notification->add($item->APPR_GROUP_ID,$item->APPR_PROCESSFLOW_ID,$request->NOTI_REMARK,$request->NOTI_LOCATION);

               }
           }

            $file = $request->file;
            if ($file != null){
            foreach($file as $item){
                $itemFile = $item;
                $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                $upFile = new suspendRevokeDocument;
                $upFile->DOC_BLOB = $blob;
                $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                $upFile->DOC_FILESIZE = $itemFile->getSize();
                $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                $upFile->CREATE_BY = $request->CREATE_BY;
                $upFile->SUSPEND_REVOKE_ID = $request->SUSPEND_REVOKE_ID;
                $upFile->save();
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

    public function filter(Request $request)
    {
        try {
			$query = DB::table('distributor_management.SUSPEND_REVOKE AS SR')
            ->select('SR.SUSPEND_REVOKE_ID','SR.DISTRIBUTOR_ID','SR.SUBMISSION_TYPE','SR.DATE_START','SR.DATE_END','SR.EFFECTIVE_DATE',
            'SR.REASON','SR.TS_ID','SR.LATEST_UPDATE','SR.LATEST_UPDATE_BY','SR.CREATE_TIMESTAMP','DA.DIST_ID',
            'D.DIST_NAME','D.DIST_REGI_NUM1','D.DIST_REGI_NUM2','D.DIST_PHONE_NUMBER','D.DIST_EMAIL','TS.TS_PARAM',
            'DA.DIST_COUNTRY','DA.DIST_STATE','DA.DIST_CITY','DA.DIST_POSTAL','DA.DIST_STATE2','DA.DIST_CITY2','DA.DIST_POSTAL2',
            'DA.DIST_ADDR_1','DA.DIST_ADDR_2','DA.DIST_ADDR_3',
            'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME','COUNTRYNAME.SET_CODE AS SET_CODE',
            'STATENAME.SET_PARAM AS STATE_NAME','CITYNAME.SET_CITY_NAME','POSTAL.POSTCODE_NO',
            'TASK.TS_PARAM AS STATUS','USR.USER_NAME','SR.ISSUBMIT')

            ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID','=','SR.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR_ADDRESS AS DA','DA.DIST_ID','=','D.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR_TYPE AS DT','DT.DIST_ID','=','D.DISTRIBUTOR_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID','=','DT.ISACTIVE')

            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'SR.TS_ID')
            ->leftJoin('admin_management.USER AS USR','USR.USER_ID', '=', 'SR.LATEST_UPDATE_BY')

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

            $query->groupBy('SR.SUSPEND_REVOKE_ID')
            ->orderBy('SR.LATEST_UPDATE','DESC')
            ->orderBy('SR.CREATE_TIMESTAMP','DESC');

            $data=$query->get();

            foreach($data as $item){
                if($item->CREATE_TIMESTAMP != null || $item->CREATE_TIMESTAMP != ""){
                    $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));
                }else{
                $item->CREATE_TIMESTAMP = '-';
                }

                if ($item->LATEST_UPDATE == null){
                    $item->LATEST_UPDATE = '-';
                }else{
                $item->LATEST_UPDATE =  $item->LATEST_UPDATE ?? '-';
                $item->LATEST_UPDATE = date('d-M-Y H:i:s', strtotime($item->LATEST_UPDATE));
                }

                if($item->SUBMISSION_TYPE == 1){
                    $item->SUBMISSION_TYPE = 'SUSPENSION';
                }elseif($item->SUBMISSION_TYPE == 2){
                $item->SUBMISSION_TYPE = 'REVOCATION';
                }else {
                    $item->SUBMISSION_TYPE ='-';
                }

                if ($item->USER_NAME == null || $item->USER_NAME =="" ){
                    $item->USER_NAME = '-';
                }

                if($item->DATE_START != null || $item->DATE_START != ""){
                    $item->DATE_START = date('d-M-Y', strtotime($item->DATE_START));
                }
                if($item->DATE_END != null || $item->DATE_END != ""){
                    $item->DATE_END = date('d-M-Y', strtotime($item->DATE_END));
                }
                if($item->EFFECTIVE_DATE != null || $item->EFFECTIVE_DATE  != ""){
                    $item->EFFECTIVE_DATE  = date('d-M-Y', strtotime($item->EFFECTIVE_DATE ));
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

    public function getRecordByID(Request $request)
    {
        try {
			$data = DB::table('distributor_management.SUSPEND_REVOKE AS SR')
            ->select('SR.SUSPEND_REVOKE_ID','SR.DISTRIBUTOR_ID','D.DIST_NAME','SR.SUBMISSION_TYPE','SR.DATE_START','SR.DATE_END',
            'SR.EFFECTIVE_DATE','SR.REASON AS REASON','SR.REASON AS REASON_FULL','SR.CREATE_TIMESTAMP','SR.DIST_ACTION','SRA.TS_ID','SRA.CREATE_BY',
            'TASK.TS_PARAM','USR.USER_NAME','SRA.ISSUBMIT','SRA.JUSTIFICATION','SRA.SUSPEND_REVOKE_APPEAL_ID',
            'SRA.CREATE_TIMESTAMP AS DATE_APPEALED','SR.DIST_ACTION','SRA.FIMM_REMARK','SRA.FIMM_REMARK AS FIMM_REMARK_FULL')

            ->leftJoin('distributor_management.SUSPEND_REVOKE_APPEAL AS SRA','SRA.SUSPEND_REVOKE_ID','=','SR.SUSPEND_REVOKE_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TASK','TASK.TS_ID', '=', 'SRA.TS_ID')
            ->leftJoin('distributor_management.USER AS USR','USR.USER_ID', '=', 'SRA.CREATE_BY')
            ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID', '=', 'SR.DISTRIBUTOR_ID')

            ->orderBy('SR.CREATE_TIMESTAMP','DESC')
            ->where('SR.DISTRIBUTOR_ID','=', $request->DISTRIBUTOR_ID)

            ->get();

            foreach($data as $item){
                if($item->CREATE_TIMESTAMP != null || $item->CREATE_TIMESTAMP != ""){
                    $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));
                }else{
                $item->CREATE_TIMESTAMP = '-';
                }

                if($item->DATE_APPEALED != null || $item->DATE_APPEALED != ""){
                    $item->DATE_APPEALED = date('d-M-Y', strtotime($item->DATE_APPEALED));
                }else{
                $item->DATE_APPEALED = '-';
                }

                if($item->DIST_ACTION == 0){
                    $item->DIST_ACTION ='-';
                }

                if($item->SUBMISSION_TYPE == 1){
                    $item->SUBMISSION_TYPE = 'SUSPENDED';
                }elseif($item->SUBMISSION_TYPE == 2){
                $item->SUBMISSION_TYPE = 'REVOKED';
                }else {
                    $item->SUBMISSION_TYPE ='-';
                }

                if($item->DIST_ACTION == 1){
                    $item->DIST_ACTION = 'ACCEPTED';
                }elseif($item->DIST_ACTION == 2){
                $item->DIST_ACTION = 'APPEALED';
                }else {
                    $item->DIST_ACTION ='-';
                }

                if($item->DATE_START != null || $item->DATE_START != ""){
                    $item->DATE_START = date('d-M-Y', strtotime($item->DATE_START));
                }else{
                    $item->DATE_START ="-";
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
                if ($item->FIMM_REMARK != null) {
                    $item->FIMM_REMARK = Str::limit($item->FIMM_REMARK, 20);
                }else{
                    $item->FIMM_REMARK = '-';
                }
                if ($item->REASON != null || $item->REASON != "" ) {
                    $item->REASON = Str::limit($item->REASON, 30);
                }else{
                    $item->REASON = '-';
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
