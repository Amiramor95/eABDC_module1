<?php

namespace App\Http\Controllers;

use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use App\Models\User;
use App\Models\Distributor;
use App\Models\DistributorAddress;
use App\Models\DistributorType;
use App\Models\DistributorApprovalDocument;
use App\Models\DistributorDocumentRemark;
use App\Models\DistributorDocument;
use App\Models\DistributorDetailInfo;
use App\Models\DistributorRepresentative;
use App\Models\DistributorDirector;
use App\Models\DistributorAdditionalInfo;
use App\Models\DistributorStatus;
use App\Models\DistributorLedger;

use GuzzleHttp\Exception\RequestException;
use App\Models\DistributorTypeRegistrationApproval;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;

class DistributorTypeRegistrationApprovalController extends Controller
{
    public function getDistIDType($id)
    {
        try {


            $active_dist_types = DB::table('distributor_management.DISTRIBUTOR_TYPE as TYPE')
                ->where("TYPE.DIST_ID", $id)
                ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS DIST_TYPE', 'DIST_TYPE.DISTRIBUTOR_TYPE_ID', '=', 'TYPE.DIST_TYPE')
                ->pluck('DIST_TYPE_NAME')->toArray();

            // if (in_array('CUTA', $active_dist_types)) {
            //     $new_dist_type = 'CPRA';
            // } elseif (in_array('CPRA', $active_dist_types)) {
            //     $new_dist_type = "CUTA";
            // }


            $first_dist_type = ["UTMC", "PRP", "IUTA", "IPRA"];

            if (array_intersect($active_dist_types, $first_dist_type)) {
                foreach ($active_dist_types as $dist_type) {

                    if ($dist_type == 'UTMC') {
                        $first_dist_type[0] = null;
                    }
                    if ($dist_type == 'PRP') {
                        $first_dist_type[1] = null;
                    }
                    if ($dist_type == 'IUTA') {
                        $first_dist_type[2] = null;
                    }
                    if ($dist_type == 'IPRA') {
                        $first_dist_type[3] = null;
                    }
                }
            } else {
                $first_dist_type = [];
            }




            $second_dist_type = ['CUTA', 'CPRA'];

            if (array_intersect($active_dist_types, $second_dist_type)) {

                foreach ($active_dist_types as $dist_type) {

                    if ($dist_type == 'CUTA') {
                        $second_dist_type[0] = null;
                    }
                    if ($dist_type == 'CPRA') {
                        $second_dist_type[1] = null;
                    }
                }
            } else {
                $second_dist_type = [];
            }



            $final_dist_type = array_merge(array_filter($first_dist_type), array_filter($second_dist_type));

            $data = DB::table('admin_management.DISTRIBUTOR_TYPE')
                ->select('DISTRIBUTOR_TYPE_ID', 'DIST_TYPE_NAME')
                ->whereIn('DIST_TYPE_NAME', $final_dist_type)
                ->get();

            // dd($data, $active_dist_types);

            //$data = DistributorType::find($request->DISTRIBUTOR_TYPE_ID);

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
    public function get(Request $request)
    {
        try {
            $data = DistributorTypeRegistrationApproval::find($request->DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL_ID);

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

    public function getRegistrationTypeList(Request $request)
    {
        try {
            $newData = array();
            $group_id = $request->APPR_GROUP_ID;
            DB::enableQueryLog();
            $data = DB::table('distributor_management.DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL AS DTRA')
                ->select('*', 'task_status2.TS_PARAM AS TS_PARAM_MAIN', 'task_status.TS_PARAM AS TS_PARAM', 'task_status.TS_PARAM AS TS_PARAM_R',  'dist_type.DIST_TYPE')
                ->join('admin_management.TASK_STATUS AS task_status', 'task_status.TS_ID', '=', 'DTRA.APPROVAL_STATUS')
                ->join('distributor_management.DISTRIBUTOR AS dist', 'dist.DISTRIBUTOR_ID', '=', 'DTRA.DIST_ID')
                ->join('admin_management.APPROVAL_LEVEL AS appr_level', 'appr_level.APPROVAL_LEVEL_ID', '=', 'DTRA.APPROVAL_LEVEL_ID')
                ->join('distributor_management.DISTRIBUTOR_TYPE as dist_type', 'DTRA.DIST_TYPE_ID', '=', 'dist_type.DIST_TYPE_ID')
                ->join('admin_management.DISTRIBUTOR_TYPE as sgtype', 'sgtype.DISTRIBUTOR_TYPE_ID', '=', 'dist_type.DIST_TYPE')

                ->leftJoin('distributor_management.DISTRIBUTOR_STATUS AS dist_status', 'dist_status.DIST_ID', '=', 'DTRA.DIST_ID')
                ->leftJoin('admin_management.TASK_STATUS AS task_status2', 'task_status2.TS_ID', '=', 'dist_status.DIST_APPROVAL_STATUS')
                ->where('DTRA.APPR_GROUP_ID', '=', $request->APPR_GROUP_ID)

                ->whereIn('DTRA.APPROVAL_DATE', function ($query) use ($group_id) {
                    return $query->select(DB::raw('max(DA2.APPROVAL_DATE) as APPROVAL_DATE'))
                        ->from('distributor_management.DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL AS DA2')
                        ->where('DA2.APPR_GROUP_ID', '=', $group_id)
                        ->groupBy('DA2.DIST_ID');
                })
                // ->whereIn('DA.APPROVAL_STATUS', ['15', '14', '2', '1'])

                //->groupBy('DA.DIST_ID')
                ->orderBy('DTRA.APPROVAL_DATE', 'DESC')
                ->orderByRaw("FIELD(DTRA.APPROVAL_STATUS ,'15', '14', '2','1') DESC");


            // filters
            if ($params = $request->get('filters')) {
                $filters = json_decode($params, true);

                if (array_key_exists('DIST_NAME', $filters) && $filters['DIST_NAME']) {
                    $data->where('dist.DIST_NAME', $filters['DIST_NAME']);
                }

                if (array_key_exists('REG_NUM', $filters) && $filters['REG_NUM']) {
                    $data->where('dist.DIST_REGI_NUM1', $filters['REG_NUM']);
                }

                if (array_key_exists('NEW_REG_NUM', $filters) && $filters['NEW_REG_NUM']) {
                    $data->where('dist.DIST_REGI_NUM2', $filters['NEW_REG_NUM']);
                }
                if (array_key_exists('STATUS', $filters) && $filters['STATUS']) {
                    $data->where('task_status.TS_PARAM', $filters['STATUS']);
                }
            }
            // ->whereIn('DTRA.APPROVAL_DATE', function ($query) use ($group_id) {
            //     return $query->select(DB::raw('max(DTRA2.APPROVAL_DATE) as APPROVAL_DATE'))
            //         ->from('distributor_management.DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL AS DTRA2')
            //         ->where('DTRA2.APPR_GROUP_ID', '=', $group_id)
            //         ->groupBy('DTRA2.DIST_TYPE_ID');
            // })
            // ->groupBy('DTRA.DIST_ID')
            $data = $data->get();



            foreach ($data as $item) {
                $item->DIST_NAME = $item->DIST_NAME ?? '-';
                $item->DIST_REGI_NUM1 = $item->DIST_REGI_NUM1 ?? '-';
                $item->DIST_REGI_NUM2 = $item->DIST_REGI_NUM2 ?? '-';
                $item->DIST_PHONE_NUMBER = $item->DIST_PHONE_NUMBER ?? '-';
                $item->DIST_EMAIL = $item->DIST_EMAIL ?? '-';
                $item->USER_NAME = $item->USER_NAME ?? '-';
                $item->LATEST_DATE = $item->LATEST_DATE ?? '-';
                $item->TS_PARAM_R = $item->TS_PARAM_R ?? '-';

                $item->LATEST_DATE = date('d-m-Y', strtotime($item->LATEST_DATE));

                if ($item->APPR_INDEX == 1) {
                    $item->TS_PARAM = $item->TS_PARAM_MAIN;
                    $newData[] = $item;
                } else {
                    $dataAppr = DistributorTypeRegistrationApproval::where('APPROVAL_INDEX', 1)->first();

                    if ($dataAppr->APPR_GROUP_ID == $item->APPR_GROUP_ID) {
                        $item->TS_PARAM = $item->TS_PARAM_MAIN;
                        $newData[] = $item;
                    } else {
                        if ($item->APPROVAL_STATUS == 15 || $item->APPROVAL_STATUS == 1 || $item->APPROVAL_STATUS == 14 || $item->APPROVAL_STATUS == 17 || $item->APPROVAL_STATUS == 18 || $item->APPROVAL_STATUS == 3) { //pending & REVIEWED
                            $newData[] = $item;
                        }
                    }
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


    public function createRegistrationType(Request $request)
    {
        try {
            //create function
            DB::enableQueryLog();
            //1. register type of distributor_type
            $dataDistType = new DistributorType;
            $dataDistType->DIST_ID = $request->DIST_ID;
            $dataDistType->DIST_TYPE = $request->DIST_TYPE;
            $dataDistType->save();
            //2. Payment Details
            $dataLedger = new DistributorLedger;
            $dataLedger->DIST_ID = $request->DIST_ID;
            $dataLedger->DIST_TYPE_ID = $dataDistType->DIST_TYPE_ID;
            $dataLedger->DIST_TRANS_REF = $request->TYPE_TRANS_REF;
            $dataLedger->DIST_TRANS_DATE = $request->TYPE_TRANS_DATE;
            $dataLedger->DIST_TRANS_TYPE = $request->TYPE_TRANS_TYPE;
            $dataLedger->DIST_ISSUEBANK = $request->TYPE_ISSUE_BANK;
            $dataLedger->DIST_ACC_AMOUNT = $request->TYPE_ACC_AMOUNT;
            $dataLedger->save();
            //3. Proposal
            // foreach($file as $key=>$item){

            //     $itemFile = $item;

            //     $contents = $itemFile->openFile()->fread($itemFile->getSize());

            //      $doc = new DistributorDocument;

            //      $doc->DIST_ID = $request->DIST_ID;
            //      $doc->DOCU_GROUP = 1;
            //      $doc->DOCU_BLOB = $contents;
            //      $doc->REQ_DOCU_ID = $fileId[$key];
            //      $doc->DOCU_ORIGINAL_NAME = $itemFile->getClientOriginalName();
            //      $doc->DOCU_FILESIZE = $itemFile->getSize();
            //      $doc->DOCU_FILETYPE = $itemFile->getClientOriginalExtension();
            //      $doc->DOCU_MIMETYPE = $itemFile->getMimeType();
            //      $doc->save();
            //  }
            //4. Approval
            $dataApproval = new DistributorTypeRegistrationApproval;
            $dataApproval->DIST_ID = $request->DIST_ID;
            $dataApproval->DIST_TYPE_ID = $dataDistType->DIST_TYPE_ID;

            //FOR RD
            $dataApproval->APPR_GROUP_ID = 5;

            $dataApproval->APPROVAL_LEVEL_ID = 7;
            $dataApproval->APPROVAL_STATUS = 2;
            $dataApproval->save();


            foreach (json_decode($request->APPR_LIST) as $item) {

                $notification = new ManageNotification();
                $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DIST) New Type Registration for RD approval.", "distributor-RegisterType-SubmissionList-rdApproval");
            }
            http_response_code(200);
            return response([
                'message' => 'Data successfully Created.'
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be Created.',
                'errorCode' => 4100
            ], 400);
        }
    }

    public function getAll()
    {
        try {
            $data = DistributorTypeRegistrationApproval::all();

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
        try {
            $pendingApproval = array();
            $data = DistributorTypeRegistrationApproval::where('DIST_ID', $request->DIST_ID)
                ->where('APPROVAL_LEVEL_ID', $request->APPROVAL_LEVEL_ID)
                ->where('APPR_GROUP_ID', $request->APPR_GROUP_ID)->first();

            if (!$data) {
                $data =  new DistributorTypeRegistrationApproval;
            }
            $data->DIST_ID = $request->DIST_ID;
            $data->DIST_TYPE_ID = $request->DIST_TYPE_ID;
            $data->APPR_GROUP_ID = $request->APPR_GROUP_ID;
            $data->APPROVAL_LEVEL_ID = $request->APPROVAL_LEVEL_ID;
            $data->APPROVAL_INDEX = $request->APPR_INDEX;
            $data->APPROVAL_STATUS = $request->APPROVAL_STATUS;
            $data->APPROVAL_FIMM_USER = $request->APPROVAL_FIMM_USER;
            $data->APPROVAL_REMARK_PROFILE = $request->APPROVAL_REMARK_PROFILE;
            $data->APPROVAL_REMARK_DETAILINFO = $request->APPROVAL_REMARK_DETAILINFO;
            $data->APPROVAL_REMARK_CEOnDIR = $request->APPROVAL_REMARK_CEOnDIR;
            $data->APPROVAL_REMARK_ARnAAR = $request->APPROVAL_REMARK_ARnAAR;
            $data->APPROVAL_REMARK_ADDTIONALINFO = $request->APPROVAL_REMARK_ADDTIONALINFO;
            $data->APPROVAL_REMARK_PAYMENT = $request->APPROVAL_REMARK_PAYMENT;
            $data->save();



            if ($request->hasFile('APPR_REMARK_DOCU_PROFILE')) {
                $file2 = $request->APPR_REMARK_DOCU_PROFILE;
                $contents1 = $file2->openFile()->fread($file2->getSize());
                $doc = DistributorDocumentRemark::where('DIST_ID', $request->DIST_ID)
                    ->where('DIST_APPR_ID', $data->DIST_APPROVAL_ID)
                    ->where('DOCU_TYPE', 1)->first();
                if (!$doc) {
                    $doc = new DistributorDocumentRemark;
                }
                $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                $doc->DIST_ID = $data->DIST_ID;
                $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                $doc->DOCU_TYPE = 1; // 1 Frofile
                $doc->DOCU_BLOB = $contents1;
                $doc->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc->DOCU_FILESIZE = $file2->getSize();
                $doc->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc->save();
            }

            if ($request->APPR_REMARK_DOCU_DETAILINFO != null && $request->hasFile('APPR_REMARK_DOCU_DETAILINFO')) {
                $file = $request->APPR_REMARK_DOCU_DETAILINFO;
                foreach ($file as $key => $item) {

                    $contents = $item->openFile()->fread($item->getSize());

                    $doc = new DistributorDocumentRemark;
                    $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                    $doc->DOCU_BLOB = $contents;
                    $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                    $doc->DOCU_FILESIZE = $item->getSize();
                    $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                    $doc->save();
                }
            }

            if ($request->APPR_REMARK_DOCU_CEOnDIR != null && $request->hasFile('APPR_REMARK_DOCU_CEOnDIR')) {
                $file = $request->APPR_REMARK_DOCU_CEOnDIR;
                foreach ($file as $key => $item) {

                    $contents = $item->openFile()->fread($item->getSize());

                    $doc = new DistributorDocumentRemark;
                    $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                    $doc->DOCU_BLOB = $contents;
                    $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                    $doc->DOCU_FILESIZE = $item->getSize();
                    $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                    $doc->save();
                }
            }

            if ($request->APPR_REMARK_DOCU_ARnAAR != null && $request->hasFile('APPR_REMARK_DOCU_ARnAAR')) {
                $file = $request->APPR_REMARK_DOCU_ARnAAR;
                foreach ($file as $key => $item) {

                    $contents = $item->openFile()->fread($item->getSize());

                    $doc = new DistributorDocumentRemark;
                    $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                    $doc->DOCU_BLOB = $contents;
                    $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                    $doc->DOCU_FILESIZE = $item->getSize();
                    $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                    $doc->save();
                }
            }

            if ($request->APPR_REMARK_DOCU_ADDITIONALINFO != null && $request->hasFile('APPR_REMARK_DOCU_ADDITIONALINFO')) {
                $file = $request->APPR_REMARK_DOCU_ADDITIONALINFO;
                foreach ($file as $key => $item) {

                    $contents = $item->openFile()->fread($item->getSize());

                    $doc = new DistributorDocumentRemark;
                    $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                    $doc->DOCU_BLOB = $contents;
                    $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                    $doc->DOCU_FILESIZE = $item->getSize();
                    $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                    $doc->save();
                }
            }

            if ($request->APPR_REMARK_DOCU_PAYMENT != null && $request->hasFile('APPR_REMARK_DOCU_PAYMENT')) {
                $file = $request->APPR_REMARK_DOCU_PAYMENT;
                foreach ($file as $key => $item) {

                    $contents = $item->openFile()->fread($item->getSize());

                    $doc = new DistributorDocumentRemark;
                    $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                    $doc->DOCU_BLOB = $contents;
                    $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                    $doc->DOCU_FILESIZE = $item->getSize();
                    $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                    $doc->save();
                }
            }

            if ($request->DOCU_REMARK_LIST = null) {
                $docRemark = $request->DOCU_REMARK_LIST;
                foreach ($docRemark as $item) {
                    $item = json_decode($item);
                    $remark = new DistributorApprovalDocument;
                    $remark->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                    $remark->REQUIRED_DOC_ID = $item->MANAGE_REQUIRED_DOCUMENT_ID;
                    $remark->DOCU_REMARK = $item->DOCU_REMARK;
                    $remark->save();
                }
            }

            if ($request->DOCU_REMARK_LIST2 != null) {
                $docRemark = $request->DOCU_REMARK_LIST2;
                foreach ($docRemark as $item) {
                    $item = json_decode($item);
                    $remark = new DistributorApprovalDocument;
                    $remark->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                    $remark->REQUIRED_DOC_ID = $item->MANAGE_REQUIRED_DOCUMENT_ID;
                    $remark->DOCU_REMARK = $item->DOCU_REMARK;
                    $remark->save();
                }
            }
            if ($request->APPROVAL_STATUS == 14 || $request->APPROVAL_STATUS == 17 || $request->APPROVAL_STATUS == 18 || $request->APPROVAL_STATUS == 3) {
                $dist_id = $request->DIST_ID;
                $appr_index = $request->APPR_INDEX_CURRENT;
                $apprList = json_decode($request->APPR_LIST);
                $dist_type = DistributorType::where('DIST_ID', $dist_id)->select('DIST_TYPE')->first();

                $dataApprovalCurrent = DistributorTypeRegistrationApproval::where('DIST_ID', $request->DIST_ID)
                    ->where('APPROVAL_INDEX', $request->APPR_INDEX_CURRENT)
                    ->whereIn('APPROVAL_DATE', function ($query) use ($dist_id, $appr_index) {
                        return $query->select(DB::raw('max(DA2.APPROVAL_DATE) as APPROVAL_DATE'))
                            ->from('DISTRIBUTOR_APPROVAL AS DA2')
                            ->where('DA2.APPROVAL_INDEX', '=', $appr_index)
                            ->where('DA2.DIST_ID', '=', $dist_id)
                            ->groupBy('DA2.APPROVAL_LEVEL_ID');
                    })
                    ->groupBy('APPROVAL_LEVEL_ID')
                    ->get();

                if (count($dataApprovalCurrent) > 1) {
                    // multiple index approval level
                    foreach ($dataApprovalCurrent as $item) {
                        if ($item->APPROVAL_STATUS == 15 || $item->APPROVAL_STATUS == 9) {
                            $pendingApproval[] = $item;
                        }
                    }
                    if (count($pendingApproval) == 0) {
                        if (count($apprList) == 0) {
                            $dataStatus = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
                            $dataStatus->DIST_ID = $request->DIST_ID;
                            $dataStatus->DIST_APPROVAL_STATUS = $request->APPROVAL_STATUS;
                            $dataStatus->DIST_VALID_STATUS = 1;
                            $dataStatus->save();
                        } else {
                            $dataStatus = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
                            $dataStatus->DIST_ID = $request->DIST_ID;
                            $dataStatus->DIST_APPROVAL_STATUS = $request->APPROVAL_STATUS;
                            $dataStatus->save();
                            foreach (json_decode($request->APPR_LIST) as $item) {

                                $dataApproval = new DistributorTypeRegistrationApproval;
                                $dataApproval->DIST_ID = $request->DIST_ID;
                                $dataApproval->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                                $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                                $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                                $dataApproval->APPROVAL_STATUS = $request->APPROVAL_STATUS_NEXT;
                                $dataApproval->APPROVAL_INDEX = $item->APPR_INDEX;
                                $dataApproval->save();

                                // $notification = new ManageNotification();

                                // $add = $notification->add($dataApproval->APPR_GROUP_ID,1);
                            }
                        }
                    }
                } else {
                    if (count($apprList) == 0) {
                        $dataStatus = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
                        $dataStatus->DIST_ID = $request->DIST_ID;
                        $dataStatus->DIST_APPROVAL_STATUS = $request->APPROVAL_STATUS;
                        $dataStatus->DIST_VALID_STATUS = 1;
                        $dataStatus->save();

                        //Send notification to distributor
                        $notification = new ManageDistributorNotification();
                        $add = $notification->add(0, 2, $request->DIST_ID, $request->NOTI_MESSAGE, $request->NOTI_LOCATION_DIST);
                        $add = $notification->add(3, 2, $request->DIST_ID, $request->NOTI_MESSAGE, $request->NOTI_LOCATION_DIST);
                    } else {
                        $dataStatus = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
                        $dataStatus->DIST_ID = $request->DIST_ID;
                        $dataStatus->DIST_APPROVAL_STATUS = $request->APPROVAL_STATUS;
                        $dataStatus->save();
                        //if single index approval level
                        foreach (json_decode($request->APPR_LIST) as $item) {
                            $dataApproval = DistributorTypeRegistrationApproval::where('DIST_ID', $request->DIST_ID)
                                ->where('APPROVAL_LEVEL_ID', $item->APPROVAL_LEVEL_ID)
                                ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->first();

                            if (!$dataApproval) {
                                $dataApproval =  new DistributorTypeRegistrationApproval;
                            }
                            $dataApproval->DIST_ID = $request->DIST_ID;
                            $dataApproval->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                            $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                            $dataApproval->APPROVAL_STATUS = $request->APPROVAL_STATUS_NEXT;
                            $dataApproval->APPROVAL_INDEX = $item->APPR_INDEX;
                            $dataApproval->save();


                            if ($item->APPR_GROUP_ID == 12) {
                                $request->NOTI_MESSAGE = "(DIST) New Type Registration for LRA approval.";
                                $request->NOTI_LOCATION = "distributor-RegisterType-SubmissionList-lraApproval";
                            } else if ($item->APPR_GROUP_ID == 7) {
                                $request->NOTI_MESSAGE = "(DIST) New Type Registration for SUV approval.";
                                $request->NOTI_LOCATION = "distributor-RegisterType-SubmissionList-supervisionApproval";
                            }


                            if ($request->NOTI_TYPE != null && $request->NOTI_TYPE == "DISTRIBUTOR") {
                                $notification = new ManageDistributorNotification();
                                $add = $notification->add(0, 2, $request->DIST_ID, $request->NOTI_MESSAGE, $request->NOTI_LOCATION_DIST);
                                $add = $notification->add(3, 2, $request->DIST_ID, $request->NOTI_MESSAGE, $request->NOTI_LOCATION_DIST);
                            } else {
                                $notification = new ManageNotification();
                                $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $request->NOTI_MESSAGE, $request->NOTI_LOCATION);
                            }




                            // if ($request->USER_GROUP_ID == 17 || $request->USER_GROUP_ID == 15) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL GM.", "distributor-RegisterType-SubmissionList-gmApproval");
                            // } elseif ($request->USER_GROUP_ID == 28 && $request->APPROVAL_STATUS == 3) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVED BY BOD.", "distributor-profile");
                            // } elseif ($request->USER_GROUP_ID == 1) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL FOR BOD.", "distributor-RegisterType-SubmissionList-boardApproval");
                            // } elseif ($request->USER_GROUP_ID == 2) {
                            //     if ($request->APPROVAL_STATUS == 18) {
                            //         $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL FOR GM.", $request->NOTI_LOCATION);
                            //     }
                            //     if ($item->APPR_GROUP_ID == 12) {
                            //         $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL FOR LRA.", "distributor-RegisterType-SubmissionList-lraApproval");
                            //     } elseif ($item->APPR_GROUP_ID == 7) {
                            //         $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL FOR SUV.", "distributor-RegisterType-SubmissionList-supervisionApproval");
                            //     }
                            // } elseif ($request->USER_GROUP_ID == 3) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL FOR CEO.", "distributor-RegisterType-SubmissionList-ceoApproval");
                            // } elseif ($request->USER_GROUP_ID == 4) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL FOR HOD RD.", "distributor-RegisterType-SubmissionList-HODrdApproval");
                            // } else {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR TYPE APPROVAL.", "dashboard");
                            // }



                            // if ($dataApproval->APP_GROUP_ID == 1){
                            //     $add = $notification->add($dataApproval->APPR_GROUP_ID,1,"Application has been updated","");
                            // }

                            // $add = $notification->add($dataApproval->APPR_GROUP_ID,1,"Application has been updated","");
                        }
                    }
                }
            } else if ($request->APPROVAL_STATUS == 9) {
                $dist_id = $request->DIST_ID;
                $appr_index = $request->APPR_INDEX_CURRENT;
                $apprList = json_decode($request->APPR_LIST);

                $dataApprovalCurrent = DistributorTypeRegistrationApproval::where('DIST_ID', $request->DIST_ID)
                    ->where('APPROVAL_INDEX', $request->APPR_INDEX_CURRENT)
                    ->whereIn('APPROVAL_DATE', function ($query) use ($dist_id, $appr_index) {
                        return $query->select(DB::raw('max(DA2.APPROVAL_DATE) as APPROVAL_DATE'))
                            ->from('DISTRIBUTOR_APPROVAL AS DA2')
                            ->where('DA2.APPROVAL_INDEX', '=', $appr_index)
                            ->where('DA2.DIST_ID', '=', $dist_id)
                            ->groupBy('DA2.APPROVAL_LEVEL_ID');
                    })
                    ->groupBy('APPROVAL_LEVEL_ID')
                    ->get();
            } else {
                $dataStatus = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
                $dataStatus->DIST_ID = $request->DIST_ID;
                $dataStatus->DIST_APPROVAL_STATUS = $request->APPROVAL_STATUS;
                $dataStatus->save();

                // $notification = new ManageNotification();

                // $add = $notification->add($dataApproval->APPR_GROUP_ID,1);
            }

            http_response_code(200);
            return response([
                'message' => 'Data successfully created.'
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to create data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function manage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL_ID' => 'required|integer',
            'DIST_ID' => 'required|integer',
            'DIST_TYPE_ID' => 'required|integer',
            'APPR_GROUP_ID' => 'required|integer',
            'APPROVAL_LEVEL_ID' => 'required|integer',
            'APPROVAL_INDEX' => 'required|integer',
            'APPROVAL_STATUS' => 'required|integer',
            'APPROVAL_USER' => 'required|integer',
            'APPROVAL_FIMM_REMARK' => 'required|string',
            'APPROVAL_2ND_REMARK' => 'required|string',
            'APPROVAL_DATE' => 'required|string'
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
            'DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL_ID' => 'required|integer',
            'DIST_ID' => 'required|integer',
            'DIST_TYPE_ID' => 'required|integer',
            'APPR_GROUP_ID' => 'required|integer',
            'APPROVAL_LEVEL_ID' => 'required|integer',
            'APPROVAL_INDEX' => 'required|integer',
            'APPROVAL_STATUS' => 'required|integer',
            'APPROVAL_USER' => 'required|integer',
            'APPROVAL_FIMM_REMARK' => 'required|string',
            'APPROVAL_2ND_REMARK' => 'required|string',
            'APPROVAL_DATE' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            $data = DistributorTypeRegistrationApproval::where('id', $id)->first();
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
            $data = DistributorTypeRegistrationApproval::find($id);
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
            'DISTRIBUTOR_TYPE_REGISTRATION_APPROVAL_ID' => 'required|integer',
            'DIST_ID' => 'required|integer',
            'DIST_TYPE_ID' => 'required|integer',
            'APPR_GROUP_ID' => 'required|integer',
            'APPROVAL_LEVEL_ID' => 'required|integer',
            'APPROVAL_INDEX' => 'required|integer',
            'APPROVAL_STATUS' => 'required|integer',
            'APPROVAL_USER' => 'required|integer',
            'APPROVAL_FIMM_REMARK' => 'required|string',
            'APPROVAL_2ND_REMARK' => 'required|string',
            'APPROVAL_DATE' => 'required|string'
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
