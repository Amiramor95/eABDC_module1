<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\DistributionPoint;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class DistributionPointController extends Controller
{
    public function getDistPointByDistID(Request $request)
    {
        try {
			$data = DB::table('distributor_management.DISTRIBUTION_POINT AS DP')
            ->select('DP.DIST_POINT_ID','DP.CREATE_TIMESTAMP','DP.DIST_POINT_CODE','DP.DIST_POINT_NAME','DP.PHONE_NUMBER',
            'DP.DIST_COUNTRY','DP.DIST_STATE','DP.DIST_CITY','DP.DIST_POSTAL','DP.OTHER_STATE','DP.OTHER_CITY','DP.OTHER_POSTAL',
            'DP.DIST_ADDR_1','DP.DIST_ADDR_2','DP.DIST_ADDR_3',
            'DP.CREATE_BY','USR.USER_NAME','TS.TS_PARAM','COUNTRYNAME.SET_PARAM AS COUNTRY_NAME','COUNTRYNAME.SET_CODE AS SET_CODE',
            'STATENAME.SET_PARAM AS STATE_NAME','CITYNAME.SET_CITY_NAME','POSTAL.POSTCODE_NO')
            ->leftJoin('distributor_management.USER AS USR','USR.USER_ID','=','DP.CREATE_BY')
            ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID','=','DP.TS_ID')
            ->leftJoin('admin_management.SETTING_GENERAL AS COUNTRYNAME','COUNTRYNAME.SETTING_GENERAL_ID','=','DP.DIST_COUNTRY')
            ->leftJoin('admin_management.SETTING_GENERAL AS STATENAME','STATENAME.SETTING_GENERAL_ID','=','DP.DIST_STATE')
            ->leftJoin('admin_management.SETTING_CITY AS CITYNAME','CITYNAME.SETTING_CITY_ID','=','DP.DIST_CITY')
            ->leftJoin('admin_management.SETTING_POSTAL AS POSTAL','POSTAL.SETTING_POSTCODE_ID','=','DP.DIST_POSTAL')

            ->where('DP.DISTRIBUTOR_ID',$request->DISTRIBUTOR_ID)
            ->get();

            foreach($data as $item){

                $item->CREATE_TIMESTAMP =  $item->CREATE_TIMESTAMP ?? '-';
                $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));

                if ($item->PHONE_NUMBER != null) {
                    $item->PHONE_NUMBER = substr($item->PHONE_NUMBER, 0, 2).'-'.substr($item->PHONE_NUMBER, 2,8);
                }else
                {
                    $item->PHONE_NUMBER  =   $item->PHONE_NUMBER ?? '-';
                }

                if($item->DIST_ADDR_2 != null){
                    $item->DIST_ADDR_2 = $item->DIST_ADDR_2." " ;
                }else{
                    $item->DIST_ADDR_2 = '';
                }

                if($item->DIST_ADDR_3 != null){
                    $item->DIST_ADDR_3 = $item->DIST_ADDR_3." " ;
                }else{
                    $item->DIST_ADDR_3 = '';
                }
             }

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

    public function getRecordByID(Request $request)
    {
        try {
			$data = DB::table('distributor_management.DISTRIBUTION_POINT AS DP')
            ->select('DP.DIST_POINT_ID','DP.CREATE_TIMESTAMP','DP.DIST_POINT_CODE','DP.DIST_POINT_NAME','DP.PHONE_NUMBER',
            'DP.DIST_COUNTRY','DP.DIST_STATE','DP.DIST_CITY','DP.DIST_POSTAL','DP.OTHER_STATE','DP.OTHER_CITY','DP.OTHER_POSTAL',
            'DP.DIST_ADDR_1','DP.DIST_ADDR_2','DP.DIST_ADDR_3',
            'DP.CREATE_BY','USR.USER_NAME','TS.TS_PARAM')
            ->leftJoin('distributor_management.USER AS USR','USR.USER_ID','=','DP.CREATE_BY')
            ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID','=','DP.TS_ID')
            ->where('DP.DIST_POINT_ID',$request->DIST_POINT_ID)
            ->get();

            foreach($data as $item){

                $item->CREATE_TIMESTAMP =  $item->CREATE_TIMESTAMP ?? '-';
                $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));

                if ($item->PHONE_NUMBER != null) {
                    $item->PHONE_NUMBER = substr($item->PHONE_NUMBER, 0, 2).'-'.substr($item->PHONE_NUMBER, 2,8);
                }else
                {
                    $item->PHONE_NUMBER  =   $item->PHONE_NUMBER ?? '-';
                }
             }

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

    public function getStatus()
    {
        try {
            $status = DB::table('admin_management.TASK_STATUS')
            ->select('TS_ID','TS_PARAM')
            ->where('TS_CODE','=','GENERAL')
            ->orderBy('TS_INDEX','ASC')
            ->get();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $status
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.', 
                'errorCode' => 4103
            ],400);
        }
    }

    public function getAllCountry()
    {
        try {
            $country = DB::table('admin_management.SETTING_GENERAL')
            ->select('SETTING_GENERAL_ID','SET_PARAM','SET_CODE')
            ->where('SET_TYPE','=','COUNTRY')
            ->get();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $country
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.', 
                'errorCode' => 4103
            ],400);
        }
    }

    public function getCountryById(Request $request)
    {
        try {
            $country = DB::table('admin_management.SETTING_GENERAL')
            ->select('SET_CODE')
            ->where('SETTING_GENERAL_ID','=',$request->SETTING_GENERAL_ID)
            ->first();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $country
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.', 
                'errorCode' => 4103
            ],400);
        }
    }

    public function getStateByID(Request $request)
    {
        try {
            $state = DB::table('admin_management.SETTING_GENERAL')
            ->select('SETTING_GENERAL_ID','SET_PARAM','SET_CODE')
            ->where('SET_TYPE','=','STATE')
            ->where('SET_VALUE','=',$request->SET_VALUE)
            ->get();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $state
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.', 
                'errorCode' => 4103
            ],400);
        }
    }

    public function getCityByID(Request $request)
    {
        try {
            $city = DB::table('admin_management.SETTING_CITY')
            ->select('SETTING_CITY_ID','SET_CITY_NAME')
            ->where('SETTING_GENERAL_ID','=',$request->SETTING_GENERAL_ID)
            ->get();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $city
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.', 
                'errorCode' => 4103
            ],400);
        }
    }

    public function getPostcodeByID(Request $request)
    {
        try {
            $postcode = DB::table('admin_management.SETTING_POSTAL')
            ->select('SETTING_POSTCODE_ID','POSTCODE_NO')
            ->where('SETTING_CITY_ID','=',$request->SETTING_CITY_ID)
            ->get();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $postcode
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.', 
                'errorCode' => 4103
            ],400);
        }
    }

    public function createDistributorPoint(Request $request)
    {
        try{
        
            $dp = new DistributionPoint;
            $dp->DISTRIBUTOR_ID = $request->DISTRIBUTOR_ID;
            $dp->DIST_POINT_CODE = $request->DP_CODE;
            $dp->DIST_POINT_NAME = $request->DP_NAME;
            $dp->PHONE_NUMBER = $request->DP_PHONENO;

            $dp->DIST_COUNTRY = $request->DIST_COUNTRY;
            $dp->DIST_STATE = $request->DIST_STATE;
            $dp->DIST_CITY = $request->DIST_CITY;
            $dp->DIST_POSTAL = $request->DIST_POSTAL;
            $dp->OTHER_STATE = $request->OTHER_STATE;
            $dp->OTHER_CITY = $request->OTHER_CITY;
            $dp->OTHER_POSTAL = $request->OTHER_POSTAL;

            $dp->DIST_ADDR_1 = $request->DIST_ADDR1 ;
            $dp->DIST_ADDR_2 = $request->DIST_ADDR2 ;
            $dp->DIST_ADDR_3 = $request->DIST_ADDR3 ;

            $dp->TS_ID = $request->TS_ID;
            $dp->CREATE_BY = $request->CREATE_BY ;
            $dp->save();
            

            http_response_code(200);
            return response([
                'message' => 'Data successfully save'
            ]);
            } catch (RequestException $r) {

                http_response_code(400);
                return response([
                    'message' => $r,
                    'errorCode' => 4103,
                ], 400);
            }

        
    }

    public function updateDistributorPoint(Request $request)
    {
        try{

            $phone = str_replace("-", "", $request->DP_PHONENO);
            
            $dp = DistributionPoint::find($request->DIST_POINT_ID);
            $dp->DIST_POINT_CODE = $request->DP_CODE;
            $dp->DIST_POINT_NAME = $request->DP_NAME;
            $dp->PHONE_NUMBER = $phone;

            $dp->DIST_COUNTRY = $request->DIST_COUNTRY;
            $dp->DIST_STATE = $request->DIST_STATE;
            $dp->DIST_CITY = $request->DIST_CITY;
            $dp->DIST_POSTAL = $request->DIST_POSTAL;
            $dp->OTHER_STATE = $request->OTHER_STATE;
            $dp->OTHER_CITY = $request->OTHER_CITY;
            $dp->OTHER_POSTAL = $request->OTHER_POSTAL;

            $dp->DIST_ADDR_1 = $request->DIST_ADDR1 ;
            $dp->DIST_ADDR_2 = $request->DIST_ADDR2 ;
            $dp->DIST_ADDR_3 = $request->DIST_ADDR3 ;

            $dp->TS_ID = $request->TS_ID;
            $dp->CREATE_BY = $request->CREATE_BY ;
            $dp->save();
            

            http_response_code(200);
            return response([
                'message' => 'Data successfully save'
            ]);
            } catch (RequestException $r) {

                http_response_code(400);
                return response([
                    'message' => $r,
                    'errorCode' => 4103,
                ], 400);
            }

        
    }
    
}
