<?php

namespace App\Http\Controllers;

use App\Models\DistributorDetailInfo;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Validator;

class DistributorDetailInfoController extends Controller
{
    public function get(Request $request)
    {
        try {
			$data = DistributorDetailInfo::find($request->DISTRIBUTOR_DETAIL_INFO_ID); 

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
            $data = DistributorDetailInfo::all();

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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'string|nullable', 
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
            $data = new DistributorDetailInfo;
            $data->DIST_ID = $request->DIST_ID;
            $data->DIST_PAID_UP_CAPITAL = $request->DIST_PAID_UP_CAPITAL;
            $data->DIST_TYPE_STRUCTURE = $request->DIST_TYPE_STRUCTURE;
            $data->DIST_MARKETING_APPROACH = $request->DIST_MARKETING_APPROACH;
            $data->DIST_NUM_DIST_POINT = $request->DIST_NUM_DIST_POINT;
            $data->DIST_NUM_CONSULTANT = $request->DIST_NUM_CONSULTANT;
            $data->DIST_INSURANCE = $request->DIST_INSURANCE;
            $data->DIST_EXPIRED_DATE = $request->DIST_EXPIRED_DATE;
            $data->save();

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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'string|nullable', 
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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'string|nullable', 
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
            $data = DistributorDetailInfo::where('id',$id)->first();
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
            $data = DistributorDetailInfo::find($id);
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
			'DIST_PAID_UP_CAPITAL' => 'string|nullable', 
			'DIST_TYPE_STRUCTURE' => 'integer|nullable', 
			'DIST_MARKETING_APPROACH' => 'string|nullable', 
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
