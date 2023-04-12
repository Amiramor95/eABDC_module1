<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\UserContact;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class UserContactController extends Controller
{
    public function get(Request $request)
    {
        try {
			$data = UserContact::find($request->USER_CONTACT_ID); 

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
            $data = UserContact::all();

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
			'USER_CONT_ID' => 'required|integer', 
			'USER_ID' => 'required|integer', 
			'USER_TELEPHONE_1' => 'required|string', 
			'USER_TELEPHONE_2' => 'required|string', 
			'USER_FAX_1' => 'required|string', 
			'USER_FAX_2' => 'required|string', 
			'USER_EMAIL_1' => 'required|string', 
			'USER_EMAIL_2' => 'required|string', 
			'CREATE_TIMESTAMP' => 'required|string' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
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
            ],400);
        }

    }

    public function manage(Request $request)
    {
$validator = Validator::make($request->all(), [ 
			'USER_CONT_ID' => 'required|integer', 
			'USER_ID' => 'required|integer', 
			'USER_TELEPHONE_1' => 'required|string', 
			'USER_TELEPHONE_2' => 'required|string', 
			'USER_FAX_1' => 'required|string', 
			'USER_FAX_2' => 'required|string', 
			'USER_EMAIL_1' => 'required|string', 
			'USER_EMAIL_2' => 'required|string', 
			'CREATE_TIMESTAMP' => 'required|string' 
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
			'USER_CONT_ID' => 'required|integer', 
			'USER_ID' => 'required|integer', 
			'USER_TELEPHONE_1' => 'required|string', 
			'USER_TELEPHONE_2' => 'required|string', 
			'USER_FAX_1' => 'required|string', 
			'USER_FAX_2' => 'required|string', 
			'USER_EMAIL_1' => 'required|string', 
			'USER_EMAIL_2' => 'required|string', 
			'CREATE_TIMESTAMP' => 'required|string' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $data = UserContact::where('id',$id)->first();
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
            $data = UserContact::find($id);
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
			'USER_CONT_ID' => 'required|integer', 
			'USER_ID' => 'required|integer', 
			'USER_TELEPHONE_1' => 'required|string', 
			'USER_TELEPHONE_2' => 'required|string', 
			'USER_FAX_1' => 'required|string', 
			'USER_FAX_2' => 'required|string', 
			'USER_EMAIL_1' => 'required|string', 
			'USER_EMAIL_2' => 'required|string', 
			'CREATE_TIMESTAMP' => 'required|string' 
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
