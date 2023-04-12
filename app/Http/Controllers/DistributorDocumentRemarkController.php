<?php

namespace App\Http\Controllers;

use App\Models\DistributorDocumentRemark;
use App\Models\DistributorApprovalDocument;
use GuzzleHttp\Exception\RequestException;
use App\Models\DistributorApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Validator;

class DistributorDocumentRemarkController extends Controller
{
    public function get(Request $request)
    {
        //return $request->all();
        try {
			$data = DistributorDocumentRemark::where('DIST_APPR_ID',$request->DIST_APPR_ID)->get();
            foreach($data as $element){
                $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
            };

            $dataDocumentRemark = DistributorApprovalDocument::where('DIST_APPR_ID',$request->DIST_APPR_ID)
            ->join('admin_management.MANAGE_REQUIRED_DOCUMENT AS req_doc', 'req_doc.MANAGE_REQUIRED_DOCUMENT_ID', '=', 'REQUIRED_DOC_ID')
            ->get();

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => ([
                    'dataDocument' => $data, 
                    'dataDocumentRemark' =>$dataDocumentRemark
                ]),
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.', 
                'errorCode' => 4103
            ],400);
        }
    }

    public function getReviewData(Request $request)
    {
        ini_set('max_execution_time', 180);
        //return $request->all();
        try {

            $approval = DistributorApproval::where('DIST_ID',$request->dist_id)
                ->where('APPROVAL_LEVEL_ID',$request->level_id)
                ->where('APPR_GROUP_ID',$request->group_id)->first();
            //return $approval;
            $doc = DistributorDocumentRemark::select('*')
                ->where('DIST_ID',($request->dist_id ?? 0))
                ->where('DIST_APPR_ID',($approval->DIST_APPROVAL_ID ?? 0))
                //->where('DOCU_TYPE',1)
                ->get();
                
            foreach($doc as $element){
                $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
            };    

            $docRemark = DistributorApprovalDocument::where('DIST_APPR_ID',($approval->DIST_APPROVAL_ID ?? 0))
                ->join('admin_management.MANAGE_REQUIRED_DOCUMENT AS req_doc', 'req_doc.MANAGE_REQUIRED_DOCUMENT_ID', '=', 'REQUIRED_DOC_ID')
                ->get();
            
                
            // $data = DistributorDocumentRemark::get();
            // $dataDocumentRemark = DistributorApprovalDocument::get();

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => ([
                    'approval' => $approval, 
                    'doc' => $doc,
                    'docRemark' => $docRemark
                ]),
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
            $data = DistributorDocumentRemark::all();

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
			'DIST_APPR_ID' => 'integer|nullable', 
			'DOCU_BLOB' => 'integer|nullable', 
			'DOCU_FILETYPE' => 'string|nullable', 
			'DOCU_FILESIZE' => 'integer|nullable', 
			'DOCU_ORIGINAL_NAME' => 'string|nullable', 
			'DOCU_TYPE' => 'integer|nullable' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            //create function

            http_response_code(200);
            return response([
                'message' => 'Data successfully updated.'
            ]);

        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4100
            ],400);
        }

    }

    public function manage(Request $request)
    {
$validator = Validator::make($request->all(), [ 
			'DIST_APPR_ID' => 'integer|nullable', 
			'DOCU_BLOB' => 'integer|nullable', 
			'DOCU_FILETYPE' => 'string|nullable', 
			'DOCU_FILESIZE' => 'integer|nullable', 
			'DOCU_ORIGINAL_NAME' => 'string|nullable', 
			'DOCU_TYPE' => 'integer|nullable' 
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
			'DIST_APPR_ID' => 'integer|nullable', 
			'DOCU_BLOB' => 'integer|nullable', 
			'DOCU_FILETYPE' => 'string|nullable', 
			'DOCU_FILESIZE' => 'integer|nullable', 
			'DOCU_ORIGINAL_NAME' => 'string|nullable', 
			'DOCU_TYPE' => 'integer|nullable' 
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ],400);
        }

        try {
            $data = DistributorDocumentRemark::where('id',$id)->first();
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
            $data = DistributorDocumentRemark::find($id);
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
			'DIST_APPR_ID' => 'integer|nullable', 
			'DOCU_BLOB' => 'integer|nullable', 
			'DOCU_FILETYPE' => 'string|nullable', 
			'DOCU_FILESIZE' => 'integer|nullable', 
			'DOCU_ORIGINAL_NAME' => 'string|nullable', 
			'DOCU_TYPE' => 'integer|nullable' 
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
