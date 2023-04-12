<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\CessationAuthorizationLetter;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class CessationAuthorizationLetterController extends Controller
{
    public function getLetterDocByID(Request $request)
    {
        try {
           
			$data = CessationAuthorizationLetter::where('CESSATION_ID',$request->CESSATION_ID)
            ->get();

            foreach($data as $element){

                if ( $element->DOC_BLOB != null ||  $element->DOC_BLOB !=""){
                $element->DOC_BLOB = base64_encode($element->DOC_BLOB);
                }else{
                    $element->DOC_BLOB = "-";
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

    public function uploadLetterDocument(Request $request)
    {
        try {
            $fileLetter = $request->fileLetter;
            if ($fileLetter != null){
            foreach($fileLetter as $item){
                $itemFile = $item;
                $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                $upFile = new CessationAuthorizationLetter;
                $upFile->DOC_BLOB = $blob;
                $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                $upFile->DOC_FILESIZE = $itemFile->getSize();
                $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                $upFile->CREATE_BY = $request->CREATE_BY;
                $upFile->CESSATION_ID = $request->CESSATION_ID;
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

    public function deleteAuhthorizationLetter(Request $request)
    {
        try {
            $data =  CessationAuthorizationLetter::find($request->CAL_DOCUMENT_ID);
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

    public function deleteAuhthorizationLetterByCessationId(Request $request)
    {
        try {
            $data =  CessationAuthorizationLetter::where('CESSATION_ID', $request->CESSATION_ID)->delete();

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
