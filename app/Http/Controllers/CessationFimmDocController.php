<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\CessationFimmDoc;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use Illuminate\Support\Str;

class CessationFimmDocController extends Controller
{
    public function getFimmCessationDocument(Request $request)
    {
        try {
           
			$data = CessationFimmDoc::where('CESSATION_ID',$request->CESSATION_ID)
            ->get();

            foreach($data as $element){

                if ( $element->DOC_BLOB != null ||  $element->DOC_BLOB !=""){
                $element->DOC_BLOB = base64_encode($element->DOC_BLOB);
                }else{
                    $element->DOC_BLOB = "-";
                }

                $getUser = DB::table('admin_management.USER')
                ->select('USER_NAME')
                ->where('USER_ID','=',$element->CREATE_BY)
                ->first();

                if($getUser->USER_NAME != " " || $getUser->USER_NAME != null ){
                    $element->CREATE_BY = $getUser->USER_NAME;
                }

                
            };
        
        

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

    public function getFimmCessationDocumentByID(Request $request)
    {
        try {
           
			$data = CessationFimmDoc::where('CESSATION_ID',$request->CESSATION_ID)
            ->where('CREATE_BY',$request->CREATE_BY)
            ->get();

           
            foreach($data as $element){
                $element->DOC_BLOB = base64_encode($element->DOC_BLOB);

            };
        

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

    public function fimmUploadCessationDocument(Request $request)
    {
        try {
            $file = $request->file;
            if ($file != null){
            foreach($file as $item){
                $itemFile = $item;
                $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                $upFile = new CessationFimmDoc;
                $upFile->DOC_BLOB = $blob;
                $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                $upFile->DOC_FILESIZE = $itemFile->getSize();
                $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                $upFile->CREATE_BY = $request->CREATE_BY;
                $upFile->CESSATION_ID = $request->CESSATION_ID;
                $upFile->CESSATION_FIMM_APPROVAL_ID = $request->CESSATION_FIMM_APPROVAL_ID;
                $upFile->save();
                }
            }
        
    
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be save.',
                'errorCode' => 0
            ]);
        }
    }

    public function deleteFimmCessationDocument(Request $request)
    {
        try {
            $data =  CessationFimmDoc::find($request->CFD_DOCUMENT_ID);
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
}
