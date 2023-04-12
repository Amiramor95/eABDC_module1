<?php

namespace App\Http\Controllers;

use App\Models\DistributorType;
use App\Models\DistributorTypeRegistrationApproval;
use App\Models\User;
use App\Models\Distributor;
use App\Models\DistributorAddress;
use App\Models\DistributorDocument;
use App\Models\DistributorDetailInfo;
use App\Models\DistributorRepresentative;
use App\Models\DistributorDirector;
use App\Models\DistributorAdditionalInfo;
use App\Models\DistributorStatus;
use App\Models\DistributorLedger;
use App\Models\DistributorFinancialPlanner;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Validator;
use DB;

class DistributorTypeController extends Controller
{
    public function get(Request $request)
    {
        try {
            $data = DistributorType::find($request->DISTRIBUTOR_TYPE_ID);

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

    public function getDistTypeByDistTypeId(Request $request)
    {
        try {
            DB::enableQueryLog();
            $data = DB::table('distributor_management.DISTRIBUTOR_TYPE AS A')
                ->select('*')
                ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS B', 'A.DIST_TYPE', '=', 'B.DISTRIBUTOR_TYPE_ID')
                ->leftJoin('distributor_management.DISTRIBUTOR_LEDGER AS C', 'A.DIST_TYPE_ID', '=', 'C.DIST_TYPE_ID')
                ->leftJoin('admin_management.SETTING_GENERAL AS D', 'D.SETTING_GENERAL_ID', '=', 'C.DIST_ISSUEBANK')
                ->where("A.DIST_ID", $request->USER_DIST_ID)
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

    public function getTypeRegByDistId(Request $request)
    {
        try {

            DB::enableQueryLog();
            $data =  Distributor::select(
                '*',
                'sg2.SET_PARAM AS SET_PARAM_STATE',
                'sg2.SETTING_GENERAL_ID AS STATE_ID',
                'sg2.SET_CODE AS SET_CODE_STATE',
                'sg1.SET_PARAM AS SET_PARAM_COUNTRY',
                'sg1.SETTING_GENERAL_ID AS COUNTRY_ID',
                'sg1.SET_CODE AS SET_CODE_COUNTRY',
                'bank.SET_PARAM as BANK_NAME'
            )
                ->where('DISTRIBUTOR_ID', $request->DIST_ID)
                ->join('DISTRIBUTOR_ADDRESS', 'DISTRIBUTOR_ADDRESS.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                ->join('DISTRIBUTOR_TYPE', 'DISTRIBUTOR_TYPE.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                ->join('DISTRIBUTOR_LEDGER', 'DISTRIBUTOR_LEDGER.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                ->join('DISTRIBUTOR_DETAIL_INFO', 'DISTRIBUTOR_DETAIL_INFO.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                ->join('DISTRIBUTOR_STATUS', 'DISTRIBUTOR_STATUS.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                ->leftJoin('admin_management.SETTING_GENERAL AS sg1', 'sg1.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_COUNTRY')
                ->leftJoin('admin_management.SETTING_CITY AS setting_city', 'setting_city.SETTING_CITY_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_CITY')
                ->leftJoin('admin_management.SETTING_GENERAL AS sg2', 'sg2.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_STATE')
                ->leftJoin('admin_management.SETTING_POSTAL AS setting_postal', 'setting_postal.SETTING_POSTCODE_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_POSTAL')
                ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS distributor_type', 'distributor_type.DISTRIBUTOR_TYPE_ID', '=', 'DISTRIBUTOR_TYPE.DIST_TYPE')
                ->leftJoin('admin_management.SETTING_GENERAL AS bank', 'bank.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_LEDGER.DIST_ISSUEBANK')

                ->first();

            // dd(DB::getQueryLog());
            $dataRepr = DistributorRepresentative::where('DIST_ID', $data->DISTRIBUTOR_ID)
                ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_REPRESENTATIVE.REPR_SALUTATION')
                ->get();

            $dataAI = DistributorAdditionalInfo::where('DIST_ID', $data->DISTRIBUTOR_ID)
                ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_ADDITIONAL_INFO.ADD_SALUTATION')
                ->get();

            $dataDir = DistributorDirector::where('DIST_ID', $data->DISTRIBUTOR_ID)
                ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_DIRECTOR.DIR_SALUTATION')
                ->orderBy('CREATE_TIMESTAMP', 'desc')
                ->get();

            foreach ($dataDir as $element) {
                $element->DIR_DATE_EFFECTIVE_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_EFFECTIVE));
                $element->DIR_DATE_END_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_END));
                $element->DIR_NAME_DISPLAY = $element->USER_SAL_NAME . ' ' . $element->DIR_NAME;
                // $element->DIR_NRIC = $element->DIR_NRIC != null ? substr($element->DIR_NRIC, 0, 6).'-'.substr($element->DIR_NRIC, 6, 2).'-'.substr($element->DIR_NRIC, 8, 4) : '-';
            };

            $dataFP = DistributorFinancialPlanner::where('DIST_ID', $data->DISTRIBUTOR_ID)
                ->select('*')
                ->first();

            $dataDoc = DistributorDocument::where('DIST_ID', $data->DISTRIBUTOR_ID)
                ->where('DOCU_GROUP', '!=', 1)
                ->where('DOCU_GROUP', '!=', 2)
                //->join('DISTRIBUTOR_APPROVAL_DOCUMENT', 'DISTRIBUTOR_APPROVAL_DOCUMENT.DIST_DOC_ID', '=', 'DIST_DOCU_ID')
                ->get();

            foreach ($dataDoc as $element) {
                $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
            };

            $dist_id = $request->DIST_ID;
            $group_id = $request->APPR_GROUP_ID;
            $dataApprovalLog = DistributorTypeRegistrationApproval::join('admin_management.TASK_STATUS AS task_status', 'task_status.TS_ID', '=', 'APPROVAL_STATUS')
                ->leftjoin('admin_management.USER AS user', 'user.USER_ID', '=', 'APPROVAL_FIMM_USER')
                ->join('admin_management.MANAGE_GROUP AS group', 'group.MANAGE_GROUP_ID', '=', 'APPR_GROUP_ID')
                //->leftjoin('DISTRIBUTOR_DOCUMENT_REMARK AS docRemark', 'docRemark.DIST_APPR_ID', '=', 'DIST_APPROVAL_ID')
                ->leftjoin('admin_management.MANAGE_DEPARTMENT AS department', 'department.MANAGE_DEPARTMENT_ID', '=', 'group.MANAGE_DEPARTMENT_ID')
                ->where('DIST_ID', $request->DIST_ID)
                // ->where('APPR_GROUP_ID','!=',$request->APPR_GROUP_ID)
                // ->whereIn('DIST_APPROVAL_ID', function ($query) use ($dist_id, $group_id) {
                //     return $query->select(DB::raw('max(DA2.DIST_APPROVAL_ID) as DIST_APPROVAL_ID'))
                //         ->from('DISTRIBUTOR_APPROVAL AS DA2')
                //         ->where('DA2.DIST_ID','=', $dist_id)
                //         ->where('DA2.APPR_GROUP_ID','!=',$group_id)
                //         ->groupBy('DA2.APPR_GROUP_ID');
                // })
                //->groupBy('APPR_GROUP_ID')
                ->orderBy('DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL_ID', 'asc')
                ->get();
            foreach ($dataApprovalLog as $element) {
                $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
                $element->APPR_REMARK_DOCU_ADDITIONALINFO = base64_encode($element->APPR_REMARK_DOCU_ADDITIONALINFO);
                $element->APPR_REMARK_DOCU_ARnAAR = base64_encode($element->APPR_REMARK_DOCU_ARnAAR);
                $element->APPR_REMARK_DOCU_CEOnDIR = base64_encode($element->APPR_REMARK_DOCU_CEOnDIR);
                $element->APPR_REMARK_DOCU_DETAILINFO = base64_encode($element->APPR_REMARK_DOCU_DETAILINFO);
                $element->APPR_REMARK_DOCU_PAYMENT = base64_encode($element->APPR_REMARK_DOCU_PAYMENT);
                $element->APPROVAL_DATE = date('d-M-Y', strtotime($element->APPROVAL_DATE));
            };
            //$dataDocumentRemarkLog =

            $data->DATAREPR = $dataRepr;
            $data->DATAAI = $dataAI;
            $data->DATADIR = $dataDir;
            $data->DATADOC = $dataDoc;
            $data->DATAFP = $dataFP;
            $data->APPRLOG = $dataApprovalLog;


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

    public function getAll()
    {
        try {
            $data = DistributorType::all();

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
            ], 400);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'DIST_ID' => 'required|integer',
            'DIST_TYPE' => 'required|integer',
            'CREATE_TIMESTAMP' => 'required|integer'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
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
            ], 400);
        }
    }

    public function manage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'DIST_ID' => 'required|integer',
            'DIST_TYPE' => 'required|integer',
            'CREATE_TIMESTAMP' => 'required|integer'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
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
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'DIST_ID' => 'required|integer',
            'DIST_TYPE' => 'required|integer',
            'CREATE_TIMESTAMP' => 'required|integer'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            $data = DistributorType::where('id', $id)->first();
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
            ], 400);
        }
    }

    public function delete($id)
    {
        try {
            $data = DistributorType::find($id);
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
            ], 400);
        }
    }

    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'DIST_ID' => 'required|integer',
            'DIST_TYPE' => 'required|integer',
            'CREATE_TIMESTAMP' => 'required|integer'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
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
            ], 400);
        }
    }
}
