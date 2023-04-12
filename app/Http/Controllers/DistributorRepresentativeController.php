<?php

namespace App\Http\Controllers;

use App\Models\DistributorRepresentative;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Validator;

class DistributorRepresentativeController extends Controller
{
    public function get(Request $request)
    {
        try {
			$data = DistributorRepresentative::find($request->DISTRIBUTOR_REPRESENTATIVE_ID); 

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
            $data = DistributorRepresentative::all();

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
			'DIST_ID' => 'required|integer', 
			'REPR_TYPE' => 'string|nullable', 
			'REPR_SALUTATION' => 'string|nullable', 
			'REPR_NAME' => 'string|nullable', 
			'REPR_POSITION' => 'string|nullable', 
			'REPR_CITIZEN' => 'integer|nullable', 
			'REPR_NRIC' => 'string|nullable', 
			'REPR_PASS_NUM' => 'string|nullable', 
			'REPR_PASS_EXP' => 'string|nullable', 
			'REPR_TELEPHONE' => 'string|nullable', 
			'REPR_PHONE_EXTENSION' => 'integer|nullable', 
			'REPR_MOBILE_NUMBER' => 'string|nullable', 
			'REPR_EMAIL' => 'string|nullable' 
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
			'DIST_ID' => 'required|integer', 
			'REPR_TYPE' => 'string|nullable', 
			'REPR_SALUTATION' => 'string|nullable', 
			'REPR_NAME' => 'string|nullable', 
			'REPR_POSITION' => 'string|nullable', 
			'REPR_CITIZEN' => 'integer|nullable', 
			'REPR_NRIC' => 'string|nullable', 
			'REPR_PASS_NUM' => 'string|nullable', 
			'REPR_PASS_EXP' => 'string|nullable', 
			'REPR_TELEPHONE' => 'string|nullable', 
			'REPR_PHONE_EXTENSION' => 'integer|nullable', 
			'REPR_MOBILE_NUMBER' => 'string|nullable', 
			'REPR_EMAIL' => 'string|nullable' 
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
			'DIST_ID' => 'required|integer', 
			'REPR_TYPE' => 'string|nullable', 
			'REPR_SALUTATION' => 'string|nullable', 
			'REPR_NAME' => 'string|nullable', 
			'REPR_POSITION' => 'string|nullable', 
			'REPR_CITIZEN' => 'integer|nullable', 
			'REPR_NRIC' => 'string|nullable', 
			'REPR_PASS_NUM' => 'string|nullable', 
			'REPR_PASS_EXP' => 'string|nullable', 
			'REPR_TELEPHONE' => 'string|nullable', 
			'REPR_PHONE_EXTENSION' => 'integer|nullable', 
			'REPR_MOBILE_NUMBER' => 'string|nullable', 
			'REPR_EMAIL' => 'string|nullable' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $data = DistributorRepresentative::where('id',$id)->first();
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
            $data = DistributorRepresentative::find($id);
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
			'DIST_ID' => 'required|integer', 
			'REPR_TYPE' => 'string|nullable', 
			'REPR_SALUTATION' => 'string|nullable', 
			'REPR_NAME' => 'string|nullable', 
			'REPR_POSITION' => 'string|nullable', 
			'REPR_CITIZEN' => 'integer|nullable', 
			'REPR_NRIC' => 'string|nullable', 
			'REPR_PASS_NUM' => 'string|nullable', 
			'REPR_PASS_EXP' => 'string|nullable', 
			'REPR_TELEPHONE' => 'string|nullable', 
			'REPR_PHONE_EXTENSION' => 'integer|nullable', 
			'REPR_MOBILE_NUMBER' => 'string|nullable', 
			'REPR_EMAIL' => 'string|nullable' 
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
