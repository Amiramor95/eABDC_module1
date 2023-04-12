<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\ManageFormTemplate;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Validator;
use File;
use Image;
use Compress\Compress;
use App\Helpers\Files;

class templateController extends Controller
{
    //
    public function getDistTemplate()
    {
        try {
           $data = DB::table('admin_management.MANAGE_FORM_TEMPLATE AS MANAGE_FORM_TEMPLATE')
           ->select('*' )
           ->join('admin_management.MANAGE_MODULE AS MANAGE_MODULE', 'MANAGE_MODULE.MANAGE_MODULE_ID', '=', 'MANAGE_FORM_TEMPLATE.MANAGE_MODULE_ID')
           ->where('MANAGE_FORM_TEMPLATE.MANAGE_MODULE_ID',10)
           ->get();
           foreach($data as $element){

            if ( $element->FILE_BLOB != null ||  $element->FILE_BLOB !=""){
            $element->FILE_BLOB = base64_encode($element->FILE_BLOB);
            }else{
                $element->FILE_BLOB = "-";
            }
        }
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
}
