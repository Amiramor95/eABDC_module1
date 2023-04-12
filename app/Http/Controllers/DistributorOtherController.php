<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Ixudra\Curl\Facades\Curl;
use Validator;
use DB;

class DistributorOtherController extends Controller
{
    public function getDistGroup()
    {
        DB::enableQueryLog();
        try {
            $data = DB::table('admin_management.DISTRIBUTOR_MANAGE_GROUP as DIST_GROUP')
                ->select('*')
                ->get();

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => ([
                    'data' => $data,
                ]),
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function getRunningNoFundCode(Request $request)
    {
        try {
            $data = DB::table('funds_management.FMS_RUNNO')
                ->select('*')
                ->where('type','FUND_CODE_FIMM')
                ->get();
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
}
