<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\AcceptanceDetails;
use App\Models\CandidateAcceptance;
use App\Models\AcceptanceDetailsRejected;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use Validator;
use DB;

class AcceptanceDetailsController extends Controller
{
    public function getCandidateListByDistID(Request $request)
    {
        try {
            $data = DB::table('distributor_management.ACCEPTANCE_DETAILS AS AD')
                ->select(
                    'AD.ACCEPTANCE_DETAILS_ID',
                    'AD.CANDIDATE_NAME',
                    'AD.CANDIDATE_NRIC',
                    'AD.CANDIDATE_PASSPORT_NO',
                    'AD.CANDIDATE_EMAIL',
                    'AD.CANDIDATE_PHONENO',
                    'AD.LICENSE_TYPE',
                    'AD.STAFF_OR_AGENT',
                    'AD.CA_CLASSIFICATION',
                    'AD.TS_ID',
                    'TS.TS_PARAM',
                    'CT.TYPE_SCHEME',
                    'SG.SET_PARAM'
                )
                ->leftJoin('admin_management.CONSULTANT_TYPE AS CT', 'CT.CONSULTANT_TYPE_ID', '=', 'AD.LICENSE_TYPE')
                ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID', '=', 'AD.TS_ID')
                ->leftJoin('admin_management.SETTING_GENERAL AS SG', 'SG.SETTING_GENERAL_ID', '=', 'AD.CA_CLASSIFICATION')
                ->where('AD.CANDIDATE_ACCEPTANCE_ID', $request->CANDIDATE_ACCEPTANCE_ID)
                ->get();

            foreach ($data as $item) {
                if ($item->CANDIDATE_PASSPORT_NO != null || $item->CANDIDATE_PASSPORT_NO != "") {
                } else {
                    $item->CANDIDATE_PASSPORT_NO  = '-';
                }
                if ($item->CANDIDATE_NRIC != null || $item->CANDIDATE_NRIC != "") {
                    $item->CANDIDATE_NRIC = substr($item->CANDIDATE_NRIC, 0, 6) . '-' . substr($item->CANDIDATE_NRIC, 6, 2) . '-' . substr($item->CANDIDATE_NRIC, 8, 4);
                } else {
                    $item->CANDIDATE_NRIC  = '-';
                }

                if ($item->SET_PARAM != null || $item->SET_PARAM != "") {
                } else {
                    $item->SET_PARAM  = '-';
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
            ], 400);
        }
    }

    public function deleteCandidateByID(Request $request)
    {
        try {


            $data = AcceptanceDetails::find($request->ACCEPTANCE_DETAILS_ID);
            $data->delete();

            http_response_code(200);
            return response([
                'message' => 'Data successfully deleted.'
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be deleted.',
                'errorCode' => 4102
            ], 400);
        }
    }

    public function getRejectedByID(Request $request)
    {
        try {

            $reject = DB::table('distributor_management.ACCEPTANCE_DETAILS_REJECTED AS AD')
                ->select(
                    'AD.CANDIDATE_NAME AS NAME',
                    'AD.CANDIDATE_NRIC AS NRIC_NUMBER',
                    'AD.CANDIDATE_PASSPORT_NO AS PASSPORT_NUMBER',
                    'AD.CANDIDATE_EMAIL AS EMAIL',
                    'AD.CANDIDATE_PHONENO AS PHONE_NUMBER',
                    'AD.LICENSE_TYPE',
                    'AD.STAFF_OR_AGENT',
                    'AD.REASON'
                )
                ->where('AD.CANDIDATE_ACCEPTANCE_ID', $request->CANDIDATE_ACCEPTANCE_ID)
                ->get();


            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $reject,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function getAcceptedByID(Request $request)
    {
        try {

            $reject = DB::table('distributor_management.ACCEPTANCE_DETAILS AS AD')
                ->select(
                    'AD.CANDIDATE_NAME AS NAME',
                    'AD.CANDIDATE_NRIC AS NRIC_NUMBER',
                    'AD.CANDIDATE_PASSPORT_NO AS PASSPORT_NUMBER',
                    'AD.CANDIDATE_EMAIL AS EMAIL',
                    'AD.CANDIDATE_PHONENO AS PHONE_NUMBER',
                    'AD.LICENSE_TYPE',
                    'AD.STAFF_OR_AGENT',
                    'SG.SET_PARAM'
                )
                ->leftJoin('admin_management.SETTING_GENERAL AS SG', 'SG.SETTING_GENERAL_ID', '=', 'AD.CA_CLASSIFICATION')
                ->where('AD.CANDIDATE_ACCEPTANCE_ID', $request->CANDIDATE_ACCEPTANCE_ID)
                ->get();


            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $reject,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function discardRecordByID(Request $request)
    {
        try {

            $data1 = AcceptanceDetailsRejected::where('CANDIDATE_ACCEPTANCE_ID', $request->CANDIDATE_ACCEPTANCE_ID)->get();

            foreach ($data1 as $item1) {
                $item1->delete();
            }

            $data2 = AcceptanceDetails::where('CANDIDATE_ACCEPTANCE_ID', $request->CANDIDATE_ACCEPTANCE_ID)->get();
            foreach ($data2 as $item2) {
                $item2->delete();
            }

            $data3 = CandidateAcceptance::where('CANDIDATE_ACCEPTANCE_ID', $request->CANDIDATE_ACCEPTANCE_ID)->first();
            $data3->delete();

            http_response_code(200);
            return response([
                'message' => 'Data successfully deleted.'
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be deleted.',
                'errorCode' => 4102
            ], 400);
        }
    }

    public function emailCandidate(Request $request)
    {

        try {
            $url = env('URL_SERVER') . '/api/module0/send_acceptance_email';
            //EMAIL NOTIFICATION
            $emailData = DB::table('distributor_management.ACCEPTANCE_DETAILS AS AD')
                ->select(
                    'AD.CANDIDATE_NAME',
                    'AD.CANDIDATE_NRIC',
                    'AD.CANDIDATE_PASSPORT_NO',
                    'AD.CANDIDATE_EMAIL',
                    'AD.CANDIDATE_PHONENO',
                    'AD.LICENSE_TYPE',
                    'AD.STAFF_OR_AGENT',
                    'CT.TYPE_SCHEME',
                    'D.DIST_NAME'
                )
                ->leftJoin('distributor_management.CANDIDATE_ACCEPTANCE AS CA', 'CA.CANDIDATE_ACCEPTANCE_ID', '=', 'AD.CANDIDATE_ACCEPTANCE_ID')
                ->leftJoin('distributor_management.DISTRIBUTOR AS D', 'D.DISTRIBUTOR_ID', '=', 'CA.DISTRIBUTOR_ID')
                ->leftJoin('admin_management.CONSULTANT_TYPE AS CT', 'CT.CONSULTANT_TYPE_ID', '=', 'AD.LICENSE_TYPE')
                ->where('AD.ACCEPTANCE_DETAILS_ID', $request->ACCEPTANCE_DETAILS_ID)
                ->first();

            $email =  $emailData->CANDIDATE_EMAIL;
            $name = $emailData->CANDIDATE_NAME;
            $nric = substr($emailData->CANDIDATE_NRIC, 0, 6) . '-' . substr($emailData->CANDIDATE_NRIC, 6, 2) . '-' . substr($emailData->CANDIDATE_NRIC, 8, 4);
            $passportNo = $emailData->CANDIDATE_PASSPORT_NO ?? '-';
            $phoneNo = $emailData->CANDIDATE_PHONENO;
            $licenseType = $emailData->TYPE_SCHEME;
            if ($emailData->STAFF_OR_AGENT == 1) {
                $staffOrAgent = "STAFF";
            } else if ($emailData->STAFF_OR_AGENT == 2) {
                $staffOrAgent = "AGENT";
            }
            $distName = $emailData->DIST_NAME;
            $title = $request->TITLE;


            //$response = Curl::to('http://192.168.3.24/api/module0/send_acceptance_email')
            $response =  Curl::to($url)
                ->withData([
                    'email' => $email, 'name' => $name, 'nric' => $nric, 'passportNo' => $passportNo, 'phoneNo' => $phoneNo, 'licenseType' => $licenseType, 'staffOrAgent' => $staffOrAgent,
                    'distName' => $distName, 'title' => $title
                ])
                ->returnResponseObject()
                ->post();

            $content = json_decode($response->content);

            if ($response->status != 200) {
                http_response_code(400);

                return response([
                    'message' => 'Failed to send email.',
                    'errorCode' => 4100
                ], 400);
            } else {

                return response([

                    'message' => 'Email notification has been sent to candidate',
                ]);
            }


            $response = Curl::to('http://192.168.3.24/api/module0/send_acceptance_email')
                ->withData([
                    'email' => $email, 'name' => $name, 'nric' => $nric, 'passportNo' => $passportNo, 'phoneNo' => $phoneNo, 'licenseType' => $licenseType, 'staffOrAgent' => $staffOrAgent,
                    'distName' => $distName, 'title' => $title
                ])
                ->returnResponseObject()
                ->post();

            $content = json_decode($response->content);

            if ($response->status != 200) {
                http_response_code(400);

                return response([
                    'message' => 'Failed to send email.',
                    'errorCode' => 4100
                ], 400);
            } else {

                return response([

                    'message' => 'Email notification has been sent to candidate',
                ]);
            }
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to send email',
                'errorCode' => 4102
            ], 400);
        }
    }
}
