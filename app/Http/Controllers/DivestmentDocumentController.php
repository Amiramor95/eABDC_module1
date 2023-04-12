<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\DivestmentDocument;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class DivestmentDocumentController extends Controller
{
    // file 1
    public function getDocumentByIDOne(Request $request)
    {
        try {

            // dd('test');
           
			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',1)
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

    //file 2
    public function getDocumentByIDTwo(Request $request)
    {
        try {

			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',2)
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

    //file 3
    public function getDocumentByIDThree(Request $request)
    {
        try {
           
			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',3)
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

    //file 4
    public function getDocumentByIDFour(Request $request)
    {
        try {
           
			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',4)
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

    //file 2nd Level
    public function getDocumentByIDSecondLevel(Request $request)
    {
        try {
           
			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',5)
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

    //file Approver 1
    public function getDocumentDistApprover(Request $request)
    {
        try {
           
			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',6)
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

    //file RD Approver   
    public function getDocumentRDApprover(Request $request)
    {
        try {
           
			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',7)
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

    //file RD Approver  
    public function getDocumentHODRDApprover(Request $request)
    {
        try {
           
			$data = DivestmentDocument::where('DIVE_ID',$request->DIVE_ID)
            ->where('FILE_NO',8)
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

    public function delete(Request $request)
    {
        try {
            $data = DivestmentDocument::find($request->DIVE_DOCU_ID );
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
