<?php

use App\Http\Controllers\DistributorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\UserSecurityQuestionController;
use App\Http\Controllers\UserRegistrationApprovalController;
use App\Http\Controllers\DistributorDetailInfoController;
use App\Http\Controllers\UserSalutationController;
use App\Http\Controllers\DistributorDirectorController;
use App\Http\Controllers\Distributor;
use App\Http\Controllers\DistributorUpdateApprovalController;
use App\Http\Controllers\DistributorTypeRegistrationApprovalController;
use App\Http\Controllers\DistributorApprovalController;
use App\Http\Controllers\DistributorDocumentRemarkController;
use App\Http\Controllers\DistributorCandidateAcceptanceController;
use App\Http\Controllers\DistributorExtensionRequest;
use App\Http\Controllers\DistributionPointController;
use App\Http\Controllers\DistributorTypeController;
use App\Http\Controllers\DivestmentController;
use App\Http\Controllers\DivestmentDocumentController;
use App\Http\Controllers\CandidateAcceptanceController;
use App\Http\Controllers\DistRunnoController;
use App\Http\Controllers\AcceptanceDetailsController;
use App\Http\Controllers\SuspendRevokeController;
use App\Http\Controllers\SuspendRevokeDocumentController;
use App\Http\Controllers\SuspendRevokeApprovalController;
use App\Http\Controllers\SuspendRevokeAppealController;
use App\Http\Controllers\SuspendRevokeAppealApprController;
use App\Http\Controllers\SuspendRevokeAppealDocController;
use App\Http\Controllers\SuspendRevokeAppealApprDocController;
use App\Http\Controllers\DistributorOtherController;
use App\Http\Controllers\CessationDistributorController;
use App\Http\Controllers\CessationDistributorApprovalController;
use App\Http\Controllers\CessationDistributorDocController;
use App\Http\Controllers\CessationFimmApprovalController;
use App\Http\Controllers\CessationFimmDocController;
use App\Http\Controllers\CessationAuthorizationLetterController;
use App\Http\Controllers\DashboardDistributorDisplaySettingController;
use App\Http\Controllers\DistributorDocumentController;
use App\Http\Controllers\templateController;
use Illuminate\Support\Facades\Route;

Route::group(['tag' => 'Demo'], function () {
    Route::post('demo-createuser', [DemoController::class, 'createuser'])->name('Create User');
    Route::get('demo-getUserByEmail', [DemoController::class, 'getUserByEmail'])->name('Get User by Email');
    Route::post('demo-blob', [DemoController::class, 'storeBLOB'])->name('Stor BLOB Data');
});
    Route::post('login', [AuthController::class, 'login'])->name('login');

        Route::post('logout', [AuthController::class, 'logout'])->name('Logout User By Id');
        Route::get('getToken', [AuthController::class, 'getTokenInfo'])->name('Get token info');
        Route::get('checkTokenValidation', [AuthController::class, 'checkTokenValidation'])->name('Check token validation');

Route::group(['middleware' => 'auth:api'], function () {
});

Route::group(['tag' => 'Distributor'], function () {
    Route::get('distributor_user', [DistributorController::class, 'checkDuplicateIC'])->name('Distributor user duplicate');
    Route::get('distributor_users', [DistributorController::class, 'getAll'])->name('Get All Distributor User');
    Route::post('distributorSaveDraftReg', [DistributorController::class, 'distributorSaveDraftReg'])->name('save distributor as draft');
    Route::post('distributor', [DistributorController::class, 'create'])->name('Create distributor');
    Route::post('distributor_docs', [DistributorController::class, 'create_Doc'])->name('Create distributor docs');
    Route::post('distributor_reg_update', [DistributorController::class, 'update'])->name(' distributor registration update');
    Route::get('distributor', [DistributorController::class, 'get'])->name('Get distributor by id');
    Route::get('distributorUpdateProfileDatas', [DistributorController::class, 'getdistributorUpdateProfileDatas'])->name('Get distributor update Profile Datas by id');
    Route::put('distributor', [DistributorController::class, 'update'])->name('Update distributor');
    Route::get('getDistributor', [DistributorController::class, 'getDistributor'])->name('get distributor list');
    Route::get('getDistType', [DistributorController::class, 'getDistType'])->name('get distributor type by id');
    // Route::get('distributor_new_application', [DistributorController::class, 'getDistributorApplicationList'])->name('Get distributor application List');
    Route::get('distributor_application_detail', [DistributorController::class, 'getByDistId'])->name('Get distributor by dist id');
    Route::get('distributor_temp_application_detail', [DistributorController::class, 'getByDisTemptId'])->name('Get distributor by dist id');
    Route::get('required_document_proposal', [DistributorController::class, 'getDocumentProposal'])->name('get required document proposal');
    // Route::get('required_document_proposal_update', [DistributorController::class, 'getDocumentProposal'])->name('get required document proposal');

    Route::delete('delete_distributor_document', [DistributorDocumentController::class, 'deleteDistributorDocument'])->name('Delete distributor document');


    Route::post('distributor_update', [DistributorController::class, 'distributor_update'])->name('Update distributor info');


    Route::get('distributor_fee', [DistributorController::class, 'getDistributorFee'])->name('get distributor fee');

    Route::post('create_dummy_distributor', [DistributorController::class, 'createDummyDistributor'])->name('Create Dummy distributor');
    Route::get('get_all_dummy_distributor', [DistributorController::class, 'getAllDummyDistributor'])->name('Get All Dummy distributor');
    Route::post('delete_dummy_distributor', [DistributorController::class, 'deleteDummyDistributor'])->name('Delete Dummy distributor');
    Route::post('update_dummy_distributor', [DistributorController::class, 'updateDummyDistributor'])->name('Update Dummy distributor');
});
Route::group(['tag' => 'Distributor Detail Info'], function () {
    Route::post('distributor_info', [DistributorDetailInfoController::class, 'create'])->name('Distributor Detail Info');
});
Route::group(['tag' => 'User Salutation'], function () {
    Route::get('user_salutation', [UserSalutationController::class, 'getAll'])->name('Get all user salutation');
});
Route::group(['tag' => 'Distributor Director'], function () {
    Route::delete('distributor_director', [DistributorDirectorController::class, 'delete'])->name('Delete director');
});

Route::group(['tag' => 'Divestment Record'], function () {
    Route::get('Distributor_consultantDivestment', [DivestmentController::class, 'getdistributorconsultant'])->name('Get distributor List');
    Route::get('distributordetails', [DivestmentController::class, 'distributordetails'])->name('Get distributor Details');
    Route::get('distributordetailsfimm', [DivestmentController::class, 'distributordetailsfimm'])->name('Get distributor Details fimm');
    Route::get('distributorMergeDetails', [DivestmentController::class, 'distributorMerge'])->name('Get distributor Details');
    Route::get('Divestment_List', [DivestmentController::class, 'DivestmentList'])->name('Get divestment List');
    Route::get('divestmentAppr_List', [DivestmentController::class, 'DivestmentApproverList'])->name('Get divestment Appr List');
    Route::get('divestmentApprFimm_List', [DivestmentController::class, 'DivestmentFimmList'])->name('Get divestment Appr Fimm List');
    Route::get('Divestment_FundStatus', [DivestmentController::class, 'Divestment_FundStatus'])->name('Get divestment fund status');
    Route::get('Clear_Fund_Selection', [DivestmentController::class, 'Clear_Fund_Selection'])->name('Clear Fund Selection');
    Route::get('Clear_Cons_Selection', [DivestmentController::class, 'Clear_Cons_Selection'])->name('Clear Cons Selection');
    Route::get('Divestment_ConsStatus', [DivestmentController::class, 'Divestment_ConsultantStatus'])->name('Get divestment Cons status');
    Route::get('fund_list', [DivestmentController::class, 'getfund'])->name('Get fund List');
    Route::get('selected_fund_list', [DivestmentController::class, 'getfundSelected'])->name('Get selected fund List');
    Route::get('selected_cons_list', [DivestmentController::class, 'getconsSelected'])->name('Get selected cons List');
    Route::get('Submission_fund_list', [DivestmentController::class, 'Submission_fund_list'])->name('Get submission fund List');
    Route::get('Submission_cons_list', [DivestmentController::class, 'Submission_cons_list'])->name('Get submission cons List');
    Route::get('Distributor_fundDivestment', [DivestmentController::class, 'getdistributorfund'])->name('Get distributorfund List');
    Route::get('Distributor_fundConsultantDivestment', [DivestmentController::class, 'getdistributorfundConsultant'])->name('Get distributorfundConsultant List');

    Route::get('consultant_list', [DivestmentController::class, 'getconsultant'])->name('Get distributor List');
    Route::post('fund_add', [DivestmentController::class, 'fund_add'])->name('add fund to Record');
    Route::post('fund_remove', [DivestmentController::class, 'fund_remove'])->name('remove fund to Record');
    Route::post('SelectAllFund', [DivestmentController::class, 'SelectAllFund'])->name('add all fund to Record');
    Route::post('UnselectAllFund', [DivestmentController::class, 'UnselectAllFund'])->name('remove all fund to Record');
    Route::post('Cons_add', [DivestmentController::class, 'Cons_add'])->name('add Consultant to Record');
    Route::post('Cons_remove', [DivestmentController::class, 'Cons_remove'])->name('remove Consultant to Record');
    Route::post('SelectAllCons', [DivestmentController::class, 'SelectAllCons'])->name('add all cons to Record');
    Route::post('UnselectAllCons', [DivestmentController::class, 'UnselectAllCons'])->name('remove all cons to Record');
    Route::post('fund_selection', [DivestmentController::class, 'createfund_selection'])->name('Create to Record');
    Route::post('fund_submission', [DivestmentController::class, 'update_submission'])->name('Create to Existing Record');
    Route::post('update_Approval', [DivestmentController::class, 'updateApproval'])->name('Update Existing Record');
    Route::post('update_Approvalrd', [DivestmentController::class, 'updateApprovalrd'])->name('Update Existing rd Record');
    Route::post('update_Approvalhodrd', [DivestmentController::class, 'updateApprovalhodrd'])->name('Update Existing hod rd Record');

    //for advancement filter divestment
    Route::get('filter_divestment_record', [DivestmentController::class, 'filter'])->name('Filter Record');
    Route::get('filter_divestment_record-rd', [DivestmentController::class, 'DivestmentFimmListFilter'])->name('RD Filter Record');
    Route::get('filter_divestment_approval', [DivestmentController::class, 'DivestmentApprovalFilter'])->name('RD Filter Approval');

    //fund discard
    Route::post('discard', [DivestmentController::class, 'Discard'])->name('Discard');
    Route::get('divestment-approval', [DivestmentController::class, 'getDivestmentApproval'])->name('Get Divestment Approval');

    //fund to fund-temp convert
    Route::post('convertFundToFundTemp', [DivestmentController::class, 'convertFundToFundTemp'])->name('convertFundToFundTemp');
    Route::post('convertConsultantToConsultantTemp', [DivestmentController::class, 'convertConsultantToConsultantTemp'])->name('convertConsultantToConsultantTemp');
});

Route::group(['tag' => 'Divestment Document'], function () {
    Route::get('get_document_Divestment_One', [DivestmentDocumentController::class, 'getDocumentByIDOne'])->name('get documentOne by ID');
    Route::get('get_document_Divestment_Two', [DivestmentDocumentController::class, 'getDocumentByIDTwo'])->name('get documentTwo by ID');
    Route::get('get_document_Divestment_Three', [DivestmentDocumentController::class, 'getDocumentByIDThree'])->name('get documentThree by ID');
    Route::get('get_document_Divestment_Four', [DivestmentDocumentController::class, 'getDocumentByIDFour'])->name('get documentFour by ID');
    Route::get('get_document_Divestment_SecondLevel', [DivestmentDocumentController::class, 'getDocumentByIDSecondLevel'])->name('get documentSecondLevel by ID');
    Route::get('get_document_Dist_Approver', [DivestmentDocumentController::class, 'getDocumentDistApprover'])->name('get DistApprover by ID');
    Route::get('get_document_RD_Approver', [DivestmentDocumentController::class, 'getDocumentRDApprover'])->name('get RDApprover by ID');
    Route::get('get_document_HODRD_Approver', [DivestmentDocumentController::class, 'getDocumentHODRDApprover'])->name('get HODRDApprover by ID');
    Route::delete('delete_Divestment_document', [DivestmentDocumentController::class, 'delete'])->name('Delete Divestment document');
});

Route::group(['tag' => 'Transaction Log'], function () {
    Route::post('transaction_log', [SuspensionApprovalController::class, 'getAll'])->name('Get All record');
});

Route::group(['tag' => 'Distributor User'], function () {
    Route::get('getDistUserInfo', [UserController::class, 'get'])->name('Get distributor User Info');
    Route::get('getDistUserList', [UserController::class, 'getDistUserList'])->name('Get distributor User List');
    Route::post('UpdateUserProfile', [UserController::class, 'UpdateUserProfile'])->name('Update distributor User Profile');
    Route::post('updateUserProfileData', [UserController::class, 'UpdateUserProfilePost'])->name('Update distributor User Profile');
    Route::get('distributor_document_remark', [DistributorDocumentRemarkController::class, 'get'])->name('Distributor document remark');
    Route::get('distributor_point_by_id', [UserController::class, 'getDistributorPoint'])->name('Get distributor Point Info');
});

Route::group(['tag' => 'Distributor Request For Extension', 'excluded_middleware' => ['api', 'auth:api']], function () {
    Route::get('distributor_extension_request', [DistributorExtensionRequest::class, 'index'])->name('Fetch list of extension request');
    Route::delete('distributor_extension_request_approval_docs/{id}', [DistributorExtensionRequest::class, 'deleteApprovalDocs'])->name('Delete extension request approval documents');
    Route::delete('distributor_extension_request/{id}', [DistributorExtensionRequest::class, 'deleteDocs'])->name('Delete extension request documents');
    Route::get('distributor_extension_request/{id}', [DistributorExtensionRequest::class, 'getById'])->name('Fetch list of extension request by ID');
    Route::get('distributor_extension_request_histories/{id}', [DistributorExtensionRequest::class, 'history'])->name('Fetch list of extension request history by ID');
    Route::post('distributor_extension_request/{id}', [DistributorExtensionRequest::class, 'update'])->name('Update extension request by ID');
    Route::post('distributor_extension_request', [DistributorExtensionRequest::class, 'store'])->name('Create new extension request');
    Route::post('distributor_subsequent_extension_request', [DistributorExtensionRequest::class, 'subsequentExtensionRequest'])->name('Create a new subsequent extension request');
    Route::post('distributor_extension_request_approval', [DistributorExtensionRequest::class, 'extensionRequestApproval'])->name('Approval an existing extension request');
    Route::get('distributor_extension_request_approval_list', [DistributorExtensionRequest::class, 'extensionRequestApprovalList'])->name('Approval history list');
    Route::prefix('subsequent')->group(function () {
        Route::get('distributor_extension_request', [DistributorExtensionRequest::class, 'subsequentIndex'])->name('Fetch list of subsequent extension request');
        Route::get('distributor_extension_request/{id}', [DistributorExtensionRequest::class, 'getSubsequentById'])->name('Fetch subsequent extension request by ID');
        Route::post('distributor_extension_request_approval', [DistributorExtensionRequest::class, 'subsequentExtensionRequestApproval'])->name('Approval an existing subsequent extension request');
        Route::post('distributor_extension_request', [DistributorExtensionRequest::class, 'storeSubsequent'])->name('Create new subsequent extension request');
        Route::post('distributor_extension_request/{id}', [DistributorExtensionRequest::class, 'updateSubsequentExtensionRequest'])->name('Update subsequent extension request');
        Route::get('distributor_extension_request_approval_list', [DistributorExtensionRequest::class, 'subsequentExtensionRequestApprovalList'])->name('Approval history list for subsequent extension request');
    });

    Route::group(['tag' => 'Suspension Revocation Record'], function () {
        Route::get('get_distributor_records', [SuspendRevokeController::class, 'getDistributorRecord'])->name('Get All Distributor Record List');
        Route::post('create_suspend_revoke_submission', [SuspendRevokeController::class, 'createSubmission'])->name('Create Submission');
        Route::get('suspend_revoke_records', [SuspendRevokeController::class, 'suspendRevokeRecord'])->name('Get All Suspend Revoke Record List');
        Route::post('update_suspend_revoke_submission', [SuspendRevokeController::class, 'updateSubmission'])->name('Update Submission');
        Route::get('filter_suspendRevoke_record', [SuspendRevokeController::class, 'filter'])->name('Filter Record');
        Route::get('get_list_byID', [SuspendRevokeController::class, 'getRecordByID'])->name('Get All Distributor Record List');
    });

    Route::group(['tag' => 'Suspend Revoke Document'], function () {
        Route::get('get_document', [SuspendRevokeDocumentController::class, 'getDocumentByID'])->name('get document by ID');
        Route::delete('delete_document', [SuspendRevokeDocumentController::class, 'delete'])->name('Delete document');
        Route::get('get_document_byApprover', [SuspendRevokeDocumentController::class, 'getDocumentByApprover'])->name('get document by Approver ID');
        Route::post('create_document', [SuspendRevokeDocumentController::class, 'createDocument'])->name('Insert new Document');
    });

    Route::group(['tag' => 'Suspend Revoke Approval'], function () {
        Route::get('appr_record', [SuspendRevokeApprovalController::class, 'getApprRecordByGroupID'])->name('get approval record by Group ID');
        Route::get('audit_approval_record', [SuspendRevokeApprovalController::class, 'getAuditLogByID'])->name('Get Audit Log By ID');
        Route::post('approval_update', [SuspendRevokeApprovalController::class, 'updateApproval'])->name('Update Approval Record');
        Route::post('approval_update_ceo', [SuspendRevokeApprovalController::class, 'updateApprovalCeo'])->name('Update Approval Record for Ceo');
        Route::post('suspend_distributorConsultant', [SuspendRevokeApprovalController::class, 'suspendDistributorConsultant'])->name('suspend Distributor Consultant');
        Route::post('accept_suspendRevoke', [SuspendRevokeApprovalController::class, 'acceptSuspendRevoke'])->name('distributor accept action');
        Route::get('get_appealDays', [SuspendRevokeApprovalController::class, 'getAppealDays'])->name('get Appeal Days');
    });

    Route::group(['tag' => 'Suspend Revoke Appeal'], function () {
        Route::post('appeal_update', [SuspendRevokeAppealController::class, 'updateAppeal'])->name('Update Appeal Record');
        Route::get('appeal_suspend_revoke_records', [SuspendRevokeAppealController::class, 'AppealSuspendRevokeRecord'])->name('Get All Suspend Revoke Appeal Record List');
        Route::get('filter_suspendRevokeAppeal_record', [SuspendRevokeAppealController::class, 'filterAppeal'])->name('Filter Appeal Record');
    });

    Route::group(['tag' => 'Suspend Revoke Appeal Document'], function () {
        Route::post('create_document_appeal', [SuspendRevokeAppealDocController::class, 'createDocumentAppeal'])->name('Insert new Document');
        Route::delete('delete_appeal_document', [SuspendRevokeAppealDocController::class, 'deleteAppealDocument'])->name('Delete appeal document');
        Route::get('get_appeal_document', [SuspendRevokeAppealDocController::class, 'getAppealDocByID'])->name('get document by ID');
    });

    Route::group(['tag' => 'Suspend Revoke Appeal Approver Document'], function () {
        Route::get('get_appeal_approver_document', [SuspendRevokeAppealApprDocController::class, 'getDocumentByAppealApprover'])->name('get document by ID');
        Route::get('getAll_appeal_approver_document', [SuspendRevokeAppealApprDocController::class, 'getAllDocumentByAppealApprover'])->name('get document by ID');
        Route::post('create_document_appeal_approval', [SuspendRevokeAppealApprDocController::class, 'createDocumentAppealApproval'])->name('Insert new Document');
        Route::delete('delete_appealApproval_document', [SuspendRevokeAppealApprDocController::class, 'deleteAppealApprovalDocument'])->name('Delete appeal document');
    });

    Route::group(['tag' => 'Suspend Revoke Appeal Approver'], function () {
        Route::get('audit_appeal_approval_record', [SuspendRevokeAppealApprController::class, 'getAppealAuditLogByID'])->name('Get Appeal Audit Log By ID');
        Route::post('appeal_approval_update', [SuspendRevokeAppealApprController::class, 'AppealUpdateApproval'])->name('Update Appeal Approval Record');
        Route::post('appeal_approval_review', [SuspendRevokeAppealApprController::class, 'AppealUpdateReview'])->name('Update Appeal Review Record');
        Route::post('appeal_approval_reject_revoke', [SuspendRevokeAppealApprController::class, 'AppealUpdateRejectRevoke'])->name('Update Appeal Reject Revoke');
        Route::post('appeal_approval_update_ceo', [SuspendRevokeAppealApprController::class, 'AppealUpdateApprovalCeo'])->name('Update Appeal Approval Record for Ceo');
        Route::get('get_appealComment', [SuspendRevokeAppealApprController::class, 'getAppealComment'])->name('Get Appeal Comment');
        Route::get('appeal_appr_record', [SuspendRevokeAppealApprController::class, 'getAppealApprRecordByGroupID'])->name('get appeal approval record by Group ID');
        Route::post('action_unSuspend', [SuspendRevokeAppealApprController::class, 'actionUnsuspend'])->name('unsuspend Distributor Consultant');
        Route::post('action_revoke', [SuspendRevokeAppealApprController::class, 'actionRevoke'])->name('revoke Distributor Consultant');
    });

    Route::group(['tag' => 'Cessation Distributor'], function () {
        Route::get('cessation_list_byDistributor', [CessationDistributorController::class, 'getCessationListByDistributor'])->name('Get Cessation List By Distributor');
        Route::get('get_active_distributor', [CessationDistributorController::class, 'getActiveDistributor'])->name('Get All Active Distributor List');
        Route::get('get_cessation_type', [CessationDistributorController::class, 'getCessationType'])->name('Get All Cessation Type List');
        Route::get('get_bank_list', [CessationDistributorController::class, 'getBankList'])->name('Get All Bank List');
        Route::get('get_distributor_info', [CessationDistributorController::class, 'getDistributorInfo'])->name('Get Distributor info');
        Route::post('create_cessation_submission', [CessationDistributorController::class, 'createCessationSubmission'])->name('Create Cessation Submission');
        Route::get('get_cessation_details_byID', [CessationDistributorController::class, 'getCessationDetailsByID'])->name('Get Cessation Details by ID');
        Route::post('update_cessation_submission', [CessationDistributorController::class, 'updateCessationSubmission'])->name('Update Cessation Submission');
    });

    Route::group(['tag' => 'Cessation Distributor Document'], function () {
        Route::get('get_cessation_document', [CessationDistributorDocController::class, 'getCessationDocByID'])->name('get document cessation by ID');
        Route::post('upload_cessation_document', [CessationDistributorDocController::class, 'uploadCessationDocument'])->name('Insert new Cessation Document');
        Route::delete('delete_cessation_document', [CessationDistributorDocController::class, 'deleteCessationDocument'])->name('Delete cessation document');
        Route::get('get_document_byManagerApprover', [CessationDistributorDocController::class, 'getDocumentByManagerApprover'])->name('get document by Manager Approver ID');
    });

    Route::group(['tag' => 'Cessation Authorization Letter'], function () {
        Route::get('get_letter_document', [CessationAuthorizationLetterController::class, 'getLetterDocByID'])->name('get authorization letter by ID');
        Route::post('upload_letter_document', [CessationAuthorizationLetterController::class, 'uploadLetterDocument'])->name('Insert new Authorization Letter Document');
        Route::delete('delete_authorization_letter', [CessationAuthorizationLetterController::class, 'deleteAuhthorizationLetter'])->name('Delete authorization Letter');
        Route::delete('delete_authorization_letter_byCessationId', [CessationAuthorizationLetterController::class, 'deleteAuhthorizationLetterByCessationId'])->name('Delete authorization Letter by cessation ID');
    });

    Route::group(['tag' => 'Cessation Distributor Approval'], function () {
        Route::get('audit_cessation_record', [CessationDistributorApprovalController::class, 'getCessationLogByID'])->name('Get Cessation Audit Log By ID');
        Route::get('cessation_list_byGroupId', [CessationDistributorApprovalController::class, 'getCessationListByGroupId'])->name('Get Cessation List By Distributor and Group ID');
        Route::post('update_manager_approval', [CessationDistributorApprovalController::class, 'updateManagerApproval'])->name('Update Cessation Manager Approval');
    });

    Route::group(['tag' => 'Cessation Fimm Approval'], function () {
        Route::get('cessation_list_byFimmGroupId', [CessationFimmApprovalController::class, 'getCessationListByFimmGroupId'])->name('Get Cessation List By Fimm Group ID');
        Route::get('fimm_audit_cessation_record', [CessationFimmApprovalController::class, 'getFimmCessationLogByID'])->name('Get Fimm Cessation Audit Log By ID');
        Route::post('update_fimm_approval', [CessationFimmApprovalController::class, 'updateFimmApproval'])->name('Update Cessation Fimm Approval');
        Route::get('cessation_all_list', [CessationFimmApprovalController::class, 'getCessationOverviewList'])->name('Get Cessation Overview List');
        Route::post('update_fimm_hod_approval', [CessationFimmApprovalController::class, 'updateFimmHodApproval'])->name('Update Cessation by Hod Fimm Approval');
        Route::post('action_cease', [CessationFimmApprovalController::class, 'actionCease'])->name('Cease Distributor Consultant');
    });

    Route::group(['tag' => 'Cessation Fimm Document'], function () {
        Route::get('get_fimm_cessation_document', [CessationFimmDocController::class, 'getFimmCessationDocument'])->name('Get Fimm Cessation Document');
        Route::get('get_fimm_cessation_document_byID', [CessationFimmDocController::class, 'getFimmCessationDocumentByID'])->name('Get Fimm Cessation Document by ID');
        Route::get('get_fimm_document_byID', [CessationFimmDocController::class, 'getFimmCessationDocumentByID'])->name('Get Fimm Cessation Document by ID');
        Route::post('fimm_upload_cessation_document', [CessationFimmDocController::class, 'fimmUploadCessationDocument'])->name('Insert Fimm new Cessation Document');
        Route::delete('delete_fimm_cessation_document', [CessationFimmDocController::class, 'deleteFimmCessationDocument'])->name('Delete fimm cessation document');
    });
});

Route::group(['tag' => 'API without middleware'], function () {
    Route::get('getDistributorMedia', [DistributorController::class, 'getDistributorMedia'])->name('Get Distributor List for Media');
    Route::post('user', [UserController::class, 'create'])->name('Create User Registration');
    Route::put('user_update', [UserController::class, 'update'])->name('Update User Registration');
    Route::get('user_check_ic', [UserController::class, 'checkDuplicateIC'])->name('Check duplicate IC no');
    Route::get('user_check_email', [UserController::class, 'checkDuplicateEmail'])->name('Check duplicate email');
    Route::get('user_check_userID', [UserController::class, 'checkDuplicateUserID'])->name('Check duplicate user ID');
    Route::get('verify_user', [UserController::class, 'verifyUser'])->name('Check existing user');
    Route::get('user_security_quest', [UserSecurityQuestionController::class, 'getAll'])->name('user security question');
    Route::put('user_update_paasword', [UserController::class, 'update_password'])->name('Update User Password');
});

Route::group(['tag' => 'Distributor Application Approval'], function () {
    Route::get('distributor_new_application_list', [DistributorApprovalController::class, 'getDistributorApplicationList'])->name('Get distributor application list');
    Route::post('distributor_review', [DistributorApprovalController::class, 'create'])->name('Update distributor application approval');
    Route::post('sendEmailNotification', [DistributorApprovalController::class, 'sendEmailNotification'])->name('send distributor application notification');
    Route::post('sendEmailReturnNotification', [DistributorApprovalController::class, 'sendEmailReturnNotification'])->name('send distributor application Return notification');
    Route::post('sendEmailRejectNotification', [DistributorApprovalController::class, 'sendEmailRejectNotification'])->name('send distributor application Reject notification');
    Route::get('distributor_document_remark', [DistributorDocumentRemarkController::class, 'get'])->name('Distributor document remark');
    Route::get('distributor_review_data', [DistributorDocumentRemarkController::class, 'getReviewData'])->name('Distributor document remark');
});

Route::group(['tag' => 'Distribution Point'], function () {
    Route::get('getAllCountry', [DistributionPointController::class, 'getAllCountry'])->name('Get All Country');
    Route::get('getStateByID', [DistributionPointController::class, 'getStateByID'])->name('Get State by Country ID');
    Route::get('getCountryByID', [DistributionPointController::class, 'getCountryByID'])->name('Get Country ID');
    Route::get('getCityByID', [DistributionPointController::class, 'getCityByID'])->name('Get City by State ID');
    Route::get('getStatus', [DistributionPointController::class, 'getStatus'])->name('Get Status');
    Route::get('getAllByID', [DistributionPointController::class, 'getRecordByID'])->name('Get Record By ID');
    Route::get('getPostcodeByID', [DistributionPointController::class, 'getPostcodeByID'])->name('Get Postcode by City ID');
    Route::get('getDistributionPointByDistID', [DistributionPointController::class, 'getDistPointByDistID'])->name('Get Distribution Point By Distributor ID');
    Route::post('create_distributorPoint', [DistributionPointController::class, 'createDistributorPoint'])->name('Create Distributor Point');
    Route::post('update_distributorPoint', [DistributionPointController::class, 'updateDistributorPoint'])->name('update Distributor Point');
});

Route::group(['tag' => 'Candidate Acceptance'], function () {
    Route::post('import_new_acceptance', [CandidateAcceptanceController::class, 'import'])->name('Import New Acceptance List');
    Route::get('getAcceptanceListByDistID', [CandidateAcceptanceController::class, 'getAcceptanceListByDistID'])->name('Get Acceptance By Distributor ID');
    Route::get('company_record', [CandidateAcceptanceController::class, 'getCompanyID'])->name('Get Company by User_id');
    Route::post('accept_record', [CandidateAcceptanceController::class, 'acceptRecord'])->name('Update acceptance Record');
    Route::delete('delete_record', [CandidateAcceptanceController::class, 'deleteRecordByID'])->name('Delete Record by ID');
    Route::get('filter_record', [CandidateAcceptanceController::class, 'filterRecord'])->name('filter record');
    Route::get('company_status', [CandidateAcceptanceController::class, 'getCompanyStatus'])->name('Get Distributor Status By ID');

});

Route::group(['tag' => 'Distributor Running No'], function () {
    Route::get('dist_runNumber', [DistRunnoController::class, 'updateRunNo'])->name('Check and update Distributor running Number');
});

Route::group(['tag' => 'Acceptance Details'], function () {
    Route::get('getCandidateListByDistID', [AcceptanceDetailsController::class, 'getCandidateListByDistID'])->name('Get Candidate Acceptance List');
    Route::delete('delete_candidate', [AcceptanceDetailsController::class, 'deleteCandidateByID'])->name('Delete Candidate Record by ID');
    Route::get('get_rejectedByID', [AcceptanceDetailsController::class, 'getRejectedByID'])->name('Get Rejected List BY ID');
    Route::get('get_acceptedByID', [AcceptanceDetailsController::class, 'getAcceptedByID'])->name('Get Accepted List BY ID');
    Route::delete('discard_record', [AcceptanceDetailsController::class, 'discardRecordByID'])->name('Discard Record by ID');
    Route::post('email_candidate', [AcceptanceDetailsController::class, 'emailCandidate'])->name('Email Candidate');
});

Route::group(['tag' => 'Distributor Type Register'], function () {
    Route::get('getRegisterTypeApplicationList', [DistributorTypeRegistrationApprovalController::class, 'getRegistrationTypeList'])->name('Get Type Application List');
    Route::post('createRegisterTypeApplication', [DistributorTypeRegistrationApprovalController::class, 'createRegistrationType'])->name('Create Register Type Application');
    Route::get('getTypeRegByDistId', [DistributorTypeController::class, 'getTypeRegByDistId'])->name('Get Distributor Type Register By Id');
    Route::get('getDistTypeByDistTypeId', [DistributorTypeController::class, 'getDistTypeByDistTypeId'])->name('Get Distributor Type By Dist Type Id');
    Route::post('distributor_register_type_review', [DistributorTypeRegistrationApprovalController::class, 'create'])->name('Update distributor type register application approval');


    Route::get('distributor_types/{id}', [DistributorTypeRegistrationApprovalController::class, 'getDistIDType'])->name('Get all distributor type byID');
});

Route::group(['tag' => 'Distributor Other'], function () {
    Route::get('getDistGroup', [DistributorOtherController::class, 'getDistGroup'])->name('Get User Manage Group List');
});

Route::group(['tag' => 'User Registration Approval'], function () {
    Route::get('getUserRegList', [UserRegistrationApprovalController::class, 'getUserRegList'])->name('Get User Registration List');
    Route::get('getUserRegListStatus', [UserRegistrationApprovalController::class, 'getUserRegListStatus'])->name('Get User Registration List Status');
    Route::post('updateApprovalUserReg', [UserRegistrationApprovalController::class, 'updateApproval'])->name('update User Registration approval');
    Route::get('getUserRegListStatusDoc', [UserRegistrationApprovalController::class, 'getUserRegListStatusDoc'])->name('Get User Registration List Status');
});

Route::group(['tag' => 'Distributor Update'], function () {
    Route::get('getDistributorUpdateList', [DistributorUpdateApprovalController::class, 'getDistributorUpdateList'])->name('Get Distributor Update List');
    Route::get('getDistributorUpdateListRD', [DistributorUpdateApprovalController::class, 'getDistributorUpdateListRD'])->name('Get Distributor Update List RD');

    Route::post('distributor_update_review', [DistributorUpdateApprovalController::class, 'create'])->name('Create distributor Update approval');
    Route::post('fimm_update_review', [DistributorUpdateApprovalController::class, 'fimmReview'])->name('Create FIMM approval');
    Route::get('getReviewRemark', [DistributorUpdateApprovalController::class, 'getReviewRemark'])->name('Get Distributor Update Remark Details');
});

Route::group(['tag' => 'Distributor Dashboard Setting'], function () {
    Route::post('distributor_dashboard_setting', [DashboardDistributorDisplaySettingController::class, 'create'])->name('Distributor Dashboard Setting');
    Route::get('get_distributor_dashboard_setting', [DashboardDistributorDisplaySettingController::class, 'get'])->name('Distributor Dashboard Setting Get');
    Route::post('delete_distributor_dashboard_setting', [DashboardDistributorDisplaySettingController::class, 'delete'])->name('Distributor Dashboard Setting Delete');
});

//template routes
Route::group(['tag' => 'Template'], function () {
    Route::get('dist_templates', [templateController::class, 'getDistTemplate'])->name('Dist Template');
});
