<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\SuspendRevokeAppealApprDoc;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class SuspendRevokeAppealApprDocController extends Controller
{
    public function createDocumentAppealApproval(Request $request)
    {
        try {

                $file = $request->fileNew;
                foreach($file as $item){
                    $itemFile = $item;
            
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new SuspendRevokeAppealApprDoc;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->SUSPEND_REVOKE_APPEAL_ID =  $request->SUSPEND_REVOKE_APPEAL_ID;
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

    public function getDocumentByAppealApprover(Request $request)
    {
        try {
           
			$data = SuspendRevokeAppealApprDoc::where('SUSPEND_REVOKE_APPEAL_ID',$request->SUSPEND_REVOKE_APPEAL_ID)
            ->where('CREATE_BY',$request->CREATE_BY)
            ->get();

           
            foreach($data as $element){
                $element->DOC_BLOB = base64_encode($element->DOC_BLOB);

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

    public function getAllDocumentByAppealApprover(Request $request)
    {
        try {
           
			$data = SuspendRevokeAppealApprDoc::where('SUSPEND_REVOKE_APPEAL_ID',$request->SUSPEND_REVOKE_APPEAL_ID)
            ->get();

           
            foreach($data as $element){
                $element->DOC_BLOB = base64_encode($element->DOC_BLOB);

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

    public function deleteAppealApprovalDocument(Request $request)
    {
        try {
            $data = SuspendRevokeAppealApprDoc::find($request->SR_APPRD_ID);
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
