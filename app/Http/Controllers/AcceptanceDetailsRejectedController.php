<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\AcceptanceDetailsRejected;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class AcceptanceDetailsRejectedController extends Controller
{
    public function get(Request $request)
    {
        try {
			$data = AcceptanceDetailsRejected::find($request->ACCEPTANCE_DETAILS_REJECTED_ID); 

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
            $data = AcceptanceDetailsRejected::all();

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
			'CANDIDATE_ACCEPTANCE_ID' => 'integer|nullable', 
			'CANDIDATE_NAME' => 'string|nullable', 
			'CANDIDATE_NRIC' => 'string|nullable', 
			'CANDIDATE_PASSPORT_NO' => 'string|nullable', 
			'CANDIDATE_EMAIL' => 'string|nullable', 
			'CANDIDATE_PHONENO' => 'string|nullable', 
			'LICENSE_TYPE' => 'integer|nullable', 
			'STAFF_OR_AGENT' => 'integer|nullable', 
			'CA_CLASSIFICATION' => 'integer|nullable', 
			'TS_ID' => 'integer|nullable' 
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
			'CANDIDATE_ACCEPTANCE_ID' => 'integer|nullable', 
			'CANDIDATE_NAME' => 'string|nullable', 
			'CANDIDATE_NRIC' => 'string|nullable', 
			'CANDIDATE_PASSPORT_NO' => 'string|nullable', 
			'CANDIDATE_EMAIL' => 'string|nullable', 
			'CANDIDATE_PHONENO' => 'string|nullable', 
			'LICENSE_TYPE' => 'integer|nullable', 
			'STAFF_OR_AGENT' => 'integer|nullable', 
			'CA_CLASSIFICATION' => 'integer|nullable', 
			'TS_ID' => 'integer|nullable' 
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
			'CANDIDATE_ACCEPTANCE_ID' => 'integer|nullable', 
			'CANDIDATE_NAME' => 'string|nullable', 
			'CANDIDATE_NRIC' => 'string|nullable', 
			'CANDIDATE_PASSPORT_NO' => 'string|nullable', 
			'CANDIDATE_EMAIL' => 'string|nullable', 
			'CANDIDATE_PHONENO' => 'string|nullable', 
			'LICENSE_TYPE' => 'integer|nullable', 
			'STAFF_OR_AGENT' => 'integer|nullable', 
			'CA_CLASSIFICATION' => 'integer|nullable', 
			'TS_ID' => 'integer|nullable' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $data = AcceptanceDetailsRejected::where('id',$id)->first();
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
            $data = AcceptanceDetailsRejected::find($id);
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
			'CANDIDATE_ACCEPTANCE_ID' => 'integer|nullable', 
			'CANDIDATE_NAME' => 'string|nullable', 
			'CANDIDATE_NRIC' => 'string|nullable', 
			'CANDIDATE_PASSPORT_NO' => 'string|nullable', 
			'CANDIDATE_EMAIL' => 'string|nullable', 
			'CANDIDATE_PHONENO' => 'string|nullable', 
			'LICENSE_TYPE' => 'integer|nullable', 
			'STAFF_OR_AGENT' => 'integer|nullable', 
			'CA_CLASSIFICATION' => 'integer|nullable', 
			'TS_ID' => 'integer|nullable' 
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
