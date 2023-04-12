<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\DistRunno;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class DistRunnoController extends Controller
{
    public function updateRunNo (Request $request)
    {
        try {

            if ($request->DISTRIBUTOR_ID != null){

            $runNo = DB::table('distributor_management.DIST_RUNNO AS RUN')
            ->where('RUN.DISTRIBUTOR_ID',$request->DISTRIBUTOR_ID)
            ->first();
             
                    if($runNo == null){

                            $distCode = DB::table('distributor_management.DISTRIBUTOR AS DIST')
                            ->where('DIST.DISTRIBUTOR_ID',$request->DISTRIBUTOR_ID)
                            ->first();

                            $disRun = new DistRunno;
                            $disRun->DISTRIBUTOR_ID = $request->DISTRIBUTOR_ID;
                            $disRun->DISTRIBUTOR_CODE = $distCode->DIST_CODE;
                            $disRun->save();
                    }
            }

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' =>  $runNo,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }
}
