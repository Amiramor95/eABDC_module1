<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\SuspendRevokeAppealDoc;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class SuspendRevokeAppealDocController extends Controller
{
    public function getAppealDocByID(Request $request)
    {
        try {
           
			$data = SuspendRevokeAppealDoc::where('SUSPEND_REVOKE_APPEAL_ID',$request->SUSPEND_REVOKE_APPEAL_ID)
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

    public function deleteAppealDocument(Request $request)
    {
        try {
            $data = SuspendRevokeAppealDoc::find($request->SR_APPEAL_DOC_ID);
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

    public function createDocumentAppeal(Request $request)
    {
        try {
            $searchAppealRecord = DB::table('distributor_management.SUSPEND_REVOKE_APPEAL')
            ->select('SUSPEND_REVOKE_APPEAL_ID')
            ->where('SUSPEND_REVOKE_ID','=', $request->SUSPEND_REVOKE_ID)
            ->first();

                $file = $request->file;
                foreach($file as $item){
                    $itemFile = $item;
            
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new SuspendRevokeAppealDoc;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->SUSPEND_REVOKE_APPEAL_ID =  $searchAppealRecord->SUSPEND_REVOKE_APPEAL_ID;
                    $upFile->save();
                }
    
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be save.',
                'errorCode' => 0
            ]);
        }
    }

}
