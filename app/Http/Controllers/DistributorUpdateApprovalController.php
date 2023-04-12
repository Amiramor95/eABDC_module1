<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use App\Models\DistributorUpdateApproval;
use App\Models\DistributorTempDocumentRemark;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use App\Http\Controllers\Controller;
use App\Models\DistributorTemp;
use App\Models\DistributorTempAdditionalInfo;
use App\Models\DistributorTempAddress;
use App\Models\DistributorTempContact;
use App\Models\DistributorTempDetailInfo;
use App\Models\DistributorTempDirector;
use App\Models\DistributorTempRepresentative;
use App\Models\DistributorTempDocument;


use App\Models\Distributor;
use App\Models\DistributorAdditionalInfo;
use App\Models\DistributorAddress;
use App\Models\DistributorContact;
use App\Models\DistributorDetailInfo;
use App\Models\DistributorDirector;
use App\Models\DistributorRepresentative;
use App\Models\DistributorDocument;
use App\Models\ProcessFlow;

use App\Manager\DistributorUpdateMainDatas;

use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use Validator;
use DB;

class DistributorUpdateApprovalController extends Controller
{
    private $distributorUpdateMainData;

    public function __construct(DistributorUpdateMainDatas $distributorUpdateMainData)
    {
        $this->distributorUpdateMainData = $distributorUpdateMainData;
    }

    public function get(Request $request)
    {
        try {
            $data = DistributorUpdateApproval::find($request->DISTRIBUTOR_UPDATE_APPROVAL_ID);

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

    public function getDistributorUpdateList(Request $request)
    {
        try {
            $data = DB::table('distributor_management.DISTRIBUTOR_TEMP AS DT')
                ->select('*', 'DT.CREATE_TIMESTAMP AS SUBMISSION_DATE')
                ->leftJoin('distributor_management.DISTRIBUTOR AS DIST', 'DIST.DISTRIBUTOR_ID', '=', 'DT.DIST_ID')
                ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', '=', 'DT.TS_ID')
                ->where('DT.DIST_ID', $request->DISTRIBUTOR_ID)
                ->where('DT.TS_ID', '!=', 0)
                ->where('DT.DIST_TEMP_CATEGORY', 1) // 1: UPFATE PROFILE 2:REGISTER NEW LICENSE
                ->orderBy('DT.CREATE_TIMESTAMP', 'desc')
                ->get();

            foreach ($data as $item) {
                if ($item->TS_ID == 15 || $item->TS_ID == 6) { // 15=Reviewed by Dist Manager but Pending for Approval by RD. / 6=Draft by RD
                    $item->TS_PARAM = 'REVIEWED';
                } else if ($item->TS_ID == 7) { // Returned by FIMM
                    $item->TS_PARAM = 'RETURNED BY FIMM';
                } else if ($item->TS_ID == 42) { // Rejected by FIMM
                    $item->TS_PARAM = 'REJECTED BY FIMM';
                }

                //Converting Date
                $item->SUBMISSION_DATE = date('d-M-Y', strtotime($item->SUBMISSION_DATE));
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

    public function getDistributorUpdateListRD(Request $request)
    {
        try {
            $data = DB::table('distributor_management.DISTRIBUTOR_TEMP AS DT')
                ->select('*', 'DT.CREATE_TIMESTAMP AS SUBMISSION_DATE')
                ->leftJoin('distributor_management.DISTRIBUTOR AS DIST', 'DIST.DISTRIBUTOR_ID', '=', 'DT.DIST_ID')
                ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', '=', 'DT.TS_ID')
                ->where('DT.DIST_TEMP_CATEGORY', 1) // 1: UPFATE PROFILE 2:REGISTER NEW LICENSE
                ->whereIn("DT.TS_ID", [15, 6, 3, 7, 42]) //Pending for Approval by RD / Draft by RD / Approved by RD / Returned by RD /Rejected by RD
                ->orderBy('DT.CREATE_TIMESTAMP', 'desc')
                ->get();

            foreach ($data as $item) {
                if ($item->TS_ID == 6) {
                    $item->TS_PARAM = 'DRAFT';
                }

                //Converting Date
                $item->SUBMISSION_DATE = date('d-M-Y', strtotime($item->SUBMISSION_DATE));
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

    public function getAll()
    {
        try {
            $data = DistributorUpdateApproval::all();

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

    public function getReviewRemark(Request $request)
    {
        try {
            DB::enableQueryLog();
            $data = DistributorUpdateApproval::where(['DIST_TEMP_ID' => $request->DIST_TEMP_ID, 'APPR_GROUP_ID' => $request->USER_GROUP_ID])
                ->orderBy('APPROVAL_DATE', 'desc')
                ->first();

            $dataDoc = DistributorTempDocumentRemark::where(
                ['DIST_TEMP_ID' => $data->DIST_TEMP_ID, 'DIST_UPDATE_APPROVAL_ID' => $data->DISTRIBUTOR_UPDATE_APPROVAL_ID]
            )
                ->get();

            foreach ($dataDoc as $element) {
                $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
            };

            $data->REMARK_DOC = $dataDoc;

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
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
        DB::enableQueryLog();
        DB::beginTransaction();
        try {
            //$pendingApproval = array();

            //Update Data for Approval by 2nd Level
            $inputs = $request->all();
            $processFlow = ProcessFlow::where(['PROCESS_FLOW_NAME' => 'UPDATE DISTRIBUTOR DETAILS - A(1)(b)'])->first();

            $distributorApproval = DistributorTemp::where(['DIST_ID' => $request->DIST_ID, 'DIST_TEMP_ID' => $request->DIST_TEMP_ID])->first();
            $distributorApproval->TS_ID = $request->APPROVAL_STATUS;
            if (!$distributorApproval->save()) throw new \ErrorException('Failed to save Distributor approval data');

            if ($request->APPROVAL_STATUS == 1) { //IF Save as Draft
                //Create Approval Log Data
                $updateDistributorProfileapproval = DistributorUpdateApproval::updateOrCreate(
                    [
                        'DIST_TEMP_ID' => $request->DIST_TEMP_ID,
                        'DIST_ID' => $request->DIST_ID,
                        'APPR_GROUP_ID' => $request->APPR_GROUP_ID,
                        'APPROVAL_STATUS' => $request->APPROVAL_STATUS,
                    ],
                    [
                        'APPROVAL_LEVEL_ID' => $processFlow->PROCESS_FLOW_ID ?? null,
                        'APPROVAL_INDEX' => $request->APPR_INDEX,
                        'APPROVAL_USER' => $request->APPROVAL_USER,
                        'APPROVAL_REMARK_PROFILE' => $request->APPROVAL_REMARK_PROFILE,
                        'APPROVAL_REMARK_DETAILINFO' => $request->APPROVAL_REMARK_DETAILINFO,
                        'APPROVAL_REMARK_CEOnDIR' => $request->APPROVAL_REMARK_CEOnDIR,
                        'APPROVAL_REMARK_ARnAAR' => $request->APPROVAL_REMARK_ARnAAR,
                        'APPROVAL_REMARK_ADDTIONALINFO' => $request->APPROVAL_REMARK_ADDTIONALINFO,
                        'APPROVAL_REMARK_PAYMENT' => $request->APPROVAL_REMARK_PAYMENT,
                    ]
                );
            } else if ($request->APPROVAL_STATUS == 8) { //Returned
                $distributorApproval->PUBLISH_STATUS = 0;
                if (!$distributorApproval->save()) throw new \ErrorException('Failed to save Distributor approval data');

                //Create Approval Log Data
                $updateDistributorProfileapproval = DistributorUpdateApproval::create(
                    [
                        'DIST_TEMP_ID' => $request->DIST_TEMP_ID,
                        'DIST_ID' => $request->DIST_ID,
                        'APPR_GROUP_ID' => $request->APPR_GROUP_ID,
                        'APPROVAL_LEVEL_ID' => $processFlow->PROCESS_FLOW_ID ?? null,
                        'APPROVAL_INDEX' => $request->APPR_INDEX,
                        'APPROVAL_STATUS' => $request->APPROVAL_STATUS,
                        'APPROVAL_USER' => $request->APPROVAL_USER,
                        'APPROVAL_REMARK_PROFILE' => $request->APPROVAL_REMARK_PROFILE,
                        'APPROVAL_REMARK_DETAILINFO' => $request->APPROVAL_REMARK_DETAILINFO,
                        'APPROVAL_REMARK_CEOnDIR' => $request->APPROVAL_REMARK_CEOnDIR,
                        'APPROVAL_REMARK_ARnAAR' => $request->APPROVAL_REMARK_ARnAAR,
                        'APPROVAL_REMARK_ADDTIONALINFO' => $request->APPROVAL_REMARK_ADDTIONALINFO,
                        'APPROVAL_REMARK_PAYMENT' => $request->APPROVAL_REMARK_PAYMENT,
                    ]
                );

                $processFlow = ProcessFlow::where(['PROCESS_FLOW_NAME' => 'UPDATE DISTRIBUTOR DETAILS - A(1)(b)'])->first();
                //Send Notification to Distributor Manager
                $data = [
                    'group_id' => 3, //Approval Group ID for ADMIN OF DISTRIBUTOR in am/DISTRIBUTOR_MANAGE_GROUP
                    'flow_id' => $processFlow->PROCESS_FLOW_ID ?? null,
                    'distributor_id' => $request->DIST_ID,
                    'status_id' => 0,
                    'remark' => '(DISTRIBUTOR UPDATE PROFILE) Returned by Manager',
                    'vue_url' => 'distributor-profile-update',
                ];
                $notification = new ManageDistributorNotification();
                $notification->add($data['group_id'], $data['flow_id'], $data['distributor_id'], $data['remark'], $data['vue_url']);
            } else {
                //Create Approval Log Data
                $updateDistributorProfileapproval = DistributorUpdateApproval::create(
                    [
                        'DIST_TEMP_ID' => $request->DIST_TEMP_ID,
                        'DIST_ID' => $request->DIST_ID,
                        'APPR_GROUP_ID' => $request->APPR_GROUP_ID,
                        'APPROVAL_LEVEL_ID' => $processFlow->PROCESS_FLOW_ID ?? null,
                        'APPROVAL_INDEX' => $request->APPR_INDEX,
                        'APPROVAL_STATUS' => $request->APPROVAL_STATUS,
                        'APPROVAL_USER' => $request->APPROVAL_USER,
                        'APPROVAL_REMARK_PROFILE' => $request->APPROVAL_REMARK_PROFILE,
                        'APPROVAL_REMARK_DETAILINFO' => $request->APPROVAL_REMARK_DETAILINFO,
                        'APPROVAL_REMARK_CEOnDIR' => $request->APPROVAL_REMARK_CEOnDIR,
                        'APPROVAL_REMARK_ARnAAR' => $request->APPROVAL_REMARK_ARnAAR,
                        'APPROVAL_REMARK_ADDTIONALINFO' => $request->APPROVAL_REMARK_ADDTIONALINFO,
                        'APPROVAL_REMARK_PAYMENT' => $request->APPROVAL_REMARK_PAYMENT,
                    ]
                );

                $processFlow = ProcessFlow::where(['PROCESS_FLOW_NAME' => 'UPDATE DISTRIBUTOR DETAILS - A(1)(b)'])->first();
                //Send Notification to RD FIMM
                $data = [
                    'group_id' => $request->APP_GROUP_ID, //Group ID for FIMM(RD) Registration Department in am/MANAGE_GROUP
                    'flow_id' => $processFlow->PROCESS_FLOW_ID ?? null,
                    'remark' => '(DIST) Update profile for RD approval.',
                    'vue_url' => 'distributor-UpdateDetails-SubmissionList-RDApproval',
                ];

                foreach (json_decode($request->APPR_LIST) as $item) {
                    $notification = new ManageNotification();
                    $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $data['remark'], $data['vue_url']);
                }
            }

            if ($request->APPR_REMARK_DOCU_PROFILE != null && $request->hasFile('APPR_REMARK_DOCU_PROFILE')) {
                $itemFiles = $request->APPR_REMARK_DOCU_PROFILE;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_PROFILE_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_DETAILINFO != null && $request->hasFile('APPR_REMARK_DOCU_DETAILINFO')) {
                $itemFiles = $request->APPR_REMARK_DOCU_DETAILINFO;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_DETAILINFO_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_CEOnDIR != null && $request->hasFile('APPR_REMARK_DOCU_CEOnDIR')) {
                $itemFiles = $request->APPR_REMARK_DOCU_CEOnDIR;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_CEOnDIR_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_ARnAAR != null && $request->hasFile('APPR_REMARK_DOCU_ARnAAR')) {
                $itemFiles = $request->APPR_REMARK_DOCU_ARnAAR;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_ARnAAR_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_ADDITIONALINFO != null && $request->hasFile('APPR_REMARK_DOCU_ADDITIONALINFO')) {
                $itemFiles = $request->APPR_REMARK_DOCU_ADDITIONALINFO;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_ADDITIONALINFO_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_PAYMENT != null && $request->hasFile('APPR_REMARK_DOCU_PAYMENT')) {
                $itemFiles = $request->APPR_REMARK_DOCU_PAYMENT;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_PAYMENT_TYPE']);
            }

            // if ($request->DOCU_REMARK_LIST = null) {
            //     $docRemark = $request->DOCU_REMARK_LIST;
            //     foreach ($docRemark as $item) {
            //         $item = json_decode($item);
            //         $remark = new DistributorTempDocumentRemark;
            //         $remark->DIST_APPR_ID = $data->DIST_APPROVAL_ID;
            //         $remark->DIST_ID = $data->DIST_ID;
            //         $remark->DIST_TYPE_ID = $request->DIST_TYPE_ID;
            //         $remark->REQUIRED_DOC_ID = $item->MANAGE_REQUIRED_DOCUMENT_ID;
            //         $remark->DOCU_REMARK = $item->DOCU_REMARK;
            //         $remark->save();
            //     }
            // }

            DB::commit();
            http_response_code(200);
            return response([
                'message' => 'Data successfully saved.'
            ]);
        } catch (\Exception $r) {
            return $r;
            DB::rollback();
            http_response_code(400);
            return response([
                'message' => 'Failed to create data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $docType)
    {
        try {
            DistributorTempDocumentRemark::where('DOCU_TYPE', $docType)
                ->where('DIST_TEMP_ID', $updateDistributorProfileapproval->DIST_TEMP_ID)
                ->where('DIST_UPDATE_APPROVAL_ID', $updateDistributorProfileapproval->DISTRIBUTOR_UPDATE_APPROVAL_ID)
                ->delete();

            foreach ($itemFiles as $item) {
                $itemFile = $item;

                $contents = $itemFile->openFile()->fread($itemFile->getSize()); //convert to blob

                $doc = new DistributorTempDocumentRemark;
                $doc->DIST_TEMP_ID = $updateDistributorProfileapproval->DIST_TEMP_ID;
                $doc->DIST_UPDATE_APPROVAL_ID = $updateDistributorProfileapproval->DISTRIBUTOR_UPDATE_APPROVAL_ID;
                $doc->DOCU_BLOB = $contents;
                $doc->DOCU_FILETYPE = $itemFile->getClientOriginalExtension();
                $doc->DOCU_FILESIZE = $itemFile->getSize();
                $doc->DOCU_ORIGINAL_NAME = $itemFile->getClientOriginalName();
                $doc->DOCU_TYPE = $docType;
                $doc->CREATE_BY = $inputs['APPROVAL_USER'];
                if (!$doc->save()) throw new \ErrorException('Failed to save document');
                //$doc->save();
            }
        } catch (\Exception $r) {
            throw $r;
        }
    }

    public function fimmReview(Request $request)
    {
        DB::enableQueryLog();
        DB::beginTransaction();
        try {
            $inputs = $request->all();
            //Update Data for Approval by 2nd Level
            $processFlow = ProcessFlow::where(['PROCESS_FLOW_NAME' => 'UPDATE DISTRIBUTOR DETAILS - A(1)(b)'])->first();

            $distributorApproval = DistributorTemp::where(['DIST_ID' => $request->DIST_ID, 'DIST_TEMP_ID' => $request->DIST_TEMP_ID])->first();
            $distributorApproval->TS_ID = $request->APPROVAL_STATUS;
            if (!$distributorApproval->save()) throw new \ErrorException('Failed to save Distributor approval data');

            if ($request->APPROVAL_STATUS == 6) { //If Save as Draft
                //Create Approval Log Data
                $updateDistributorProfileapproval = DistributorUpdateApproval::updateOrCreate(
                    [
                        'DIST_TEMP_ID' => $request->DIST_TEMP_ID,
                        'DIST_ID' => $request->DIST_ID,
                        'APPR_GROUP_ID' => $request->APPR_GROUP_ID,
                        'APPROVAL_STATUS' => $request->APPROVAL_STATUS,
                    ],
                    [
                        'APPROVAL_LEVEL_ID' => $processFlow->PROCESS_FLOW_ID ?? null,
                        'APPROVAL_INDEX' => $request->APPR_INDEX,
                        'APPROVAL_USER' => $request->APPROVAL_USER,
                        'APPROVAL_REMARK_PROFILE' => $request->APPROVAL_REMARK_PROFILE,
                        'APPROVAL_REMARK_DETAILINFO' => $request->APPROVAL_REMARK_DETAILINFO,
                        'APPROVAL_REMARK_CEOnDIR' => $request->APPROVAL_REMARK_CEOnDIR,
                        'APPROVAL_REMARK_ARnAAR' => $request->APPROVAL_REMARK_ARnAAR,
                        'APPROVAL_REMARK_ADDTIONALINFO' => $request->APPROVAL_REMARK_ADDTIONALINFO,
                        'APPROVAL_REMARK_PAYMENT' => $request->APPROVAL_REMARK_PAYMENT,
                    ]
                );
            } else if ($request->APPROVAL_STATUS == 7) { //Returned
                $distributorApproval->PUBLISH_STATUS = 0;
                if (!$distributorApproval->save()) throw new \ErrorException('Failed to save Distributor approval data');

                //Create Approval Log Data
                $updateDistributorProfileapproval = DistributorUpdateApproval::create(
                    [
                        'DIST_TEMP_ID' => $request->DIST_TEMP_ID,
                        'DIST_ID' => $request->DIST_ID,
                        'APPR_GROUP_ID' => $request->APPR_GROUP_ID,
                        'APPROVAL_LEVEL_ID' => $processFlow->PROCESS_FLOW_ID ?? null,
                        'APPROVAL_INDEX' => $request->APPR_INDEX,
                        'APPROVAL_STATUS' => $request->APPROVAL_STATUS,
                        'APPROVAL_USER' => $request->APPROVAL_USER,
                        'APPROVAL_REMARK_PROFILE' => $request->APPROVAL_REMARK_PROFILE,
                        'APPROVAL_REMARK_DETAILINFO' => $request->APPROVAL_REMARK_DETAILINFO,
                        'APPROVAL_REMARK_CEOnDIR' => $request->APPROVAL_REMARK_CEOnDIR,
                        'APPROVAL_REMARK_ARnAAR' => $request->APPROVAL_REMARK_ARnAAR,
                        'APPROVAL_REMARK_ADDTIONALINFO' => $request->APPROVAL_REMARK_ADDTIONALINFO,
                        'APPROVAL_REMARK_PAYMENT' => $request->APPROVAL_REMARK_PAYMENT,
                    ]
                );

                $processFlow = ProcessFlow::where(['PROCESS_FLOW_NAME' => 'UPDATE DISTRIBUTOR DETAILS - A(1)(b)'])->first();
                ///Send Notification to Distributor Adminsitrator
                $data = [
                    'group_id' => 3, //Approval Group ID for ADMIN OF DISTRIBUTOR in am/DISTRIBUTOR_MANAGE_GROUP
                    'flow_id' => $processFlow->PROCESS_FLOW_ID ?? null,
                    'distributor_id' => $request->DIST_ID,
                    'status_id' => 0,
                    'remark' => '(DISTRIBUTOR UPDATE PROFILE) Returned by RD',
                    'vue_url' => 'distributor-profile-update',
                ];
                $notification = new ManageDistributorNotification();
                $notification->add($data['group_id'], $data['flow_id'], $data['distributor_id'], $data['remark'], $data['vue_url']);
            } else if ($request->APPROVAL_STATUS == 3) { // Approved
                //Create Approval Log Data
                $updateDistributorProfileapproval = DistributorUpdateApproval::create(
                    [
                        'DIST_TEMP_ID' => $request->DIST_TEMP_ID,
                        'DIST_ID' => $request->DIST_ID,
                        'APPR_GROUP_ID' => $request->APPR_GROUP_ID,
                        'APPROVAL_LEVEL_ID' => $processFlow->PROCESS_FLOW_ID ?? null,
                        'APPROVAL_INDEX' => $request->APPR_INDEX,
                        'APPROVAL_STATUS' => $request->APPROVAL_STATUS,
                        'APPROVAL_USER' => $request->APPROVAL_USER,
                        'APPROVAL_REMARK_PROFILE' => $request->APPROVAL_REMARK_PROFILE,
                        'APPROVAL_REMARK_DETAILINFO' => $request->APPROVAL_REMARK_DETAILINFO,
                        'APPROVAL_REMARK_CEOnDIR' => $request->APPROVAL_REMARK_CEOnDIR,
                        'APPROVAL_REMARK_ARnAAR' => $request->APPROVAL_REMARK_ARnAAR,
                        'APPROVAL_REMARK_ADDTIONALINFO' => $request->APPROVAL_REMARK_ADDTIONALINFO,
                        'APPROVAL_REMARK_PAYMENT' => $request->APPROVAL_REMARK_PAYMENT,
                    ]
                );

                $processFlow = ProcessFlow::where(['PROCESS_FLOW_NAME' => 'UPDATE DISTRIBUTOR DETAILS - A(1)(b)'])->first();
                //Send Notification to Distributor Adminsitrator
                $data = [
                    'group_id' => 3, //Approval Group ID for ADMIN OF DISTRIBUTOR in am/DISTRIBUTOR_MANAGE_GROUP
                    'flow_id' => $processFlow->PROCESS_FLOW_ID ?? null,
                    'distributor_id' => $request->DIST_ID,
                    'status_id' => 0,
                    'remark' => '(DISTRIBUTOR UPDATE PROFILE) Approved by RD',
                    'vue_url' => 'distributor-profile-update',
                ];
                $notification = new ManageDistributorNotification();
                $notification->add($data['group_id'], $data['flow_id'], $data['distributor_id'], $data['remark'], $data['vue_url']);

                //Send Approval email to Distributor
                // $distributorEmail = Distributor::where('DISTRIBUTOR_ID', $request->DIST_ID)->first();
                // $email = $distributorEmail->DIST_EMAIL;
                // Mail::send('emails.profileUpdateApprovalEmail', ['data' => ''],
                //     function ($message) use ($email) {
                //         $message->to($email);
                //         $message->subject('(DISTRIBUTOR UPDATE PROFILE) Approved by RD');
                //     }
                // );

            }

            //Uploadin Approval Documents
            if ($request->APPR_REMARK_DOCU_PROFILE != null && $request->hasFile('APPR_REMARK_DOCU_PROFILE')) {
                $itemFiles = $request->APPR_REMARK_DOCU_PROFILE;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_PROFILE_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_DETAILINFO != null && $request->hasFile('APPR_REMARK_DOCU_DETAILINFO')) {
                $itemFiles = $request->APPR_REMARK_DOCU_DETAILINFO;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_DETAILINFO_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_CEOnDIR != null && $request->hasFile('APPR_REMARK_DOCU_CEOnDIR')) {
                $itemFiles = $request->APPR_REMARK_DOCU_CEOnDIR;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_CEOnDIR_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_ARnAAR != null && $request->hasFile('APPR_REMARK_DOCU_ARnAAR')) {
                $itemFiles = $request->APPR_REMARK_DOCU_ARnAAR;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_ARnAAR_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_ADDITIONALINFO != null && $request->hasFile('APPR_REMARK_DOCU_ADDITIONALINFO')) {
                $itemFiles = $request->APPR_REMARK_DOCU_ADDITIONALINFO;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_ADDITIONALINFO_TYPE']);
            }

            if ($request->APPR_REMARK_DOCU_PAYMENT != null && $request->hasFile('APPR_REMARK_DOCU_PAYMENT')) {
                $itemFiles = $request->APPR_REMARK_DOCU_PAYMENT;
                $this->UploadProfileReviewDoc($inputs, $itemFiles, $updateDistributorProfileapproval, $inputs['APPR_REMARK_DOCU_PAYMENT_TYPE']);
            }

            if ($request->APPROVAL_STATUS == 3) {
                $distributorTempData = DistributorTemp::where(['DIST_ID' => $request->DIST_ID, 'DIST_TEMP_ID' => $request->DIST_TEMP_ID])->first();
                //$distributorTempData->TS_ID = 6; // For now static for testing - To prevent Duplicate when Approve
                //if (!$distributorTempData->save()) throw new \ErrorException('Failed to save Distributor approval data'); // For now static for testing - To prevent Duplicate when Approve

                // 1. Update Tab 1 Datas - Main Info and Addresses DB Datas
                $this->distributorUpdateMainData->updateDistributorMainInfoAdress($distributorTempData);

                // 2. Update Tab 2 Datas - Main Details Information DB Datas
                $this->distributorUpdateMainData->updateDistributorMainDetails($distributorTempData);

                // 3. Update Tab 3 Datas - Main CEO And Director DB Datas
                $this->distributorUpdateMainData->updateDistributorMainCEOandDirector($distributorTempData);

                // 4. Update Tab 4 Datas - Main AR and AAR DB Datas
                $this->distributorUpdateMainData->updateDistributorMainAR($distributorTempData);
                $this->distributorUpdateMainData->updateDistributorMainAAR($distributorTempData);

                // 5. Update Tab 5 Datas - HOD_COMPL, HOD_UTS, HOD_PRS and HOD_TRAIN
                $this->distributorUpdateMainData->updateDistributorMainHOD_COMPL($distributorTempData);
                $this->distributorUpdateMainData->updateDistributorMainHOD_UTS($distributorTempData);
                $this->distributorUpdateMainData->updateDistributorMainHOD_PRS($distributorTempData);
                $this->distributorUpdateMainData->updateDistributorMainHOD_TRAIN($distributorTempData);
            }

            DB::commit();
            http_response_code(200);
            return response([
                'message' => 'Data successfully saved.'
            ]);
        } catch (RequestException $r) {
            DB::rollback();
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
            'APPR_GROUP_ID' => 'integer|nullable',
            'APPROVAL_LEVEL_ID' => 'integer|nullable',
            'APPROVAL_INDEX' => 'integer|nullable',
            'APPROVAL_STATUS' => 'integer|nullable',
            'APPROVAL_USER' => 'integer|nullable',
            'APPROVAL_REMARK_SECONDLVL' => 'string|nullable',
            'APPROVAL_REMARK_RD' => 'string|nullable',
            'CREATE_TIMESTAMP' => 'string|nullable'
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
            'APPR_GROUP_ID' => 'integer|nullable',
            'APPROVAL_LEVEL_ID' => 'integer|nullable',
            'APPROVAL_INDEX' => 'integer|nullable',
            'APPROVAL_STATUS' => 'integer|nullable',
            'APPROVAL_USER' => 'integer|nullable',
            'APPROVAL_REMARK_SECONDLVL' => 'string|nullable',
            'APPROVAL_REMARK_RD' => 'string|nullable',
            'CREATE_TIMESTAMP' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106
            ], 400);
        }

        try {
            $data = DistributorUpdateApproval::where('id', $id)->first();
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
            $data = DistributorUpdateApproval::find($id);
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
            'APPR_GROUP_ID' => 'integer|nullable',
            'APPROVAL_LEVEL_ID' => 'integer|nullable',
            'APPROVAL_INDEX' => 'integer|nullable',
            'APPROVAL_STATUS' => 'integer|nullable',
            'APPROVAL_USER' => 'integer|nullable',
            'APPROVAL_REMARK_SECONDLVL' => 'string|nullable',
            'APPROVAL_REMARK_RD' => 'string|nullable',
            'CREATE_TIMESTAMP' => 'string|nullable'
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
