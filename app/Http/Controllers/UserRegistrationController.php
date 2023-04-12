<?php

namespace App\Http\Controllers;

use App\Models\UserRegistration;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Validator;

class UserRegistrationController extends Controller
{
    public function checkEmailAndPassword(Request $request)
    {
        try {
            $data = UserRegistration::where('USER_REGI_EMAIL',$request->USER_REGI_EMAIL)
            ->where('USER_REGI_PASSWORD', $request->USER_REGI_PASSWORD)
            ->get();

            http_response_code(200);
            return response([
                'message' => 'This user ID already exists in our record',
            ]);

        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ],400);
        }
    }
    public function checkDuplicateUserID(Request $request)
    {
        try {
            $data = UserRegistration::where('USER_REGI_USERID',$request->USER_REGI_USERID)->get();

            if(count($data) != 0){
                http_response_code(200);
                return response([
                    'message' => 'This user ID already exists in our record',
                ]);
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ],400);
        }
    }
    public function checkDuplicateIC(Request $request)
    {
        try {
            $data = UserRegistration::where('USER_REGI_NRIC',$request->USER_REGI_NRIC)->get();

            if(count($data) != 0){
                http_response_code(200);
                return response([
                    'message' => 'This NRIC number already exists in our record',
                ]);
            }


        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ],400);
        }
    }
    public function checkDuplicateEmail(Request $request)
    {
        try {
            $data = UserRegistration::where('USER_REGI_EMAIL',$request->USER_REGI_EMAIL)->get();

            if(count($data) != 0){
                http_response_code(200);
                return response([
                    'message' => 'This email already exists in our record',
                ]);
            }


        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ],400);
        }
    }
    public function get(Request $request)
    {
        try {
			$data = UserRegistration::find($request->USER_REGISTRATION_ID);

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

    public function getAll()
    {
        try {
            $data = UserRegistration::all();

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
            ],400);
        }
    }

    public function create(Request $request)
    {
    $validator = Validator::make($request->all(), [
			'USER_REGI_NAME' => 'string|nullable',
			'USER_REGI_CITIZEN' => 'required|integer',
			'USER_REGI_NRIC' => 'string|nullable',
			'USER_REGI_PASS_NUM' => 'string|nullable',
			'USER_REGI_PASS_EXP' => 'string|nullable',
			'USER_REGI_EMAIL' => 'string|nullable',
			'USER_REGI_TELEPHONE' => 'string|nullable',
			'DIST_TYPE' => 'integer|nullable',
			'DIST_NAME' => 'integer|nullable',
			'DIST_BRANCH' => 'integer|nullable',
			'CREATE_TIMESTAMP' => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $email = $request->USER_REGI_EMAIL;
            $name = $request->USER_REGI_USERID;
            $response = Curl::to('http://fimmserv_module0/api/module0/send_email')
                ->withData(['email'=> $email, 'name'=> $name])
                ->returnResponseObject()
                ->post();

            $content = json_decode($response->content);

            if($response->status != 200){
                http_response_code(400);
                return response([
                    'message' => 'Failed to send email.',
                    'errorCode' => 4100
                ],400);
            }

            $userRegistration = new UserRegistration;
            $userRegistration->USER_REGI_USERID = $request->USER_REGI_USERID;
            $userRegistration->USER_REGI_CITIZEN = $request->USER_REGI_CITIZEN;
            $userRegistration->USER_REGI_NRIC = $request->USER_REGI_NRIC;
            $userRegistration->USER_REGI_PASS_NUM = $request->USER_REGI_PASS_NUM;
            $userRegistration->USER_REGI_PASS_EXP = $request->USER_REGI_PASS_EXP;
            $userRegistration->USER_REGI_EMAIL = $request->USER_REGI_EMAIL;
            $userRegistration->USER_REGI_PASSWORD = $content->data;
            $userRegistration->save();

            http_response_code(200);
            return response([
                'message' => 'Data successfully created.'
            ]);

        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be created.',
                'errorCode' => 4100
            ],400);
        }

    }

    public function manage(Request $request)
    {
    $validator = Validator::make($request->all(), [
			'USER_REGI_NAME' => 'required|string',
			'USER_REGI_CITIZEN' => 'required|integer',
			'USER_REGI_NRIC' => 'string|nullable',
			'USER_REGI_PASS_NUM' => 'string|nullable',
			'USER_REGI_PASS_EXP' => 'string|nullable',
			'USER_REGI_EMAIL' => 'string|nullable',
			'USER_REGI_TELEPHONE' => 'string|nullable',
			'DIST_TYPE' => 'integer|nullable',
			'DIST_NAME' => 'integer|nullable',
			'DIST_BRANCH' => 'integer|nullable',
			'USER_REGI_ADDR_1' => 'string|nullable',
			'USER_REGI_ADDR_2' => 'string|nullable',
			'USER_REGI_ADDR_3' => 'string|nullable',
			'USER_REGI_POSTAL' => 'integer|nullable',
			'USER_REGI_DIVISION' => 'integer|nullable',
			'USER_REGI_DEPARTMENT' => 'integer|nullable',
			'USER_REGI_GROUP' => 'integer|nullable',
			'USER_REGI_PASSWORD' => 'string|nullable',
			'USER_REGI_SECURITY_QUESTION' => 'integer|nullable',
			'USER_REGI_SECURITY_ANSWER' => 'string|nullable',
			'USER_REGI_USERNAME' => 'string|nullable',
			'USER_REGI_DIST_ID' => 'integer|nullable',
			'CREATE_TIMESTAMP' => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
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
            ],400);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
			'USER_REGI_NAME' => 'required|string',
			'USER_REGI_CITIZEN' => 'required|integer',
			'USER_REGI_NRIC' => 'string|nullable',
			'USER_REGI_PASS_NUM' => 'string|nullable',
			'USER_REGI_PASS_EXP' => 'string|nullable',
			'USER_REGI_EMAIL' => 'string|nullable',
			'USER_REGI_TELEPHONE' => 'string|nullable',
			'DIST_TYPE' => 'integer|nullable',
			'DIST_NAME' => 'integer|nullable',
			'DIST_BRANCH' => 'integer|nullable',
			'USER_REGI_ADDR_1' => 'string|nullable',
			'USER_REGI_ADDR_2' => 'string|nullable',
			'USER_REGI_ADDR_3' => 'string|nullable',
			'USER_REGI_POSTAL' => 'integer|nullable',
			'USER_REGI_DIVISION' => 'integer|nullable',
			'USER_REGI_DEPARTMENT' => 'integer|nullable',
			'USER_REGI_GROUP' => 'integer|nullable',
			'USER_REGI_PASSWORD' => 'string|nullable',
			'USER_REGI_SECURITY_QUESTION' => 'integer|nullable',
			'USER_REGI_SECURITY_ANSWER' => 'string|nullable',
			'USER_REGI_USERNAME' => 'string|nullable',
			'USER_REGI_DIST_ID' => 'integer|nullable',
			'CREATE_TIMESTAMP' => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $data = UserRegistration::where('id',$id)->first();
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
            ],400);
        }
    }

    public function delete($id)
    {
        try {
            $data = UserRegistration::find($id);
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
            ],400);
        }
    }

    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'USER_REGI_NAME' => 'required|string',
			'USER_REGI_CITIZEN' => 'required|integer',
			'USER_REGI_NRIC' => 'string|nullable',
			'USER_REGI_PASS_NUM' => 'string|nullable',
			'USER_REGI_PASS_EXP' => 'string|nullable',
			'USER_REGI_EMAIL' => 'string|nullable',
			'USER_REGI_TELEPHONE' => 'string|nullable',
			'DIST_TYPE' => 'integer|nullable',
			'DIST_NAME' => 'integer|nullable',
			'DIST_BRANCH' => 'integer|nullable',
			'USER_REGI_ADDR_1' => 'string|nullable',
			'USER_REGI_ADDR_2' => 'string|nullable',
			'USER_REGI_ADDR_3' => 'string|nullable',
			'USER_REGI_POSTAL' => 'integer|nullable',
			'USER_REGI_DIVISION' => 'integer|nullable',
			'USER_REGI_DEPARTMENT' => 'integer|nullable',
			'USER_REGI_GROUP' => 'integer|nullable',
			'USER_REGI_PASSWORD' => 'string|nullable',
			'USER_REGI_SECURITY_QUESTION' => 'integer|nullable',
			'USER_REGI_SECURITY_ANSWER' => 'string|nullable',
			'USER_REGI_USERNAME' => 'string|nullable',
			'USER_REGI_DIST_ID' => 'integer|nullable',
			'CREATE_TIMESTAMP' => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
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
            ],400);
        }
    }
}
