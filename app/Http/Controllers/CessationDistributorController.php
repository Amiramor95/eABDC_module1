<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\CessationDistributor;
use App\Models\CessationDistributorApproval;
use App\Models\CessationDistributorDoc;
use App\Models\CessationAuthorizationLetter;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use Illuminate\Support\Str;

class CessationDistributorController extends Controller
{
    public function getCessationListByDistributor(Request $request)
    {
        try {
            $data = DB::table('distributor_management.CESSATION_DISTRIBUTOR AS CD')
            ->select(
                'CD.CESSATION_ID',
                'CD.DISTRIBUTOR_ID',
                'CD.CESSATION_TYPE',
                'CD.CESSATION_DATE',
                'CD.LEGAL_DATE',
                'CD.DISTRIBUTOR_MERGER',
                'CD.OTHER_REMARK',
                'CD.RECIPIENT_NAME',
                'CD.BANK_ID',
                'CD.ACCOUNT_NO',
                'CD.LATEST_UPDATE',
                'CD.TS_ID',
                'CD.LATEST_UPDATE_BY',
                'CD.ISSUBMIT',
                'CD.CREATE_TIMESTAMP',
                'TASK.TS_PARAM',
                'USR.USER_NAME',
                'D.DIST_NAME AS DISTRIBUTOR_NAME',
                'TYPE.SET_PARAM AS CESSATION_NAME',
                'BANK.SET_PARAM AS BANK_NAME',
                'DM.DIST_NAME AS DISTRIBUTOR_MERGER_NAME',
                'CD.FIMM_TS_ID',
                'TS.TS_PARAM AS FIMM_STATUS',
                'CD.FIMM_REMARK',
                'CD.FIMM_REMARK AS FIMM_REMARK_FULL',
                'CD.SUBMISSION_DATE'
            )

            ->leftJoin('admin_management.TASK_STATUS AS TASK', 'TASK.TS_ID', '=', 'CD.TS_ID')
            ->leftJoin('admin_management.TASK_STATUS AS TS', 'TS.TS_ID', '=', 'CD.FIMM_TS_ID')
            ->leftJoin('distributor_management.USER AS USR', 'USR.USER_ID', '=', 'CD.CREATE_BY')
            ->leftJoin('distributor_management.DISTRIBUTOR AS D', 'D.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR AS DM', 'DM.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_MERGER')
            ->leftJoin('admin_management.SETTING_GENERAL AS TYPE', 'TYPE.SETTING_GENERAL_ID', '=', 'CD.CESSATION_TYPE')
            ->leftJoin('admin_management.SETTING_GENERAL AS BANK', 'BANK.SETTING_GENERAL_ID', '=', 'CD.BANK_ID')

            ->where('CD.DISTRIBUTOR_ID', '=', $request->DISTRIBUTOR_ID)

            ->get();

            foreach ($data as $item) {
                if ($item->CREATE_TIMESTAMP != null || $item->CREATE_TIMESTAMP != "") {
                    $item->CREATE_TIMESTAMP = date('d-M-Y', strtotime($item->CREATE_TIMESTAMP));
                } else {
                    $item->CREATE_TIMESTAMP = '-';
                }

                if ($item->SUBMISSION_DATE != null || $item->SUBMISSION_DATE != "") {
                    $item->SUBMISSION_DATE = date('d-M-Y', strtotime($item->SUBMISSION_DATE));
                } else {
                    $item->SUBMISSION_DATE ="-";
                }

                if ($item->CESSATION_NAME == "" ||$item->CESSATION_NAME == null) {
                    $item->CESSATION_NAME ='-';
                }

                if ($item->CESSATION_DATE != null || $item->CESSATION_DATE != "") {
                    $item->CESSATION_DATE = date('d-M-Y', strtotime($item->CESSATION_DATE));
                } else {
                    $item->CESSATION_DATE ="-";
                }
                if ($item->LEGAL_DATE != null || $item->LEGAL_DATE != "") {
                    $item->LEGAL_DATE = date('d-M-Y', strtotime($item->LEGAL_DATE));
                } else {
                    $item->LEGAL_DATE ="-";
                }

                if ($item->DISTRIBUTOR_MERGER_NAME == null || $item->DISTRIBUTOR_MERGER_NAME == "") {
                    $item->DISTRIBUTOR_MERGER_NAME = "-";
                }

                if ($item->OTHER_REMARK == null || $item->OTHER_REMARK == "" || $item->OTHER_REMARK == "null") {
                    $item->OTHER_REMARK = "-";
                }
                if ($item->RECIPIENT_NAME == null || $item->RECIPIENT_NAME == "") {
                    $item->RECIPIENT_NAME = "-";
                }
                if ($item->ACCOUNT_NO == null || $item->ACCOUNT_NO == "") {
                    $item->ACCOUNT_NO = "-";
                }

                if ($item->FIMM_STATUS == null || $item->FIMM_STATUS == "" || $item->FIMM_STATUS == "DRAFT") {
                    $item->FIMM_STATUS = "-";
                }
                if ($item->FIMM_REMARK == null || $item->FIMM_REMARK == "" || $item->FIMM_REMARK == 'null') {
                    $item->FIMM_REMARK ="-";
                } else {
                    $item->FIMM_REMARK = Str::limit($item->FIMM_REMARK, 20);
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
            ], 400);
        }
    }

    public function getActiveDistributor()
    {
        try {
            $data = DB::table('distributor_management.DISTRIBUTOR AS D')
            ->select('D.DISTRIBUTOR_ID', 'D.DIST_NAME')
            ->leftJoin('distributor_management.DISTRIBUTOR_TYPE AS DT', 'DT.DIST_ID', '=', 'D.DISTRIBUTOR_ID')
            ->where('DT.ISACTIVE', '=', 22)
            ->groupBy('D.DISTRIBUTOR_ID')
            ->orderBy('D.DIST_NAME', 'ASC')

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

    public function getCessationType()
    {
        try {
            $data = DB::table('admin_management.SETTING_GENERAL')
            ->select('SETTING_GENERAL_ID', 'SET_PARAM')
            ->where('SET_TYPE', '=', 'CESSATIONTYPE')
            ->orderBy('SET_VALUE', 'ASC')
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

    public function getBankList()
    {
        try {
            $data = DB::table('admin_management.SETTING_GENERAL')
            ->select('SETTING_GENERAL_ID', 'SET_PARAM')
            ->where('SET_TYPE', '=', 'BANK')
            ->orderBy('SET_PARAM', 'ASC')
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

    public function getDistributorInfo(Request $request)
    {
        try {
            $company = DB::table('distributor_management.DISTRIBUTOR')
            ->select('DISTRIBUTOR_ID', 'DIST_NAME')
            ->where('DISTRIBUTOR_ID', $request->USER_DIST_ID)
            ->first();


            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $company,
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function getCessationDetailsByID(Request $request)
    {
        try {
            $data = DB::table('distributor_management.CESSATION_DISTRIBUTOR AS CD')
            ->select(
                'CD.CESSATION_ID',
                'CD.DISTRIBUTOR_ID',
                'CD.CESSATION_TYPE',
                'CD.CESSATION_DATE',
                'CD.LEGAL_DATE',
                'CD.DISTRIBUTOR_MERGER',
                'CD.OTHER_REMARK',
                'CD.RECIPIENT_NAME',
                'CD.BANK_ID',
                'CD.ACCOUNT_NO',
                'CD.LATEST_UPDATE',
                'CD.TS_ID',
                'CD.LATEST_UPDATE_BY',
                'CD.ISSUBMIT',
                'CD.CREATE_TIMESTAMP',
                'TASK.TS_PARAM',
                'USR.USER_NAME',
                'D.DIST_NAME AS DISTRIBUTOR_NAME',
                'TYPE.SET_PARAM AS CESSATION_NAME',
                'BANK.SET_PARAM AS BANK_NAME',
                'DM.DIST_NAME AS DISTRIBUTOR_MERGER_NAME'
            )

            ->leftJoin('admin_management.TASK_STATUS AS TASK', 'TASK.TS_ID', '=', 'CD.TS_ID')
            ->leftJoin('distributor_management.USER AS USR', 'USR.USER_ID', '=', 'CD.CREATE_BY')
            ->leftJoin('distributor_management.DISTRIBUTOR AS D', 'D.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_ID')
            ->leftJoin('distributor_management.DISTRIBUTOR AS DM', 'DM.DISTRIBUTOR_ID', '=', 'CD.DISTRIBUTOR_MERGER')
            ->leftJoin('admin_management.SETTING_GENERAL AS TYPE', 'TYPE.SETTING_GENERAL_ID', '=', 'CD.CESSATION_TYPE')
            ->leftJoin('admin_management.SETTING_GENERAL AS BANK', 'BANK.SETTING_GENERAL_ID', '=', 'CD.BANK_ID')

            ->where('CD.CESSATION_ID', '=', $request->CESSATION_ID)

            ->first();

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

    public function createCessationSubmission(Request $request)
    {
        try {
            $data = new CessationDistributor;
            $data->CESSATION_TYPE = $request->CESSATION_TYPE;
            $data->DISTRIBUTOR_ID = $request->DISTRIBUTOR_ID;
            $data->CESSATION_DATE = $request->CESSATION_DATE;
            $data->LEGAL_DATE = $request->LEGAL_DATE;
            $data->OTHER_REMARK = $request->OTHER_REMARK;
            $data->DISTRIBUTOR_MERGER = $request->DISTRIBUTOR_MERGER;
            $data->BANK_ID = $request->BANK_ID;
            $data->ACCOUNT_NO = $request->ACCOUNT_NO;
            $data->RECIPIENT_NAME = $request->RECIPIENT_NAME;
            $data->PUBLISH_STATUS = $request->PUBLISH_STATUS;
            $data->TS_ID = $request->TS_ID;
            $data->CREATE_BY = $request->CREATE_BY ;
            $data->LATEST_UPDATE_BY = $request->CREATE_BY ;
            $data->save();

            if ($request->PUBLISH_STATUS == "1") {

                //first entry
                foreach (json_decode($request->ENTRY_LIST) as $item) {
                    $dataEntry = new CessationDistributorApproval;
                    $dataEntry->CESSATION_ID = $data->CESSATION_ID;
                    $dataEntry->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                    $dataEntry->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                    $dataEntry->TS_ID = 2;
                    $dataEntry->CREATE_BY = $request->CREATE_BY;
                    $dataEntry->APPR_PUBLISH_STATUS = $request->PUBLISH_STATUS;
                    $dataEntry->save();
                }

                foreach (json_decode($request->APPR_LIST) as $item) {
                    $dataApproval = new CessationDistributorApproval;
                    $dataApproval->CESSATION_ID = $data->CESSATION_ID;
                    $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                    $dataApproval->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                    $dataApproval->TS_ID = $request->TS_ID;
                    $dataApproval->save();

                    $notification = new ManageDistributorNotification();
                    $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $request->DISTRIBUTOR_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                }
            }

            $file = $request->file;
            if ($file != null) {
                foreach ($file as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new CessationDistributorDoc;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->CESSATION_ID = $data->CESSATION_ID;
                    $upFile->save();
                }
            }

            $fileLetter = $request->fileLetter;
            if ($fileLetter != null) {
                foreach ($fileLetter as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new CessationAuthorizationLetter;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName();//$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->CESSATION_ID = $data->CESSATION_ID;
                    $upFile->save();
                }
            }



            http_response_code(200);
            if ($request->PUBLISH_STATUS == "0") {
                return response([ 'message' => 'Data successfully save' ]);
            } elseif ($request->PUBLISH_STATUS == "1") {
                return response([ 'message' => 'Data successfully submitted' ]);
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                    'message' => $r,
                    'errorCode' => 4103,
                ], 400);
        }
    }

    public function updateCessationSubmission(Request $request)
    {
        try {
            $data= CessationDistributor::find($request->CESSATION_ID);
            $data->CESSATION_TYPE = $request->CESSATION_TYPE;
            $data->DISTRIBUTOR_ID = $request->DISTRIBUTOR_ID;
            $data->CESSATION_DATE = $request->CESSATION_DATE;
            $data->LEGAL_DATE = $request->LEGAL_DATE;
            $data->OTHER_REMARK = $request->OTHER_REMARK;
            $data->DISTRIBUTOR_MERGER = $request->DISTRIBUTOR_MERGER;
            $data->BANK_ID = $request->BANK_ID;
            $data->ACCOUNT_NO = $request->ACCOUNT_NO;
            $data->RECIPIENT_NAME = $request->RECIPIENT_NAME;
            $data->PUBLISH_STATUS = $request->PUBLISH_STATUS;
            $data->TS_ID = $request->TS_ID;
            if ($request->PUBLISH_STATUS == "1") {
                $data->FIMM_TS_ID = "";
                $data->FIMM_REMARK = "";
            }
            $data->CREATE_BY = $request->CREATE_BY ;
            $data->LATEST_UPDATE_BY = $request->CREATE_BY ;

            $query = $data->save();

            if ($request->PUBLISH_STATUS == "1") {

                //first entry
                foreach (json_decode($request->ENTRY_LIST) as $item) {
                    //count record
                    $getCount= DB::table('CESSATION_DISTRIBUTOR_APPROVAL')
                    ->select(DB::raw('COUNT(CESSATION_DISTRIBUTOR_APPROVAL_ID) AS TOTAL'))
                    ->where('CESSATION_ID', '=', $request->CESSATION_ID)
                    ->where('APPR_GROUP_ID', '=', $item->APPR_GROUP_ID)
                    ->orderby('CESSATION_DISTRIBUTOR_APPROVAL_ID', 'DESC')
                    ->first();

                    //resubmit = 37
                    if ($getCount->TOTAL > 1) {
                        $getCDAID= DB::table('CESSATION_DISTRIBUTOR_APPROVAL')
                    ->select('CESSATION_DISTRIBUTOR_APPROVAL_ID')
                    ->where('CESSATION_ID', '=', $request->CESSATION_ID)
                    ->where('APPR_GROUP_ID', '=', $item->APPR_GROUP_ID)
                    ->orderby('CESSATION_DISTRIBUTOR_APPROVAL_ID', 'DESC')
                    ->first();

                        $appr= CessationDistributorApproval::find($getCDAID->CESSATION_DISTRIBUTOR_APPROVAL_ID);
                        $appr->CREATE_BY = $request->CREATE_BY;
                        $appr->APPR_PUBLISH_STATUS = $request->PUBLISH_STATUS;
                        $appr->TS_ID = 37;
                        $appr->save();
                    } elseif ($getCount->TOTAL < 1 || $getCount->TOTAL == null) {
                        $dataEntry = new CessationDistributorApproval;
                        $dataEntry->CESSATION_ID = $request->CESSATION_ID;
                        $dataEntry->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                        $dataEntry->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                        $dataEntry->TS_ID = 2;
                        $dataEntry->CREATE_BY = $request->CREATE_BY;
                        $dataEntry->APPR_PUBLISH_STATUS = $request->PUBLISH_STATUS;
                        $dataEntry->save();
                    }
                }

                foreach (json_decode($request->APPR_LIST) as $item) {
                    $dataApproval = new CessationDistributorApproval;
                    $dataApproval->CESSATION_ID = $request->CESSATION_ID;
                    $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                    $dataApproval->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                    $dataApproval->TS_ID = $request->TS_ID;
                    $dataApproval->save();

                    $notification = new ManageDistributorNotification();
                    $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $request->DISTRIBUTOR_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                }
            }



            http_response_code(200);
            if ($request->PUBLISH_STATUS == "0") {
                return response([ 'message' => 'Data successfully save' ]);
            } elseif ($request->PUBLISH_STATUS == "1") {
                return response([ 'message' => 'Data successfully submitted' ]);
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                    'message' => $r,
                    'errorCode' => 4103,
                ], 400);
        }
    }
}
