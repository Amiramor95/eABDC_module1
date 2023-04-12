<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\DistributorTempRepresentative;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class DistributorTempRepresentativeController extends Controller
{
    public function get(Request $request)
    {
        try {
			$data = DistributorTempRepresentative::find($request->DISTRIBUTOR_TEMP_REPRESENTATIVE_ID); 

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
            $data = DistributorTempRepresentative::all();

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
			'DIST_TEMP_ID' => 'required|integer', 
			'REPR_TYPE' => 'required|integer', 
			'REPR_SALUTATION' => 'required|integer', 
			'REPR_FNAME' => 'required|string', 
			'REPR_MNAME' => 'required|string', 
			'REPR_LNAME' => 'required|string', 
			'REPR_POSITION' => 'required|integer', 
			'REPR_CITIZEN' => 'required|integer', 
			'REPR_NRIC' => 'required|string', 
			'REPR_PASS_NUM' => 'required|string', 
			'REPR_PASS_EXP' => 'required|string', 
			'REPR_TELEPHONE' => 'required|string', 
			'REPR_FAX' => 'required|string', 
			'REPR_EMAIL' => 'required|string', 
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
			'DIST_TEMP_ID' => 'required|integer', 
			'REPR_TYPE' => 'required|integer', 
			'REPR_SALUTATION' => 'required|integer', 
			'REPR_FNAME' => 'required|string', 
			'REPR_MNAME' => 'required|string', 
			'REPR_LNAME' => 'required|string', 
			'REPR_POSITION' => 'required|integer', 
			'REPR_CITIZEN' => 'required|integer', 
			'REPR_NRIC' => 'required|string', 
			'REPR_PASS_NUM' => 'required|string', 
			'REPR_PASS_EXP' => 'required|string', 
			'REPR_TELEPHONE' => 'required|string', 
			'REPR_FAX' => 'required|string', 
			'REPR_EMAIL' => 'required|string', 
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
			'DIST_TEMP_ID' => 'required|integer', 
			'REPR_TYPE' => 'required|integer', 
			'REPR_SALUTATION' => 'required|integer', 
			'REPR_FNAME' => 'required|string', 
			'REPR_MNAME' => 'required|string', 
			'REPR_LNAME' => 'required|string', 
			'REPR_POSITION' => 'required|integer', 
			'REPR_CITIZEN' => 'required|integer', 
			'REPR_NRIC' => 'required|string', 
			'REPR_PASS_NUM' => 'required|string', 
			'REPR_PASS_EXP' => 'required|string', 
			'REPR_TELEPHONE' => 'required|string', 
			'REPR_FAX' => 'required|string', 
			'REPR_EMAIL' => 'required|string', 
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
            $data = DistributorTempRepresentative::where('id',$id)->first();
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
            $data = DistributorTempRepresentative::find($id);
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
			'DIST_TEMP_ID' => 'required|integer', 
			'REPR_TYPE' => 'required|integer', 
			'REPR_SALUTATION' => 'required|integer', 
			'REPR_FNAME' => 'required|string', 
			'REPR_MNAME' => 'required|string', 
			'REPR_LNAME' => 'required|string', 
			'REPR_POSITION' => 'required|integer', 
			'REPR_CITIZEN' => 'required|integer', 
			'REPR_NRIC' => 'required|string', 
			'REPR_PASS_NUM' => 'required|string', 
			'REPR_PASS_EXP' => 'required|string', 
			'REPR_TELEPHONE' => 'required|string', 
			'REPR_FAX' => 'required|string', 
			'REPR_EMAIL' => 'required|string', 
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
