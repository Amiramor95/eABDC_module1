<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\UserRegistrationApproval;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Illuminate\Support\Facades\Log;

class UserRegistrationApprovalController extends Controller
{
    public function get(Request $request)
    {
        try {
            $data = UserRegistrationApproval::find($request->USER_REGISTRATION_APPROVAL_ID);

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
    public function getUserRegList(Request $request)
    {
        try {
            $data = DB::table('distributor_management.USER_REGISTRATION_APPROVAL AS REGAPPR')
           // ->leftJoin('distributor_management.USER_REGISTRATION AS USERREG', 'REGAPPR.USER_REGI_ID', '=', 'USERREG.USER_REGI_ID')
            ->leftJoin('distributor_management.USER_ADDRESS AS USERADDR', 'REGAPPR.USER_ID', '=', 'USERADDR.USER_ID')
            ->leftJoin('distributor_management.USER AS USER', 'USER.USER_ID', '=', 'REGAPPR.USER_ID')
            ->leftJoin('admin_management.DISTRIBUTOR_MANAGE_GROUP as GROUPUSER', 'GROUPUSER.DISTRIBUTOR_MANAGE_GROUP_ID', '=', 'USER.USER_GROUP')
            ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', '=', 'REGAPPR.APPR_STATUS')
            ->select('*')
            ->where('REGAPPR.USER_DIST_ID', $request->USER_DIST_ID)
            ->get();
            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $data
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103
            ], 400);
        }
    }
    public function getUserRegListStatus(Request $request)
    {
        try {
            $data = DB::table('distributor_management.USER AS USER')
            ->select('USER.*','REGAPPR.*','USERADDR.*','SG.SET_PARAM AS COUNTRYNAME','SGSTATE.SET_PARAM AS STATENAME','SETTING_CITY.SET_CITY_NAME AS SET_CITY_NAME','SETTING_POSTAL.POSTCODE_NO','TS.TS_PARAM AS TS_PARAM','DISTRIBUTION_POINT.DIST_POINT_NAME AS DIST_POINT_NAME','DISTRIBUTOR.DIST_NAME AS DIST_NAME','GROUPUSER.DISTRIBUTOR_MANAGE_GROUP_NAME AS DISTRIBUTOR_MANAGE_GROUP_NAME')
           ->leftJoin('distributor_management.USER_REGISTRATION_APPROVAL AS REGAPPR', 'REGAPPR.USER_ID', '=', 'USER.USER_ID')
           ->leftJoin('distributor_management.USER_ADDRESS AS USERADDR', 'USER.USER_ID', '=', 'USERADDR.USER_ID')
           ->leftJoin('admin_management.DISTRIBUTOR_MANAGE_GROUP as GROUPUSER', 'GROUPUSER.DISTRIBUTOR_MANAGE_GROUP_ID', '=', 'USER.USER_GROUP')
           ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', '=', 'REGAPPR.APPR_STATUS')
           ->leftJoin('distributor_management.DISTRIBUTOR AS DISTRIBUTOR', 'DISTRIBUTOR.DISTRIBUTOR_ID', '=', 'USER.USER_DIST_ID')
           ->leftJoin('distributor_management.DISTRIBUTION_POINT AS DISTRIBUTION_POINT', 'DISTRIBUTION_POINT.DISTRIBUTOR_ID', '=', 'USER.USER_DIST_ID')
           ->leftJoin('admin_management.SETTING_GENERAL as SG', 'SG.SETTING_GENERAL_ID', '=', 'USERADDR.USER_ADDR_COUNTRY')
           ->leftJoin('admin_management.SETTING_GENERAL as SGSTATE', 'SGSTATE.SETTING_GENERAL_ID', '=', 'USERADDR.USER_ADDR_STATE')
           ->leftJoin('admin_management.SETTING_CITY as SETTING_CITY', 'SETTING_CITY.SETTING_CITY_ID', '=', 'USERADDR.USER_ADDR_CITY')
           ->leftJoin('admin_management.SETTING_POSTAL as SETTING_POSTAL', 'SETTING_POSTAL.SETTING_POSTCODE_ID', '=', 'USERADDR.USER_ADDR_POSTAL')
           ->where('USER.USER_ID', $request->USER_ID)
           ->first();
            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $data
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103
            ], 400);
        }
    }
    public function getUserRegListStatusDoc(Request $request)
    {
        try {
            $data = DB::table('distributor_management.USER AS USER')
            ->select('*')
           ->leftJoin('distributor_management.USER_REGISTRATION_DOCUMENT AS USER_REGISTRATION_DOCUMENT', 'USER_REGISTRATION_DOCUMENT.USER_ID', '=', 'USER.USER_ID')
           ->where('USER.USER_ID', $request->USER_ID)
           ->get();

           

           $resp = [];
            foreach($data as  $d){
               // Log::info( "d ===>" , $d);
               $resp['USER_REGI_DOCU_ID'] = $d->USER_REGI_DOCU_ID;
                $resp['PHOTO_FILENAME'] = $d->PHOTO_FILENAME;
                $resp['PHOTO_FILEEXTENSION'] = $d->PHOTO_FILEEXTENSION;
                $resp['PHOTO_FILESIZE'] = $d->PHOTO_FILESIZE;
                $resp['PHOTO_BLOB'] = base64_encode($d->PHOTO_BLOB);
                $resp['PHOTO_MIMETYPE'] = $d->PHOTO_MIMETYPE;
                $resp['PHOTO_IMAGE'] = "data:".$d->PHOTO_MIMETYPE.";base64,".base64_encode($d->PHOTO_BLOB);
                $resp['DOC_FILENAME'] = $d->DOC_FILENAME;
                $resp['DOC_FILEEXTENSION'] = $d->DOC_FILEEXTENSION;
                $resp['DOC_FILESIZE'] = $d->DOC_FILESIZE;
                $resp['DOC_BLOB'] = base64_encode($d->DOC_BLOB);
                $resp['DOC_MIMETYPE'] = $d->DOC_MIMETYPE;
            }

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $resp
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103
            ], 400);
        }
    }
    public function updateApproval(Request $request)
    {
        try {
            $data = UserRegistrationApproval::where('USER_REGI_APPR_ID', $request->USER_REGI_APPR_ID)->first();
            $data->APPR_REMARK = $request->APPR_REMARK;
            $data->APPR_STATUS = $request->APPR_STATUS;
            $data->APPR_PUBLISH_STATUS = 1;
            $data->APPR_GROUP_ID = $request->APPR_GROUP_ID;
            // $data->APPROVAL_LEVEL_ID = $request->APPROVAL_LEVEL_ID;
            $data->save();

            if ($request->APPR_STATUS == 3) {
                $userUpdate = User::where('USER_ID', $data->USER_ID)->first();
                $userUpdate->USER_GROUP = $request->DISTRIBUTOR_MANAGE_GROUP_ID;
                $userUpdate-> save();
            }

            http_response_code(200);
            return response([
                'message' => 'Data successfully updated.',

            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to updated.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function getAll()
    {
        try {
            $data = UserRegistrationApproval::all();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $data
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'USER_REGI_ID' => 'required|integer',
            'USER_ID' => 'required|integer',
            'APPR_REMARK' => 'required|string',
            'APPR_STATUS' => 'required|string',
            'CREATE_TIMESTAMP' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            //create function

            http_response_code(200);
            return response([
                'message' => 'Data successfully updated.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4100
            ], 400);
        }
    }

    public function manage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'USER_REGI_ID' => 'required|integer',
            'USER_ID' => 'required|integer',
            'APPR_REMARK' => 'required|string',
            'APPR_STATUS' => 'required|string',
            'CREATE_TIMESTAMP' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            //manage function

            http_response_code(200);
            return response([
                'message' => ''
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => '',
                'errorCode' => 4104
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'USER_REGI_ID' => 'required|integer',
            'USER_ID' => 'required|integer',
            'APPR_REMARK' => 'required|string',
            'APPR_STATUS' => 'required|string',
            'CREATE_TIMESTAMP' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            $data = UserRegistrationApproval::where('id', $id)->first();
            $data->TEST = $request->TEST; //nama column
            $data->save();

            http_response_code(200);
            return response([
                'message' => ''
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4101
            ], 400);
        }
    }

    public function delete($id)
    {
        try {
            $data = UserRegistrationApproval::find($id);
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

    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'USER_REGI_ID' => 'required|integer',
            'USER_ID' => 'required|integer',
            'APPR_REMARK' => 'required|string',
            'APPR_STATUS' => 'required|string',
            'CREATE_TIMESTAMP' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            //manage function

            http_response_code(200);
            return response([
                'message' => 'Filtered data successfully retrieved.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Filtered data failed to be retrieved.',
                'errorCode' => 4105
            ], 400);
        }
    }
}
