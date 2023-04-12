<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\DistributorApproval;
use App\Models\DistributorStatus;
use App\Models\DistributorApprovalDocument;
use App\Models\DistributorDocumentRemark;
use App\Models\DistributorType;
use GuzzleHttp\Exception\RequestException;
use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use Validator;
use DB;

class DistributorApprovalController extends Controller
{
    public function sendEmailNotification(Request $request)
    {
        try {
            $url = env('URL_SERVER') . '/api/module0/send_dist_reg_email';

            $DIST_EMAIL = (string)$request->EMAIL;
            $DIST_NAME = (string)$request->DIST_NAME;
            $DIST_REMARK = (string)$request->DIST_REMARK;
            // $response = Curl::to('http://fimmserv_module0/api/module0/send_email')
            // $response = Curl::to('http://localhost:7000/api/module0/send_dist_reg_email')
            //$response = Curl::to('http://192.168.3.24/api/module0/send_dist_reg_email')
            $response =  Curl::to($url)
                ->withData(['email' => $DIST_EMAIL, 'distName' => $DIST_NAME, 'distRemark' => $DIST_REMARK])
                ->returnResponseObject()
                ->post();

            $content = json_decode($response->content);

            if ($response->status != 200) {
                http_response_code(400);

                return response([
                    'message' => 'Failed to send email: ' . $content,
                    'errorCode' => 4100
                ], 400);
            } else {
                return response([

                    'message' => 'Email notification has been sent to Admin Distributor',
                ]);
            }

            // http_response_code(200);
            // return response([
            //     'message' => 'Data successfully updated.'
            // ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4100
            ], 400);
        }
    }
    public function get(Request $request)
    {
        try {
            $data = DistributorApproval::find($request->DISTRIBUTOR_APPROVAL_ID);

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

    public function getDistributorApplicationList(Request $request)
    {
        try {
            //return $request->all();
            $newData = array();
            $group_id = $request->APPR_GROUP_ID;
            //DB::enableQueryLog();
            $data = DB::table('distributor_management.DISTRIBUTOR_APPROVAL AS DA')
                ->select('*', 'task_status2.TS_PARAM AS TS_PARAM_MAIN', 'task_status.TS_PARAM AS TS_PARAM', 'task_status.TS_PARAM AS TS_PARAM_R', 'USER.USER_NAME', 'DA.APPROVAL_DATE AS LATEST_DATE', 'dist.CREATE_TIMESTAMP as SUBMISSION_DATE', 'TS.TS_PARAM as PAYMENT_STATUS')
                ->leftJoin('admin_management.TASK_STATUS AS task_status', 'task_status.TS_ID', '=', 'DA.APPROVAL_STATUS')
                ->leftJoin('distributor_management.DISTRIBUTOR AS dist', 'dist.DISTRIBUTOR_ID', '=', 'DA.DIST_ID')
                ->leftJoin('admin_management.APPROVAL_LEVEL AS appr_level', 'appr_level.APPROVAL_LEVEL_ID', '=', 'DA.APPROVAL_LEVEL_ID')
                ->leftJoin('distributor_management.DISTRIBUTOR_STATUS AS dist_status', 'dist_status.DIST_ID', '=', 'DA.DIST_ID')
                ->leftJoin('admin_management.TASK_STATUS AS task_status2', 'task_status2.TS_ID', '=', 'dist_status.DIST_APPROVAL_STATUS')
                ->leftJoin('admin_management.USER', 'USER.USER_ID', '=', 'DA.APPROVAL_FIMM_USER')

                ->leftJoin('finance_management.TRANSACTION_LEDGER as TL', 'TL.DISTRIBUTOR_ID', 'DA.DIST_ID')
                ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', 'TL.TRANS_STATUS')


                ->where('DA.APPR_GROUP_ID', '=', $request->APPR_GROUP_ID)

                ->whereIn('DA.APPROVAL_DATE', function ($query) use ($group_id) {
                    return $query->select(DB::raw('max(DA2.APPROVAL_DATE) as APPROVAL_DATE'))
                        ->from('distributor_management.DISTRIBUTOR_APPROVAL AS DA2')
                        ->where('DA2.APPR_GROUP_ID', '=', $group_id)
                        ->groupBy('DA2.DIST_ID');
                })
                // ->whereIn('DA.APPROVAL_STATUS', ['15', '14', '2', '1'])

                //->groupBy('DA.DIST_ID')
                ->orderBy('DA.APPROVAL_DATE', 'DESC')
                ->orderByRaw("FIELD(DA.APPROVAL_STATUS ,'15', '14', '2','1') DESC");

            // filters
            if ($params = $request->get('filters')) {
                $filters = json_decode($params, true);

                if (array_key_exists('DIST_NAME', $filters) && $filters['DIST_NAME']) {
                    $data->where('dist.DIST_NAME', 'LIKE', '%' . $filters['DIST_NAME'] . '%');
                }

                if (array_key_exists('REG_NUM', $filters) && $filters['REG_NUM']) {
                    $data->where('dist.DIST_REGI_NUM1', 'LIKE', '%' . $filters['REG_NUM'] . '%');
                }

                if (array_key_exists('NEW_REG_NUM', $filters) && $filters['NEW_REG_NUM']) {
                    $data->where('dist.DIST_REGI_NUM2', 'LIKE', '%' . $filters['NEW_REG_NUM'] . '%');
                }
                if (array_key_exists('STATUS', $filters) && $filters['STATUS']) {
                    $data->where('task_status.TS_PARAM', 'LIKE', '%' . $filters['STATUS'] . '%');
                }
            }


            $data = $data->get();
            //    dd(DB::getQueryLog());
            // return $data;
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
                $item->SUBMISSION_DATE = date('d-m-Y', strtotime($item->SUBMISSION_DATE));
                $item->PAYMENT_STATUS = $item->PAYMENT_STATUS ?? '-';

                if ($item->APPR_INDEX == 1) {
                    $item->TS_PARAM = $item->TS_PARAM_MAIN;
                    $newData[] = $item;
                } else {
                    $dataAppr = DistributorApproval::where('APPROVAL_INDEX', 1)->first();
                    if ($dataAppr->APPR_GROUP_ID == $item->APPR_GROUP_ID) {
                        $item->TS_PARAM = $item->TS_PARAM_MAIN;
                        $newData[] = $item;
                    } else {
                        if ($item->APPROVAL_STATUS == 15 || $item->APPROVAL_STATUS == 1 || $item->APPROVAL_STATUS == 14 || $item->APPROVAL_STATUS == 17 || $item->APPROVAL_STATUS == 18 || $item->APPROVAL_STATUS == 3 || $item->APPROVAL_STATUS == 7 || $item->APPROVAL_STATUS == 9) { //pending & REVIEWED
                            $newData[] = $item;
                        }
                    }
                }
            }
            // dd($newData);
            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => $newData
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
            $data = DistributorApproval::all();

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
        // return $request->all();
        try {
            $pendingApproval = array();
            $data = DistributorApproval::where('DIST_ID', $request->DIST_ID)
                ->where('APPROVAL_LEVEL_ID', $request->APPROVAL_LEVEL_ID)
                ->where('APPR_GROUP_ID', $request->APPR_GROUP_ID)->first();
            if (!$data) {
                $data =  new DistributorApproval;
            }
            $data->DIST_ID = $request->DIST_ID;
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


            // dd($request->APPR_REMARK_DOCU_PROFILE);
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



            if ($request->hasFile('APPR_REMARK_DOCU_DETAILINFO')) {
                $file2 = $request->APPR_REMARK_DOCU_DETAILINFO;
                $contents1 = $file2->openFile()->fread($file2->getSize());

                $doc = DistributorDocumentRemark::where('DIST_ID', $request->DIST_ID)
                    ->where('DIST_APPR_ID', $data->DIST_APPROVAL_ID)
                    ->where('DOCU_TYPE', 2)->first();
                if (!$doc) {
                    $doc = new DistributorDocumentRemark;
                }
                $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                $doc->DIST_ID = $data->DIST_ID;
                $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                $doc->DOCU_TYPE = 2; // 1 Frofile
                $doc->DOCU_BLOB = $contents1;
                $doc->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc->DOCU_FILESIZE = $file2->getSize();
                $doc->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc->save();
                // foreach ($file as $key => $item) {
                //     $contents2 = $item->openFile()->fread($item->getSize());
                //     $doc = new DistributorDocumentRemark;
                //     $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                //     $doc->DIST_ID = $data->DIST_ID;
                //     $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                //     $doc->DOCU_TYPE = 2; // 1 Frofile
                //     $doc->DOCU_BLOB = $contents2;
                //     $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                //     $doc->DOCU_FILESIZE = $item->getSize();
                //     $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                //     $doc->save();
                // }
            }



            if ($request->hasFile('APPR_REMARK_DOCU_CEOnDIR')) {
                $file2 = $request->APPR_REMARK_DOCU_CEOnDIR;
                $contents1 = $file2->openFile()->fread($file2->getSize());

                $doc = DistributorDocumentRemark::where('DIST_ID', $request->DIST_ID)
                    ->where('DIST_APPR_ID', $data->DIST_APPROVAL_ID)
                    ->where('DOCU_TYPE', 3)->first();

                if (!$doc) {
                    $doc = new DistributorDocumentRemark;
                }
                $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                $doc->DIST_ID = $data->DIST_ID;
                $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                $doc->DOCU_TYPE = 3; // 1 Frofile
                $doc->DOCU_BLOB = $contents1;
                $doc->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc->DOCU_FILESIZE = $file2->getSize();
                $doc->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc->save();
                // foreach ($file as $key => $item) {
                //     $contents3 = $item->openFile()->fread($item->getSize());
                //     $doc = new DistributorDocumentRemark;
                //     $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                //     $doc->DIST_ID = $data->DIST_ID;
                //     $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                //     $doc->DOCU_TYPE = 3; // 3 CEO DIR
                //     $doc->DOCU_BLOB = $contents3;
                //     $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                //     $doc->DOCU_FILESIZE = $item->getSize();
                //     $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                //     $doc->save();
                // }
            }


            if ($request->hasFile('APPR_REMARK_DOCU_ARnAAR')) {
                $file2 = $request->APPR_REMARK_DOCU_ARnAAR;
                $contents4 = $file2->openFile()->fread($file2->getSize());

                $doc = DistributorDocumentRemark::where('DIST_ID', $request->DIST_ID)
                    ->where('DIST_APPR_ID', $data->DIST_APPROVAL_ID)
                    ->where('DOCU_TYPE', 4)->first();
                if (!$doc) {
                    $doc = new DistributorDocumentRemark;
                }
                $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                $doc->DIST_ID = $data->DIST_ID;
                $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                $doc->DOCU_TYPE = 4; // 1 Frofile
                $doc->DOCU_BLOB = $contents4;
                $doc->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc->DOCU_FILESIZE = $file2->getSize();
                $doc->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc->save();
                // foreach ($file2 as $key => $item) {
                //     $contents4 = $item->openFile()->fread($item->getSize());
                //     $doc = new DistributorDocumentRemark;
                //     $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                //     $doc->DIST_ID = $data->DIST_ID;
                //     $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                //     $doc->DOCU_TYPE = 4; // 4 AR / AAR Info
                //     $doc->DOCU_BLOB = $contents4;
                //     $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                //     $doc->DOCU_FILESIZE = $item->getSize();
                //     $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                //     $doc->save();
                // }
            }

            if ($request->hasFile('APPR_REMARK_DOCU_ADDITIONALINFO')) {
                $file2 = $request->APPR_REMARK_DOCU_ADDITIONALINFO;
                $contents5 = $file2->openFile()->fread($file2->getSize());

                $doc = DistributorDocumentRemark::where('DIST_ID', $request->DIST_ID)
                    ->where('DIST_APPR_ID', $data->DIST_APPROVAL_ID)
                    ->where('DOCU_TYPE', 5)->first();
                if (!$doc) {
                    $doc = new DistributorDocumentRemark;
                }
                $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                $doc->DIST_ID = $data->DIST_ID;
                $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                $doc->DOCU_TYPE = 5; // 1 Frofile
                $doc->DOCU_BLOB = $contents5;
                $doc->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc->DOCU_FILESIZE = $file2->getSize();
                $doc->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc->save();
                // foreach ($file2 as $key => $item) {
                //     $contents5 = $item->openFile()->fread($item->getSize());
                //     $doc = new DistributorDocumentRemark;
                //     $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                //     $doc->DIST_ID = $data->DIST_ID;
                //     $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                //     $doc->DOCU_TYPE = 5; // 5 Additional Info
                //     $doc->DOCU_BLOB = $contents5;
                //     $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                //     $doc->DOCU_FILESIZE = $item->getSize();
                //     $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                //     $doc->save();
                // }
            }

            if ($request->hasFile('APPR_REMARK_DOCU_PAYMENT')) {
                $file2 = $request->APPR_REMARK_DOCU_PAYMENT;
                $contents6 = $file2->openFile()->fread($file2->getSize());

                $doc = DistributorDocumentRemark::where('DIST_ID', $request->DIST_ID)
                    ->where('DIST_APPR_ID', $data->DIST_APPROVAL_ID)
                    ->where('DOCU_TYPE', 6)->first();

                if (!$doc) {
                    $doc = new DistributorDocumentRemark;
                }
                $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                $doc->DIST_ID = $data->DIST_ID;
                $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                $doc->DOCU_TYPE = 6; // 1 Frofile
                $doc->DOCU_BLOB = $contents6;
                $doc->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc->DOCU_FILESIZE = $file2->getSize();
                $doc->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc->save();
                // foreach ($file2 as $key => $item) {
                //     $contents6 = $item->openFile()->fread($item->getSize());
                //     $doc = new DistributorDocumentRemark;
                //     $doc->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                //     $doc->DIST_ID = $data->DIST_ID;
                //     $doc->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                //     $doc->DOCU_TYPE = 6; // 6 Payment Info
                //     $doc->DOCU_BLOB = $contents6;
                //     $doc->DOCU_ORIGINAL_NAME = $item->getClientOriginalName();
                //     $doc->DOCU_FILESIZE = $item->getSize();
                //     $doc->DOCU_FILETYPE = $item->getClientOriginalExtension();
                //     $doc->save();
                // }
            }

            //DB::enableQueryLog();
            if ($request->DOCU_REMARK_LIST != null) {
                $docRemark = json_decode($request->DOCU_REMARK_LIST, true);
                foreach ($docRemark as $item) {
                    foreach ($item['list'] ?? [] as $list) {
                        $remark = DistributorApprovalDocument::where('DIST_APPR_DOC_ID', $list['DIST_APPR_DOC_ID'])->first();
                        if (!$remark) {
                            $remark = new DistributorApprovalDocument;
                        }
                        $remark->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
                        $remark->DIST_ID = $data->DIST_ID;
                        $remark->DIST_TYPE_ID = $request->DIST_TYPE_ID;
                        $remark->REQUIRED_DOC_ID = $list['MANAGE_REQUIRED_DOCUMENT_ID'] ?? '';
                        $remark->DOCU_REMARK = $list['PROPOSAL_REMARK'] ?? '';
                        $remark->save();
                    }
                }
            }

            // if ($request->DOCU_REMARK_LIST2 != null) {
            //     $docRemark = $request->DOCU_REMARK_LIST2;
            //     foreach ($docRemark as $item) {
            //         $item = json_decode($item);
            //         $remark = new DistributorApprovalDocument;
            //         $remark->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
            //         $remark->DIST_ID = $data->DIST_ID;
            //         $remark->DIST_TYPE_ID = $request->DIST_TYPE_ID;
            //         $remark->REQUIRED_DOC_ID = $item->MANAGE_REQUIRED_DOCUMENT_ID;
            //         $remark->DOCU_REMARK = $item->DOCU_REMARK;
            //         $remark->save();
            //     }
            // }
            // dd(DB::getQueryLog());
            //reviewed / approved process
            if ($request->APPROVAL_STATUS == 14 || $request->APPROVAL_STATUS == 17 || $request->APPROVAL_STATUS == 18 || $request->APPROVAL_STATUS == 3) {
                $dist_id = $request->DIST_ID;
                $appr_index = $request->APPR_INDEX_CURRENT;
                $apprList = json_decode($request->APPR_LIST);
                $dist_type = DistributorType::where('DIST_ID', $dist_id)->select('DIST_TYPE')->first();

                //Approved the user after BOD Approved
                if (($request->USER_GROUP_ID == 28) || ($request->USER_GROUP_ID == 1 && $request->DIST_TYPE != "UTMC")) {
                    $user = User::where('USER_DIST_ID', $request->DIST_ID)->first();
                    $user->USER_GROUP = 3;
                    $user->save();
                }

                $dataApprovalCurrent = DistributorApproval::where('DIST_ID', $request->DIST_ID)
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
                        if (count($apprList) == 0 || ($request->USER_GROUP_ID == 1 && $request->DIST_TYPE != "UTMC")) {
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
                                $dataApproval = new DistributorApproval;
                                $dataApproval->DIST_ID = $request->DIST_ID;
                                $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                                $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                                $dataApproval->APPROVAL_STATUS = $request->APPROVAL_STATUS_NEXT;
                                $dataApproval->APPROVAL_INDEX = $item->APPR_INDEX;
                                $dataApproval->save();

                                $notification = new ManageNotification();
                                $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) DISTRIBUTOR APPROVAL FOR RD.", "distributor-SubmissionList-rdApproval");
                            }
                        }
                    }
                } else {
                    if (count($apprList) == 0 ||  ($request->USER_GROUP_ID == 1 && $request->DIST_TYPE != "UTMC")) {
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
                            $dataApproval = DistributorApproval::where('DIST_ID', $request->DIST_ID)
                                ->where('APPROVAL_LEVEL_ID', $item->APPROVAL_LEVEL_ID)
                                ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->first();
                            if (!$dataApproval) {
                                $dataApproval =  new DistributorApproval;
                            }
                            $dataApproval->DIST_ID = $request->DIST_ID;
                            $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                            $dataApproval->APPROVAL_STATUS = $request->APPROVAL_STATUS_NEXT;
                            $dataApproval->APPROVAL_INDEX = $item->APPR_INDEX;
                            $dataApproval->save();

                            // $notification = new ManageNotification();
                            // if ($dataApproval->APP_GROUP_ID == 1){
                            //     $add = $notification->add($dataApproval->APPR_GROUP_ID,1,"Application has been updated","");
                            // }

                            //FOR LRA MESSAGE & NOTI_LOCATION
                            if ($item->APPR_GROUP_ID == 12) {
                                $request->NOTI_MESSAGE = "(DIST) New registration for LRA approval.";
                                $request->NOTI_LOCATION = "distributor-SubmissionList-lraApproval";
                            } else if ($item->APPR_GROUP_ID == 7) {
                                $request->NOTI_MESSAGE = "(DIST) New registration for SUV approval.";
                                $request->NOTI_LOCATION = "distributor-SubmissionList-supervisionApproval";
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
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR FOR APPROVAL GM.", "distributor-SubmissionList-gmApproval");
                            // } elseif ($request->USER_GROUP_ID == 28 && $request->APPROVAL_STATUS == 3) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) DISTRIBUTOR APPROVED BY BOD.", "distributor-details-registration");
                            // } elseif ($request->USER_GROUP_ID == 1) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR APPROVAL FOR BOD.", "distributor-SubmissionList-boardApproval");
                            // } elseif ($request->USER_GROUP_ID == 2) {
                            //     if ($request->APPROVAL_STATUS == 18) {
                            //         $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR APPROVAL FOR GM.", $request->NOTI_LOCATION);
                            //     }
                            //     if ($item->APPR_GROUP_ID == 12) {
                            //         $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR APPROVAL FOR SUV.", "distributor-SubmissionList-lraApproval");
                            //     } elseif ($item->APPR_GROUP_ID == 7) {
                            //         $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR APPROVAL FOR SUV.", "distributor-SubmissionList-supervisionApproval");
                            //     }
                            // } elseif ($request->USER_GROUP_ID == 3) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR FOR APPROVAL CEO.", "distributor-SubmissionList-ceoApproval");
                            // } elseif ($request->USER_GROUP_ID == 4) {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR FOR APPROVAL HOD RD.", "distributor-SubmissionList-HODrdApproval");
                            // } else {
                            //     $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) NEW DISTRIBUTOR FOR APPROVAL.", "dashboard");
                            // }


                            // $add = $notification->add($dataApproval->APPR_GROUP_ID,1,"Application has been updated","");
                        }
                    }
                }
                // return process
            } elseif ($request->APPROVAL_STATUS == 9) {
                $dist_id = $request->DIST_ID;
                $appr_index = $request->APPR_INDEX_CURRENT;
                $apprList = json_decode($request->APPR_LIST);

                $dataApprovalCurrent = DistributorApproval::where('DIST_ID', $request->DIST_ID)
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

                //Update the status of RD to RETURNED
                $rDApproval = DistributorApproval::where('DIST_ID', $request->DIST_ID)
                    ->where('APPROVAL_INDEX', 1)
                    ->where('APPR_GROUP_ID', 5)
                    ->first();
                $rDApproval->APPROVAL_STATUS = 7;
                $rDApproval->save();


                //Sending notification to the RD level for Return status;
                foreach (json_decode($request->APPR_LIST) as $item) {
                    $notification = new ManageNotification();
                    $distributor = Distributor::where('DISTRIBUTOR_ID', $request->DIST_ID)->first();
                    $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DISTRIBUTOR) " . $distributor->DIST_CODE . " Distributor has been returned. ", "distributor-SubmissionList-rdApproval");
                }
            } elseif ($request->APPROVAL_STATUS == 7 || $request->APPROVAL_STATUS == 8) {
                $dist_temp_id = $request->DIST_TEMP_ID;
                $dist_id = $request->DIST_ID;
                $appr_index = $request->APPR_INDEX;

                if ($request->USER_TYPE == "SECONDLEVEL") {
                    $dataUpdateApproval = new DistributorApproval;
                    $dataUpdateApproval->DIST_TEMP_ID = $request->DIST_TEMP_ID;
                    $dataUpdateApproval->DIST_ID = $request->DIST_ID;
                    $dataUpdateApproval->APPR_GROUP_ID = 5;
                    $dataUpdateApproval->APPROVAL_LEVEL_ID = 1;
                    $dataUpdateApproval->APPROVAL_STATUS = 7;
                    $dataUpdateApproval->APPROVAL_INDEX = 2;
                    $dataUpdateApproval->save();
                    $notification = new DistributorApproval();
                    // $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $returnSubmission->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    $add = $notification->add(1, 2, $request->DIST_ID, "(DIST) NEW DISTRIBUTOR HAS BEEN RETURNED", "distributor-UpdateDetails-SubmissionList-secondApproval");
                    $add = $notification->add(3, 2, $request->DIST_ID, "(DIST) NEW DISTRIBUTOR HAS BEEN RETURNED", "distributor-UpdateDetails-SubmissionList-secondApproval");
                } elseif ($request->USER_TYPE == "RD") {
                    // RD Return to distributor----
                    $dataUpdateApproval = DistributorApproval::where('DIST_ID', $request->DIST_ID)
                        ->where('APPR_GROUP_ID', $request->APPR_GROUP_ID)
                        ->where('APPROVAL_LEVEL_ID', $request->APPROVAL_LEVEL_ID)->first();
                    // $dataUpdateApproval = new DistributorApproval;
                    // $dataUpdateApproval->DIST_TEMP_ID = $request->DIST_TEMP_ID;
                    // $dataUpdateApproval->DIST_ID = $request->DIST_ID;
                    // $dataUpdateApproval->APPR_GROUP_ID = 4;
                    // $dataUpdateApproval->APPROVAL_LEVEL_ID = 1;
                    $dataUpdateApproval->APPROVAL_STATUS = 7;
                    //$dataUpdateApproval->APPROVAL_INDEX = 2;
                    $dataUpdateApproval->save();

                    $dataStatus = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
                    $dataStatus->DIST_PUBLISH_STATUS = 0;
                    $dataStatus->DIST_APPROVAL_STATUS = 7;
                    $dataStatus->save();

                    $notification = new ManageDistributorNotification();
                    // $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $returnSubmission->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    $add = $notification->add(0, 2, $request->DIST_ID, "(DIST) NEW DISTRIBUTOR HAS BEEN RETURNED", "distributor-details-registration");
                    $add = $notification->add(3, 2, $request->DIST_ID, "(DIST) NEW DISTRIBUTOR HAS BEEN RETURNED", "distributor-details-registration");
                    // $add = $notification->add(3, 2, $request->DIST_ID, "(DIST) NEW DISTRIBUTOR HAS BEEN RETURNED", "distributor-UpdateDetails-SubmissionList-secondApproval");

                }
            } else {
                $dataStatus = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
                $dataStatus->DIST_ID = $request->DIST_ID;
                $dataStatus->DIST_APPROVAL_STATUS = $request->APPROVAL_STATUS;
                $dataStatus->save();


                $notification = new ManageNotification();
                //$add = $notification->add(4, 1, "(DISTRIBUTOR) NEW DISTRIBUTOR REGISTRATION ENTRY", "distributor-SubmissionList-rdApproval");
                // $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
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
            'DIST_ID' => 'required|integer',
            'DIST_APPR_GROUP_ID' => 'integer|nullable',
            'APPROVAL_ID' => 'integer|nullable'
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
            'DIST_APPR_GROUP_ID' => 'integer|nullable',
            'APPROVAL_ID' => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            $data = DistributorApproval::where('id', $id)->first();
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
            $data = DistributorApproval::find($id);
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
        // $validator = Validator::make($request->all(), [
        //   $request->all(), [
        // 	'DIST_ID' => 'required|integer',
        // 	'DIST_APPR_GROUP_ID' => 'integer|nullable',
        // 	'APPROVAL_ID' => 'integer|nullable'
        //   ]]);
        // if ($validator->fails()) {
        //     http_response_code(400);
        //     return response([
        //         'message' => 'Data validation error.',
        //         'errorCode' => 4106
        //     ],400);
        // }
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
