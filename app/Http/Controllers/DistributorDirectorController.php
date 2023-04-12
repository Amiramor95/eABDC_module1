<?php

namespace App\Http\Controllers;

use App\Models\DistributorDirector;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Validator;

class DistributorDirectorController extends Controller
{
    public function get(Request $request)
    {
        try {
			$data = DistributorDirector::find($request->DISTRIBUTOR_DIRECTOR_ID); 

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
            $data = DistributorDirector::all();

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
			'DIR_NAME' => 'string|nullable', 
			'DIR_NRIC' => 'string|nullable', 
			'DIR_DATE_EFFECTIVE' => 'string|nullable', 
			'DIR_DATE_END' => 'string|nullable', 
			'CREATE_TIMESTAMP' => 'string|nullable' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $dataDir= new DistributorDirector;
            $dataDir->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataDir->DIR_SALUTATION = $item->DIR_SALUTATION;
            $dataDir->DIR_NAME = $item->DIR_NAME_DB;
            $dataDir->DIR_NRIC = str_replace("-","",$item->DIR_NRIC);
            $dataDir->DIR_DATE_EFFECTIVE = $item->DIR_DATE_EFFECTIVE_DB;
            $dataDir->DIR_DATE_END = $item->DIR_DATE_END_DB;
            $dataDir->save();

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
			'DIR_NAME' => 'string|nullable', 
			'DIR_NRIC' => 'string|nullable', 
			'DIR_DATE_EFFECTIVE' => 'string|nullable', 
			'DIR_DATE_END' => 'string|nullable', 
			'CREATE_TIMESTAMP' => 'string|nullable' 
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
			'DIR_NAME' => 'string|nullable', 
			'DIR_NRIC' => 'string|nullable', 
			'DIR_DATE_EFFECTIVE' => 'string|nullable', 
			'DIR_DATE_END' => 'string|nullable', 
			'CREATE_TIMESTAMP' => 'string|nullable' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $data = DistributorDirector::where('id',$id)->first();
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

    public function delete(Request $request)
    {
        try {
            $data = DistributorDirector::find($request->DIST_DIR_ID);
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
			'DIR_NAME' => 'string|nullable', 
			'DIR_NRIC' => 'string|nullable', 
			'DIR_DATE_EFFECTIVE' => 'string|nullable', 
			'DIR_DATE_END' => 'string|nullable', 
			'CREATE_TIMESTAMP' => 'string|nullable' 
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
