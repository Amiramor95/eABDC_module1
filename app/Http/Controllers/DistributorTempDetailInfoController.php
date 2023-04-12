<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\DistributorTempDetailInfo;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class DistributorTempDetailInfoController extends Controller
{
    public function get(Request $request)
    {
        try {
			$data = DistributorTempDetailInfo::find($request->DISTRIBUTOR_TEMP_DETAIL_INFO_ID); 

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
            $data = DistributorTempDetailInfo::all();

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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'integer|nullable', 
			'DIST_NUM_DIST_POINT' => 'integer|nullable', 
			'DIST_NUM_CONSULTANT' => 'integer|nullable', 
			'DIST_INSURANCE' => 'string|nullable', 
			'DIST_EXPIRED_DATE' => 'string|nullable' 
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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'integer|nullable', 
			'DIST_NUM_DIST_POINT' => 'integer|nullable', 
			'DIST_NUM_CONSULTANT' => 'integer|nullable', 
			'DIST_INSURANCE' => 'string|nullable', 
			'DIST_EXPIRED_DATE' => 'string|nullable' 
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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'integer|nullable', 
			'DIST_NUM_DIST_POINT' => 'integer|nullable', 
			'DIST_NUM_CONSULTANT' => 'integer|nullable', 
			'DIST_INSURANCE' => 'string|nullable', 
			'DIST_EXPIRED_DATE' => 'string|nullable' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $data = DistributorTempDetailInfo::where('id',$id)->first();
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
            $data = DistributorTempDetailInfo::find($id);
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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'integer|nullable', 
			'DIST_NUM_DIST_POINT' => 'integer|nullable', 
			'DIST_NUM_CONSULTANT' => 'integer|nullable', 
			'DIST_INSURANCE' => 'string|nullable', 
			'DIST_EXPIRED_DATE' => 'string|nullable' 
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
