<?php

namespace App\Http\Controllers;
use App\Models\CandidateAcceptance;
use App\Models\DistRunno;
use App\Models\AcceptanceDetailsRejected;
use App\Models\AcceptanceDetails;
use App\Imports\AcceptanceListUpload;
use App\Http\Controllers\Controller;
use Ixudra\Curl\Facades\Curl;
use App\Imports\AcceptanceUploadSheet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Validator;
use DB;


class CandidateAcceptanceController extends Controller
{
     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function getCompanyID(Request $request)
    {
        try {

            $company = DB::table('distributor_management.USER AS UR')
            ->select('UR.USER_DIST_ID AS USER_DIST_ID')
            ->where('USER_ID',$request->USER_ID)
            ->first();


            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $company,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function getCompanyStatus(Request $request)
    {
        try {

            $company = DB::table('distributor_management.DISTRIBUTOR_STATUS')
            ->select('DIST_VALID_STATUS')
            ->where('DIST_ID',$request->DIST_ID)
            ->first();


            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $company,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }



    public function getAcceptanceListByDistID(Request $request)
    {
        try {
            $data = DB::table('distributor_management.CANDIDATE_ACCEPTANCE AS CA')
            ->select('CA.CANDIDATE_ACCEPTANCE_ID','CA.DISTRIBUTOR_ID','CA.REFERENCE_NO','CA.CREATE_BY','CA.CREATE_TIMESTAMP',
            DB::raw('COUNT(AD.CANDIDATE_ACCEPTANCE_ID) AS TOTAL_CANDIDATE'),'USER.USER_NAME AS USERNAME','TS.TS_PARAM','CA.TS_ID')
            ->leftJoin('distributor_management.ACCEPTANCE_DETAILS AS AD','AD.CANDIDATE_ACCEPTANCE_ID','=','CA.CANDIDATE_ACCEPTANCE_ID')
            ->leftJoin('distributor_management.USER AS USER','USER.USER_ID','=','CA.CREATE_BY')
            ->leftJoin('admin_management.TASK_STATUS AS TS','TS.TS_ID','=','CA.TS_ID')
            ->where('CA.DISTRIBUTOR_ID', $request->DISTRIBUTOR_ID)
            ->orderBy('CA.CREATE_TIMESTAMP', 'DESC')
            ->groupBy('CA.CANDIDATE_ACCEPTANCE_ID')
            ->get();

            foreach($data as $item){

                $item->CREATE_TIMESTAMP =  $item->CREATE_TIMESTAMP ?? '-';
                $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));
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

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'file|nullable',
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.' . $validator->messages(),
                'errorCode' => 4106,
            ], 400);
        }

        //
        try {

            $runNo = DB::table('distributor_management.DIST_RUNNO AS RUN')
            ->where('RUN.DISTRIBUTOR_ID',$request->COMPANY_ID)
            ->first();

            $distRNo = $runNo->CURRENT_NO + 1;
            $referenceNo = $runNo->DISTRIBUTOR_CODE.$distRNo;

            // create master
            $inputs['DISTRIBUTOR_ID'] = $request-> COMPANY_ID;
            $inputs['CREATE_BY'] = $request-> CREATE_BY;
            $inputs['REFERENCE_NO'] = $referenceNo;
            $inputs['TS_ID'] = 1;

            $acceptance = CandidateAcceptance::create($inputs);
            $inputs['CANDIDATE_ACCEPTANCE_ID'] = $acceptance->CANDIDATE_ACCEPTANCE_ID;

            //update dist no
            $updRunNo = DistRunno::find($runNo->DIST_RUNNO_ID);
            $updRunNo->CURRENT_NO = $distRNo;
            $updRunNo->save();



            Excel::import(new AcceptanceListUpload($inputs['CANDIDATE_ACCEPTANCE_ID'],$inputs['DISTRIBUTOR_ID']), $request->file('file'));

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'CANDIDATE_ACCEPTANCE_ID' => $acceptance->CANDIDATE_ACCEPTANCE_ID,
            ]);

        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => $r,
                'errorCode' => 4103,
            ], 400);
        }

    }

    public function acceptRecord(Request $request){

        try {

            $url = env('URL_SERVER') . '/api/module0/send_acceptance_email';
        $data = CandidateAcceptance::find($request->CANDIDATE_ACCEPTANCE_ID);
        $data->TS_ID = 17;
        $data->save();

        $data1 = AcceptanceDetails::where('CANDIDATE_ACCEPTANCE_ID',$request->CANDIDATE_ACCEPTANCE_ID)->get();

        $data2 = AcceptanceDetailsRejected::where('CANDIDATE_ACCEPTANCE_ID',$request->CANDIDATE_ACCEPTANCE_ID)->get();
        foreach($data2 as $item2){
            $item2->delete();
        }


        foreach($data1 as $item1){
           //1.update status
            $item1->TS_ID = 15;
            $item1->save();

            //2.send email

            $emailData = DB::table('distributor_management.ACCEPTANCE_DETAILS AS AD')
            ->select('AD.CANDIDATE_NAME','AD.CANDIDATE_NRIC','AD.CANDIDATE_PASSPORT_NO',
            'AD.CANDIDATE_EMAIL','AD.CANDIDATE_PHONENO','AD.LICENSE_TYPE','AD.STAFF_OR_AGENT','CT.TYPE_SCHEME','D.DIST_NAME')
            ->leftJoin('distributor_management.CANDIDATE_ACCEPTANCE AS CA','CA.CANDIDATE_ACCEPTANCE_ID','=','AD.CANDIDATE_ACCEPTANCE_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR AS D','D.DISTRIBUTOR_ID','=','CA.DISTRIBUTOR_ID')
            ->leftJoin('admin_management.CONSULTANT_TYPE AS CT','CT.CONSULTANT_TYPE_ID','=','AD.LICENSE_TYPE')
            ->where('AD.ACCEPTANCE_DETAILS_ID',$item1->ACCEPTANCE_DETAILS_ID)
            ->first();

            $email=  $emailData->CANDIDATE_EMAIL;
            $name = $emailData->CANDIDATE_NAME;
            $nric = substr($emailData->CANDIDATE_NRIC, 0, 6) . '-' . substr($emailData->CANDIDATE_NRIC, 6, 2) . '-' . substr($emailData->CANDIDATE_NRIC, 8, 4);
            $passportNo = $emailData->CANDIDATE_PASSPORT_NO ?? '-';
            $phoneNo = $emailData->CANDIDATE_PHONENO;
            $licenseType = $emailData->TYPE_SCHEME;
            if ($emailData->STAFF_OR_AGENT == 1){
              $staffOrAgent = "STAFF";
            }else if ($emailData->STAFF_OR_AGENT == 2){
              $staffOrAgent = "AGENT";
            }
            $distName = $emailData->DIST_NAME;
            $title = "Your following application has been accepted by the distributor. Please click the link below to proceed with the registration.";

            //$response = Curl::to('http://192.168.3.24/api/module0/send_acceptance_email')
            $response =  Curl::to($url)
            ->withData(['email' => $email,'name' => $name,'nric' => $nric,'passportNo' => $passportNo,'phoneNo' => $phoneNo,'licenseType' => $licenseType,'staffOrAgent' => $staffOrAgent,
            'distName' => $distName,'title' => $title])
            ->returnResponseObject()
            ->post();

               $content = json_decode($response->content);

               if ($response->status != 200) {
                   http_response_code(400);

                   return response([
                       'message' => 'Failed to send email.',
                       'errorCode' => 4100
                   ], 400);
               }
        }


        http_response_code(200);
            return response([
                'message' => 'Data successfully accepted.'
            ]);

        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be accepted.',
                'errorCode' => 4102
            ],400);
        }

    }

    public function deleteRecordByID(Request $request)
    {
        try {
            $data1 = CandidateAcceptance::find($request->CANDIDATE_ACCEPTANCE_ID);
            $data1->delete();

            http_response_code(200);
            return response([
                'message' => 'Data successfully deleted.'
            ]);

        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be deleted.',
                'errorCode' => 4102
            ],400);
        }
    }

    public function filterRecord(Request $request)
    {
        try {
            $query = DB::table('distributor_management.ACCEPTANCE_DETAILS AS AD')
            ->select('AD.ACCEPTANCE_DETAILS_ID','AD.CANDIDATE_NAME','AD.CANDIDATE_NRIC','AD.CANDIDATE_PASSPORT_NO',
            'AD.CANDIDATE_EMAIL','AD.CANDIDATE_PHONENO','AD.LICENSE_TYPE','AD.STAFF_OR_AGENT','AD.CA_CLASSIFICATION',
            'AD.TS_ID','TS.TS_PARAM','CT.TYPE_SCHEME','SG.SET_PARAM',
            'CA.CANDIDATE_ACCEPTANCE_ID','CA.DISTRIBUTOR_ID','CA.REFERENCE_NO','CA.CREATE_BY',
            'CA.CREATE_TIMESTAMP','USER.USER_NAME AS USERNAME')

            ->leftJoin('admin_management.CONSULTANT_TYPE AS CT','CT.CONSULTANT_TYPE_ID','=','AD.LICENSE_TYPE')
            ->leftJoin('admin_management.TASK_STATUS AS TS','TS.TS_ID','=','AD.TS_ID')
            ->leftJoin('admin_management.SETTING_GENERAL AS SG','SG.SETTING_GENERAL_ID','=','AD.CA_CLASSIFICATION')
            ->leftJoin('distributor_management.CANDIDATE_ACCEPTANCE AS CA','CA.CANDIDATE_ACCEPTANCE_ID','=','AD.CANDIDATE_ACCEPTANCE_ID')
            ->leftJoin('distributor_management.USER AS USER','USER.USER_ID','=','CA.CREATE_BY');

                if ($request->CANDIDATE_NAME != "") {
                    $query->where('AD.CANDIDATE_NAME', 'like', '%' . $request->CANDIDATE_NAME . '%');
                }
                if ($request->CANDIDATE_NRIC != "") {
                    $query->where('AD.CANDIDATE_NRIC', 'like', '%' . str_replace('-', '', $request->CANDIDATE_NRIC) . '%');
                }
                if ($request->CANDIDATE_PASSPORT_NO != "") {
                    $query->where('AD.CANDIDATE_PASSPORT_NO','like', '%' .  $request->CANDIDATE_PASSPORT_NO . '%');

                }

            $query->orderBy('AD.ACCEPTANCE_DETAILS_ID', 'DESC');

            $data = $query->get();

            foreach ($data as $item) {
                $item->CANDIDATE_NAME = strtoupper($item->CANDIDATE_NAME);

                $item->CREATE_TIMESTAMP =  $item->CREATE_TIMESTAMP ?? '-';
                $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));

                if ($item->CANDIDATE_PASSPORT_NO != null) {
                } else {
                    $item->CANDIDATE_PASSPORT_NO = $item->CANDIDATE_PASSPORT_NO ?? '-';
                }
                if ($item->CANDIDATE_NRIC != null) {
                    $item->CANDIDATE_NRIC = substr($item->CANDIDATE_NRIC, 0, 6) . '-' . substr($item->CANDIDATE_NRIC, 6, 2) . '-' . substr($item->CANDIDATE_NRIC, 8, 4);
                } else {
                    $item->CANDIDATE_NRIC = $item->CANDIDATE_NRIC ?? '-';
                }

                if ($item->SET_PARAM != null || $item->SET_PARAM != "") {
                }else{
                    $item->SET_PARAM  = '-';
                }
            }

            http_response_code(200);
            return response([
                'message' => 'Filtered data successfully retrieved.',
                'data' => $data,
            ]);

        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Filtered data failed to be retrieved.',
                'errorCode' => 4105,
            ], 400);
        }
    }
}
