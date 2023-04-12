<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Distributor;
use App\Models\DistributorAddress;
use App\Models\DistributorType;
use App\Models\DistributorDocument;
use App\Models\DistributorDetailInfo;
use App\Models\DistributorRepresentative;
use App\Models\DistributorDirector;
use App\Models\DistributorAdditionalInfo;
use App\Models\DistributorStatus;
use App\Models\DistributorLedger;
use App\Models\DistributorFinancialPlanner;
use App\Models\DistributorApproval;

use App\Models\DistributorTemp;
use App\Models\DistributorTempAddress;
use App\Models\DistributorTempDetailInfo;
use App\Models\DistributorTempRepresentative;
use App\Models\DistributorTempDirector;
use App\Models\DistributorTempAdditionalInfo;
use App\Models\DistributorTempFinancialPlanner;
use App\Models\DistributorTempDocument;
use App\Models\DistributorUpdateApproval;
use App\Models\DistributorTempDocumentRemark;

use App\Manager\DistributorProfileUpdate;
use App\Manager\DistributorGetProfileUpdateDatas;

use App\Models\ManageRequiredDocument;

use App\Models\BankruptcySearch;
use App\Models\ProcessFlow;
use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use App\Models\TransactionLedger;
use App\Models\TransactionLedgerFimm;
use Carbon\Carbon;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Ixudra\Curl\Facades\Curl;
use Validator;
use DB;

use Illuminate\Support\Facades\Log;

class DistributorController extends Controller
{
    private $distributorProfileUpdate;
    private $distributorGetProfileUpdateDatas;

    public function __construct(DistributorProfileUpdate $distributorProfileUpdate, DistributorGetProfileUpdateDatas $distributorGetProfileUpdateDatas)
    {
        $this->distributorProfileUpdate = $distributorProfileUpdate;
        $this->distributorGetProfileUpdateDatas = $distributorGetProfileUpdateDatas;
    }

    public function getDistType(Request $request)
    {
        if ($request->USER_DIST_ID != null) {
            DB::enableQueryLog();
            $data =  Distributor::select('*')
                ->where('DIST_TYPE.DIST_ID', $request->USER_DIST_ID)
                ->join('distributor_management.DISTRIBUTOR_TYPE as DIST_TYPE', 'DISTRIBUTOR_ID', '=', 'DIST_TYPE.DIST_ID')
                ->join('distributor_management.DISTRIBUTOR_LEDGER as DIST_LEDGER', 'DIST_TYPE.DIST_TYPE_ID', '=', 'DIST_LEDGER.DIST_TYPE_ID')
                ->get();
            // dd(DB::getQueryLog());
            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => $data
            ]);
        } else {
            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => null
            ]);
        }
    }

    public function get(Request $request)
    {
        ini_set('max_execution_time', 180); //3 minutes
        //return $request->all();
        try {
            DB::enableQueryLog();
            $user = User::find($request->USER_ID);

            if ($user->USER_DIST_ID != null) {
                $data =  Distributor::select(
                    '*',
                    'sg2.SET_PARAM AS SET_PARAM_STATE',
                    'sg2.SETTING_GENERAL_ID AS STATE_ID',
                    'sg2.SET_CODE AS SET_CODE_STATE',
                    'sg1.SET_PARAM AS SET_PARAM_COUNTRY',
                    'sg1.SETTING_GENERAL_ID AS COUNTRY_ID',
                    'sg1.SET_CODE AS SET_CODE_COUNTRY',

                    'sg3.SET_PARAM AS BIZ_SET_PARAM_COUNTRY',
                    'sg3.SETTING_GENERAL_ID AS BIZ_COUNTRY_ID',
                    'sg3.SET_CODE AS BIZ_SET_CODE_COUNTRY',

                    'bank.SETTING_GENERAL_ID as BANKID',
                    'bank.SET_PARAM AS IssueBank'
                )
                    ->where('DISTRIBUTOR_ID', $user->USER_DIST_ID)
                    ->leftJoin('DISTRIBUTOR_ADDRESS', 'DISTRIBUTOR_ADDRESS.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_TYPE', 'DISTRIBUTOR_TYPE.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_LEDGER', 'DISTRIBUTOR_LEDGER.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_DETAIL_INFO', 'DISTRIBUTOR_DETAIL_INFO.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_STATUS', 'DISTRIBUTOR_STATUS.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('admin_management.SETTING_GENERAL AS sg1', 'sg1.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_COUNTRY')
                    ->leftJoin('admin_management.SETTING_GENERAL AS sg3', 'sg3.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_BIZ_COUNTRY')
                    ->leftJoin('admin_management.SETTING_CITY AS setting_city', 'setting_city.SETTING_CITY_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_CITY')
                    ->leftJoin('admin_management.SETTING_GENERAL AS sg2', 'sg2.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_STATE')
                    ->leftJoin('admin_management.SETTING_POSTAL AS setting_postal', 'setting_postal.SETTING_POSTCODE_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_POSTAL')
                    ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS distributor_type', 'distributor_type.DISTRIBUTOR_TYPE_ID', '=', 'DISTRIBUTOR_TYPE.DIST_TYPE')
                    ->leftJoin('admin_management.SETTING_GENERAL AS bank', 'bank.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_LEDGER.DIST_ISSUEBANK')
                    ->leftJoin('DISTRIBUTOR_FINANCIAL_PLANNER', 'DISTRIBUTOR_FINANCIAL_PLANNER.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    // ->join('admin_management.SETTING_CITY AS setting_city', 'setting_city.SETTING_CITY_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_CITY')
                    ->first(); // Show results of log

                // foreach($data as $element){
                //     $element->DIR_DATE_EFFECTIVE_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_EFFECTIVE));
                //     $element->DIR_DATE_END_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_END));
                //     $element->DIR_NAME_DISPLAY = $element->USER_SAL_NAME . ' ' . $element->DIR_NAME;
                // };

                $distributor_types =  DB::table('distributor_management.DISTRIBUTOR_TYPE AS A')
                    ->select('*')
                    ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS B', 'A.DIST_TYPE', '=', 'B.DISTRIBUTOR_TYPE_ID')
                    ->leftJoin('distributor_management.DISTRIBUTOR_LEDGER AS C', 'A.DIST_TYPE_ID', '=', 'C.DIST_TYPE_ID')
                    ->leftJoin('admin_management.SETTING_GENERAL AS D', 'D.SETTING_GENERAL_ID', '=', 'C.DIST_ISSUEBANK')
                    ->where("A.DIST_ID", $user->USER_DIST_ID)
                    ->get();

                $dataRepr = DistributorRepresentative::where('DIST_ID', $user->USER_DIST_ID)
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_REPRESENTATIVE.REPR_SALUTATION')
                    ->get();

                $dataAI = DistributorAdditionalInfo::where('DIST_ID', $user->USER_DIST_ID)
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_ADDITIONAL_INFO.ADD_SALUTATION')
                    ->get();

                $dataDir = DistributorDirector::where('DIST_ID', $user->USER_DIST_ID)
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_DIRECTOR.DIR_SALUTATION')
                    ->orderBy('CREATE_TIMESTAMP', 'desc')
                    ->get();

                foreach ($dataDir as $element) {
                    $element->DIR_DATE_EFFECTIVE_DISPLAY = ($element->DIR_DATE_EFFECTIVE != '0000-00-00') ? date('d-m-Y', strtotime($element->DIR_DATE_EFFECTIVE)) : '';
                    $element->DIR_DATE_END_DISPLAY = ($element->DIR_DATE_END != '0000-00-00') ? date('d-m-Y', strtotime($element->DIR_DATE_END)) : '';
                    $element->DIR_NAME_DISPLAY = $element->USER_SAL_NAME . ' ' . $element->DIR_NAME;
                    $element->DIR_PASSPORT_EXPIRY_DISPLAY = ($element->DIR_DATE_END != '0000-00-00') ? date('d-m-Y', strtotime($element->DIR_PASSPORT_EXPIRY)) : '';
                };

                $dataFP = DistributorFinancialPlanner::where('DIST_ID', $user->USER_DIST_ID)
                    ->select('*')
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_FINANCIAL_PLANNER.DIST_FP_SALUTATION')
                    ->first();

                $dataDoc = DistributorDocument::where('DIST_ID', $user->USER_DIST_ID)
                    ->where('DOCU_GROUP', '!=', 1)
                    ->where('DOCU_GROUP', '!=', 2)
                    ->get();

                foreach ($dataDoc as $element) {
                    $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
                };

                $data->DATAREPR = $dataRepr;
                $data->DATAAI = $dataAI;
                $data->DATADIR = $dataDir;
                $data->DATAFP = $dataFP;
                $data->DATADOC = $dataDoc;
                $data->DIST_TYPES = $distributor_types;

                // return $data;

                // dd(DB::getQueryLog());
                // dd(DB::getQueryLog($data)); // Show results of log
                http_response_code(200);
                return response([
                    'message' => 'Data successfully retrieved.',
                    'data' => $data
                ]);
            } else {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully retrieved.',
                    'data' => null
                ]);
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function getdistributorUpdateProfileDatas(Request $request)
    {
        ini_set('max_execution_time', 180); //3 minutes
        //return $request->all();
        try {
            DB::enableQueryLog();
            $user = User::find($request->USER_ID);

            if ($user->USER_DIST_ID != null) {
                $data =  Distributor::select(
                    '*',
                    'sg2.SET_PARAM AS SET_PARAM_STATE',
                    'sg2.SETTING_GENERAL_ID AS STATE_ID',
                    'sg2.SET_CODE AS SET_CODE_STATE',
                    'sg1.SET_PARAM AS SET_PARAM_COUNTRY',
                    'sg1.SETTING_GENERAL_ID AS COUNTRY_ID',
                    'sg1.SET_CODE AS SET_CODE_COUNTRY',

                    'sg3.SET_PARAM AS BIZ_SET_PARAM_COUNTRY',
                    'sg3.SETTING_GENERAL_ID AS BIZ_COUNTRY_ID',
                    'sg3.SET_CODE AS BIZ_SET_CODE_COUNTRY',

                    'bank.SETTING_GENERAL_ID as BANKID',
                    'bank.SET_PARAM AS IssueBank'
                )
                    ->where('DISTRIBUTOR_ID', $user->USER_DIST_ID)
                    ->leftJoin('DISTRIBUTOR_ADDRESS', 'DISTRIBUTOR_ADDRESS.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_TYPE', 'DISTRIBUTOR_TYPE.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_LEDGER', 'DISTRIBUTOR_LEDGER.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_DETAIL_INFO', 'DISTRIBUTOR_DETAIL_INFO.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('DISTRIBUTOR_STATUS', 'DISTRIBUTOR_STATUS.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    ->leftJoin('admin_management.SETTING_GENERAL AS sg1', 'sg1.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_COUNTRY')
                    ->leftJoin('admin_management.SETTING_GENERAL AS sg3', 'sg3.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_BIZ_COUNTRY')
                    ->leftJoin('admin_management.SETTING_CITY AS setting_city', 'setting_city.SETTING_CITY_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_CITY')
                    ->leftJoin('admin_management.SETTING_GENERAL AS sg2', 'sg2.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_STATE')
                    ->leftJoin('admin_management.SETTING_POSTAL AS setting_postal', 'setting_postal.SETTING_POSTCODE_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_POSTAL')
                    ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS distributor_type', 'distributor_type.DISTRIBUTOR_TYPE_ID', '=', 'DISTRIBUTOR_TYPE.DIST_TYPE')
                    ->leftJoin('admin_management.SETTING_GENERAL AS bank', 'bank.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_LEDGER.DIST_ISSUEBANK')
                    ->leftJoin('DISTRIBUTOR_FINANCIAL_PLANNER', 'DISTRIBUTOR_FINANCIAL_PLANNER.DIST_ID', '=', 'DISTRIBUTOR.DISTRIBUTOR_ID')
                    // ->join('admin_management.SETTING_CITY AS setting_city', 'setting_city.SETTING_CITY_ID', '=', 'DISTRIBUTOR_ADDRESS.DIST_CITY')
                    ->first(); // Show results of log

                // foreach($data as $element){
                //     $element->DIR_DATE_EFFECTIVE_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_EFFECTIVE));
                //     $element->DIR_DATE_END_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_END));
                //     $element->DIR_NAME_DISPLAY = $element->USER_SAL_NAME . ' ' . $element->DIR_NAME;
                // };

                $dataRepr = DistributorRepresentative::where('DIST_ID', $user->USER_DIST_ID)
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_REPRESENTATIVE.REPR_SALUTATION')
                    ->get();

                $dataAI = DistributorAdditionalInfo::where('DIST_ID', $user->USER_DIST_ID)
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_ADDITIONAL_INFO.ADD_SALUTATION')
                    ->get();

                $dataDir = DistributorDirector::where('DIST_ID', $user->USER_DIST_ID)
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_DIRECTOR.DIR_SALUTATION')
                    ->orderBy('CREATE_TIMESTAMP', 'desc')
                    ->get();

                foreach ($dataDir as $element) {
                    $element->DIR_DATE_EFFECTIVE_DISPLAY = ($element->DIR_DATE_EFFECTIVE != '0000-00-00') ? date('d-m-Y', strtotime($element->DIR_DATE_EFFECTIVE)) : '';
                    $element->DIR_DATE_END_DISPLAY = ($element->DIR_DATE_END != '0000-00-00') ? date('d-m-Y', strtotime($element->DIR_DATE_END)) : '';
                    $element->DIR_NAME_DISPLAY = $element->USER_SAL_NAME . ' ' . $element->DIR_NAME;
                    $element->DIR_PASSPORT_EXPIRY_DISPLAY = ($element->DIR_DATE_END != '0000-00-00') ? date('d-m-Y', strtotime($element->DIR_PASSPORT_EXPIRY)) : '';
                };

                $dataFP = DistributorFinancialPlanner::where('DIST_ID', $user->USER_DIST_ID)
                    ->select('*')
                    ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_FINANCIAL_PLANNER.DIST_FP_SALUTATION')
                    ->first();

                $dataDoc = DistributorDocument::where('DIST_ID', $user->USER_DIST_ID)
                    ->where('DOCU_GROUP', '!=', 1)
                    ->where('DOCU_GROUP', '!=', 2)
                    ->get();

                foreach ($dataDoc as $element) {
                    $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
                };

                $data->DATAREPR = $dataRepr;
                $data->DATAAI = $dataAI;
                $data->DATADIR = $dataDir;
                $data->DATAFP = $dataFP;
                $data->DATADOC = $dataDoc;

                /** Process to Get all Update Datas of Distributor */
                //Check and Get Updated Tab 1 Datas - Distributor Profile
                $distributorUpdateProfileData = DistributorTemp::where([
                    'PUBLISH_STATUS' => 0,
                    'TS_ID' => 0,
                    'DIST_ID' => $user->USER_DIST_ID
                ])->first();

                if (!$distributorUpdateProfileData) {
                    $distributorUpdateProfileData = DistributorTemp::where([
                        'DIST_TEMP_CATEGORY' => $request->DIST_TEMP_CATEGORY,
                        'PUBLISH_STATUS' => 0,
                        'DIST_ID' => $user->USER_DIST_ID
                    ])->orderBy('CREATE_TIMESTAMP', 'desc')->first();
                }

                if ($distributorUpdateProfileData) {
                    $data->PUBLISH_STATUS = $distributorUpdateProfileData->PUBLISH_STATUS;
                    $data->APPROVAL_STATUS = $distributorUpdateProfileData->TS_ID;
                    //Check and Get Updated Tab 1 Datas - Distributor Profile
                    $this->distributorGetProfileUpdateDatas->copyUpdateProfileDataToDistributor($data, $distributorUpdateProfileData);

                    //Check and Get Updated Tab 1 Datas - Addresses
                    $distributorUpdateAddressesData = DistributorTempAddress::where('DIST_TEMP_ID', $distributorUpdateProfileData->DIST_TEMP_ID)->first();
                    if ($distributorUpdateAddressesData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorAddressData($data, $distributorUpdateAddressesData);
                    }

                    //Check and Get Updated Tab 2 Datas - Details Information
                    $distributorUpdateDetailsData = DistributorTempDetailInfo::where('DIST_TEMP_ID', $distributorUpdateProfileData->DIST_TEMP_ID)->first();
                    if ($distributorUpdateDetailsData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorDetailsData($data, $distributorUpdateDetailsData);
                    }

                    //Check and Get Updated Tab 3 Datas - CEO
                    $distributorUpdateCeoRprData = DistributorTempRepresentative::where([
                        'DIST_TEMP_ID' => $distributorUpdateProfileData->DIST_TEMP_ID,
                        'REPR_TYPE' => 'CEO'
                    ])->first();
                    if ($distributorUpdateCeoRprData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorCeoRprData($data, $distributorUpdateCeoRprData, $dataRepr);
                    }

                    //Check and Get Updated Tab 3 Datas - Director

                    //Check and Get Updated Tab 4 Datas - AR
                    $distributorUpdateArRprData = DistributorTempRepresentative::where([
                        'DIST_TEMP_ID' => $distributorUpdateProfileData->DIST_TEMP_ID,
                        'REPR_TYPE' => 'AR'
                    ])->first();
                    if ($distributorUpdateArRprData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorArRprData($data, $distributorUpdateArRprData, $dataRepr);
                    }

                    //Check and Get Updated Tab 4 Datas -  AAR
                    $distributorUpdateAarRprData = DistributorTempRepresentative::where([
                        'DIST_TEMP_ID' => $distributorUpdateProfileData->DIST_TEMP_ID,
                        'REPR_TYPE' => 'AAR'
                    ])->first();
                    if ($distributorUpdateAarRprData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorAarRprData($data, $distributorUpdateAarRprData, $dataRepr);
                    }

                    //Check and Get Updated ab 5 Datas - HOD_COMPL
                    $distributorUpdateHOD_COMPLData = DistributorTempAdditionalInfo::where([
                        'DIST_TEMP_ID' => $distributorUpdateProfileData->DIST_TEMP_ID,
                        'ADD_TYPE' => 'HOD_COMPL'
                    ])->first();
                    if ($distributorUpdateHOD_COMPLData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorHOD_COMPLData($data, $distributorUpdateHOD_COMPLData, $dataAI);
                    }

                    //Check and Get Updated ab 5 Datas - HOD_UTS
                    $distributorUpdateHOD_UTSData = DistributorTempAdditionalInfo::where([
                        'DIST_TEMP_ID' => $distributorUpdateProfileData->DIST_TEMP_ID,
                        'ADD_TYPE' => 'HOD_UTS'
                    ])->first();
                    if ($distributorUpdateHOD_UTSData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorHOD_UTSData($data, $distributorUpdateHOD_UTSData, $dataAI);
                    }

                    //Check and Get Updated ab 5 Datas - HOD_PRS
                    $distributorUpdateHOD_PRSData = DistributorTempAdditionalInfo::where([
                        'DIST_TEMP_ID' => $distributorUpdateProfileData->DIST_TEMP_ID,
                        'ADD_TYPE' => 'HOD_PRS'
                    ])->first();
                    if ($distributorUpdateHOD_PRSData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorHOD_PRSData($data, $distributorUpdateHOD_PRSData, $dataAI);
                    }

                    //Check and Get Updated ab 5 Datas - HOD_TRAIN
                    $distributorUpdateHOD_TRAINData = DistributorTempAdditionalInfo::where([
                        'DIST_TEMP_ID' => $distributorUpdateProfileData->DIST_TEMP_ID,
                        'ADD_TYPE' => 'HOD_TRAIN'
                    ])->first();
                    if ($distributorUpdateHOD_TRAINData) {
                        $this->distributorGetProfileUpdateDatas->copyUpdateDistributorHOD_TRAINData($data, $distributorUpdateHOD_TRAINData, $dataAI);
                    }
                }
                /** Process to Get all Update Datas of Distributor */

                // dd(DB::getQueryLog());
                // dd(DB::getQueryLog($data)); // Show results of log
                http_response_code(200);
                return response([
                    'message' => 'Data successfully retrieved.',
                    'data' => $data
                ]);
            } else {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully retrieved.',
                    'data' => null
                ]);
            }
        } catch (\Exception $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function  update(Request $request)
    {

        try {
            $user = User::where('USER_ID', $request->USER_ID)->first();
            if ($user) {
                $request->DISTRIBUTOR_ID = $user->USER_DIST_ID;
            }
            $d = 0;
            $data = Distributor::where('DISTRIBUTOR_ID', $request->DISTRIBUTOR_ID)->first();
            if (!$data) {
                $d = 0;
                $data = new Distributor;
            } else {
                $d = 1;
            }

            $data->DIST_NAME = $request->DIST_NAME;
            $data->DIST_CODE = $request->DIST_CODE;
            $data->DIST_REGI_NUM1 = $request->DIST_REGI_NUM1;
            $data->DIST_REGI_NUM2 = $request->DIST_REGI_NUM2;
            $data->DIST_DATE_INCORP = $request->DIST_DATE_INCORP;
            $data->DIST_TYPE_SETUP = $request->DIST_TYPE_SETUP;
            $data->DIST_PHONE_NUMBER = $request->DIST_PHONE_NUMBER;
            $data->DIST_PHONE_EXTENSION = $request->DIST_PHONE_EXTENSION;
            $data->DIST_MOBILE_NUMBER = $request->DIST_MOBILE_NUMBER;
            // if ($request->DIST_PHONE_NUMBER == null) {
            //     $data->DIST_PHONE_NUMBER = "";
            // } else {
            //     $data->DIST_PHONE_NUMBER = $request->DIST_PHONE_NUMBER;
            // }
            // if ($request->DIST_PHONE_EXTENSION == null) {
            //     $data->DIST_PHONE_EXTENSION = "";
            // } else {
            //     $data->DIST_PHONE_EXTENSION = $request->DIST_PHONE_EXTENSION;
            // }
            // if ($request->DIST_MOBILE_NUMBER == null) {
            //     $data->DIST_PHONE_NUMBER = "";
            // } else {
            //     $data->DIST_MOBILE_NUMBER = $request->DIST_MOBILE_NUMBER;
            // }
            if ($request->DIST_EMAIL == null) {
                $data->DIST_EMAIL = "";
            } else {
                $data->DIST_EMAIL = $request->DIST_EMAIL;
            }
            $data->DIST_FAX_NUMBER = $request->DIST_FAX_NUMBER ?? "";
            $data->DIST_COMPANY_WEBSITE = $request->DIST_COMPANY_WEBSITE;
            $data->save();
            $user = User::where('USER_ID', $request->USER_ID)->first();
            $user->USER_DIST_ID = $data->DISTRIBUTOR_ID;
            $user->save();

            //ID MASKING INTEGRATION By Tahmina
            if ($d == 0) {
                $datacount =  DB::table('admin_management.DISTRIBUTOR_ID_MASKING_SETTING')->count();
                if ($datacount == 0) {
                    DB::table('admin_management.DISTRIBUTOR_ID_MASKING_SETTING')->insert([
                        'PREFIX' => 0,
                        'RUN_NO' => 0,
                        'CURRENT_RUN_NO' => 0, // Default value
                        'DESCRIPTION' => 'Default Entry',
                        'STATUS' => 'ACTIVE',
                    ]);
                }
                $datamasking = DB::table('admin_management.DISTRIBUTOR_ID_MASKING_SETTING as ID_MASKING_SETTING')
                    ->select('ID_MASKING_SETTING.CURRENT_RUN_NO AS CURRENT_RUN_NO', 'ID_MASKING_SETTING.DISTRIBUTOR_MASKING_ID AS DISTRIBUTOR_MASKING_ID', 'ID_MASKING_SETTING.PREFIX AS PREFIX')
                    ->where(['ID_MASKING_SETTING.STATUS' => 'ACTIVE'])
                    ->first();

                $d_run_no = $datamasking->CURRENT_RUN_NO + 1;


                $updatemasking = DB::table('distributor_management.DISTRIBUTOR as DISTRIBUTOR')
                    ->where('DISTRIBUTOR.DISTRIBUTOR_ID', '=', $data->DISTRIBUTOR_ID)
                    ->update(['DISTRIBUTOR.DIST_RUN_NO' => $d_run_no]);

                $updatesetting = DB::table('admin_management.DISTRIBUTOR_ID_MASKING_SETTING as ID_MASKING_SETTING')
                    ->where('ID_MASKING_SETTING.DISTRIBUTOR_MASKING_ID', '=', $datamasking->DISTRIBUTOR_MASKING_ID)
                    ->update(['ID_MASKING_SETTING.CURRENT_RUN_NO' => $d_run_no]);
            }
            // End of Masking


            // 1.create distributor profile
            $file = $request->FILEOBJECT;
            $fileId = $request->FILEOBJECTID;


            if ($request->FILEOBJECT != null && $request->hasFile('FILEOBJECT')) {
                $file = $request->FILEOBJECT;
                $fileId = $request->FILEOBJECTID;

                foreach ($file as $key => $item) {
                    $itemFile = $item;
                    // dd($itemFile);
                    $contents = $itemFile->openFile()->fread($itemFile->getSize());

                    $doc = DistributorDocument::where('REQ_DOCU_ID', $fileId[$key])->first();
                    if (!$doc) {
                        $doc = new DistributorDocument;
                    }
                    $doc->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc->DOCU_GROUP = 1;
                    $doc->DOCU_BLOB = $contents;
                    $doc->REQ_DOCU_ID = $fileId[$key];
                    $doc->DOCU_ORIGINAL_NAME = $itemFile->getClientOriginalName();
                    $doc->DOCU_FILESIZE = $itemFile->getSize();
                    $doc->DOCU_FILETYPE = $itemFile->getClientOriginalExtension();
                    $doc->DOCU_MIMETYPE = $itemFile->getMimeType();
                    $doc->save();
                }
            }


            //return $request->all();
            if ($request->FILEOBJECT2 != null) {
                $file2 = $request->FILEOBJECT2;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $docId2 = DistributorDocument::where('DOCU_GROUP', 8)->where('DIST_ID', $request->DISTRIBUTOR_ID)->first();
                if ($docId2 != null) {
                    $doc2 = DistributorDocument::where('DOCU_GROUP', 8)->where('DIST_ID', $request->DISTRIBUTOR_ID)->first();
                } else {
                    $doc2 = new DistributorDocument;
                }
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 8;
                $doc2->DOCU_BLOB = $contents2;
                // $doc->REQ_DOCU_ID = $fileId[$key];
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            // Start File Upload Conpany Logo =======================
            if ($request->hasFile('companyLogo')) {
                DistributorDocument::where('DOCU_GROUP', 3)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->companyLogo;
                $contents2 = $file2->openFile()->fread($file2->getSize());
                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 3;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }
            // file delet
            if ($request->hasFile('ssmForm8')) {
                DistributorDocument::where('DOCU_GROUP', 5)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm8;
                $contents2 = $file2->openFile()->fread($file2->getSize());
                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 5;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->hasFile('ssmForm9')) {
                DistributorDocument::where('DOCU_GROUP', 4)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm9;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 4;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('ssmForm13')) {
                DistributorDocument::where('DOCU_GROUP', 9)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm13;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 9;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }



            if ($request->hasFile('receipt')) {

                DistributorDocument::where('DOCU_GROUP', 7)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->receipt;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 7;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('BODApprove')) {
                DistributorDocument::where('DOCU_GROUP', 6)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->BODApprove;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 6;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }



            if ($request->hasFile('uploadCMSLOnly')) {
                DistributorDocument::where('DOCU_GROUP', 10)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->uploadCMSLOnly;
                $contents2 = $file2->openFile()->fread($file2->getSize());
                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 10;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('DIST_INSURANCE_POLICY')) {
                DistributorDocument::where('DOCU_GROUP', 15)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->DIST_INSURANCE_POLICY;
                $contents2 = $file2->openFile()->fread($file2->getSize());
                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 15;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('uploadCMSLorMOFLicense')) {
                DistributorDocument::where('DOCU_GROUP', 11)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->uploadCMSLorMOFLicense;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 11;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('ssmForm49')) {
                DistributorDocument::where('DOCU_GROUP', 12)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm49;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 12;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->hasFile('complianceDeclaration')) {
                DistributorDocument::where('DOCU_GROUP', 13)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->complianceDeclaration;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 13;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->hasFile('ssmForm24')) {
                DistributorDocument::where('DOCU_GROUP', 14)->where('DIST_ID', $data->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm24;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 14;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            // End File Upload


            //return $data;

            $dataAddr = DistributorAddress::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
            if (!$dataAddr) {
                $dataAddr = new DistributorAddress;
            }
            $dataAddr->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAddr->DIST_ADDR_1 = $request->DIST_ADDR_1;
            $dataAddr->DIST_ADDR_2 = $request->DIST_ADDR_2;
            $dataAddr->DIST_ADDR_3 = $request->DIST_ADDR_3;
            if ($request->COUNTRY_CODE != 'MY') {
                $dataAddr->DIST_POSTAL2 = $request->DIST_POSTAL;
                $dataAddr->DIST_CITY2 = $request->DIST_CITY;
                $dataAddr->DIST_STATE2 = $request->DIST_STATE;
            } else {
                $dataAddr->DIST_POSTAL = $request->DIST_POSTAL;
                $dataAddr->DIST_CITY = $request->DIST_CITY;
                $dataAddr->DIST_STATE = $request->DIST_STATE;
            }

            $dataAddr->DIST_BIZ_ADDR_1 = $request->DIST_BIZ_ADDR_1;
            $dataAddr->DIST_BIZ_ADDR_2 = $request->DIST_BIZ_ADDR_2;
            $dataAddr->DIST_BIZ_ADDR_3 = $request->DIST_BIZ_ADDR_3;
            if ($request->BIZ_COUNTRY_CODE != 'MY') {
                $dataAddr->DIST_BIZ_POSTAL2 = $request->DIST_BIZ_POSTAL;
                $dataAddr->DIST_BIZ_CITY2 = $request->DIST_BIZ_CITY;
                $dataAddr->DIST_BIZ_STATE2 = $request->DIST_BIZ_STATE;
            } else {
                $dataAddr->DIST_BIZ_POSTAL = $request->DIST_BIZ_POSTAL;
                $dataAddr->DIST_BIZ_CITY = $request->DIST_BIZ_CITY;
                $dataAddr->DIST_BIZ_STATE = $request->DIST_BIZ_STATE;
            }
            $dataAddr->DIST_BIZ_COUNTRY = $request->DIST_BIZ_COUNTRY;
            $dataAddr->DIST_COUNTRY = $request->DIST_COUNTRY;
            $dataAddr->DIST_ADDR_SAME = ($request->DIST_ADDR_SAME  == 'true') ? 1 : 0;
            $dataAddr->save();

            if ($request->DIST_TYPE && $request->DIST_TYPE != 'undefined') {
                $dataDistType = DistributorType::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
                if (!$dataDistType) {
                    $dataDistType = new DistributorType;
                }
                $dataDistType->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataDistType->DIST_TYPE = $request->DIST_TYPE;
                $dataDistType->save();
            }
            //return $request->all();

            //2. Details Information
            $dataInfo = DistributorDetailInfo::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
            if (!$dataInfo) {
                $dataInfo = new DistributorDetailInfo;
            }
            $dataInfo->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataInfo->DIST_PAID_UP_CAPITAL = str_replace(['RM', ','], '', $request->DIST_PAID_UP_CAPITAL);
            //  $dataInfo->DIST_PAID_UP_CAPITAL = preg_replace('/\s+/', '', $request->DIST_PAID_UP_CAPITAL);
            $dataInfo->DIST_TYPE_STRUCTURE = $request->DIST_TYPE_STRUCTURE;
            $dataInfo->DIST_MARKETING_APPROACH = $request->DIST_MARKETING_APPROACH;
            $dataInfo->DIST_NUM_DIST_POINT = $request->DIST_NUM_DIST_POINT;
            $dataInfo->DIST_NUM_CONSULTANT = $request->DIST_NUM_CONSULTANT;
            $dataInfo->DIST_INSURANCE = $request->DIST_INSURANCE;
            $dataInfo->DIST_EXPIRED_DATE = $request->DIST_EXPIRED_DATE;
            $dataInfo->save();


            // // // $dataCEO = new DistributorRepresentative;
            if ($request->CEO_REPR_TYPE != null) {
                $dataCEO = DistributorRepresentative::where('DIST_ID', $data->DISTRIBUTOR_ID)
                    ->where('REPR_TYPE', $request->CEO_REPR_TYPE)
                    ->first();
                if ($dataCEO) {
                    $dataCEO = DistributorRepresentative::where('DIST_ID', $data->DISTRIBUTOR_ID)
                        ->where('REPR_TYPE', $request->CEO_REPR_TYPE)
                        ->first();
                } else {
                    $dataCEO = new DistributorRepresentative;
                }
                $dataCEO->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataCEO->REPR_TYPE = $request->CEO_REPR_TYPE;
                $dataCEO->REPR_SALUTATION = $request->CEO_REPR_SALUTATION;
                $dataCEO->REPR_NAME = $request->CEO_REPR_NAME;
                $dataCEO->REPR_POSITION = $request->CEO_REPR_POSITION;
                $dataCEO->REPR_CITIZEN = $request->CEO_REPR_CITIZEN;
                $dataCEO->REPR_NRIC = $request->CEO_REPR_NRIC;
                $dataCEO->REPR_PASS_NUM = $request->CEO_REPR_PASS_NUM;
                $dataCEO->REPR_PASS_EXP = $request->CEO_REPR_PASS_EXP;
                $dataCEO->REPR_TELEPHONE = $request->CEO_REPR_TELEPHONE;
                $dataCEO->REPR_PHONE_EXTENSION = $request->CEO_REPR_PHONE_EXTENSION;
                $dataCEO->REPR_MOBILE_NUMBER = $request->CEO_REPR_MOBILE_NUMBER;
                $dataCEO->REPR_EMAIL = $request->CEO_REPR_EMAIL;

                if ($request->CEO_isRepresentative == 1 || $request->CEO_isRepresentative == 'true') {
                    $dataCEO->IS_REPRESENTATIVE = 1;
                } else {
                    $dataCEO->IS_REPRESENTATIVE = 0;
                }

                $dataCEO->save();
            }


            foreach (json_decode($request->DELETED_DIR) as $element) {
                $deletedDir = DistributorDirector::find($element->DIST_DIR_ID);
                $deletedDir->delete();
            }

            foreach (json_decode($request->DIR_LIST) as $item) {
                if ($item->DIST_DIR_ID != null) {
                    $dataDir = DistributorDirector::where('DIST_DIR_ID', $item->DIST_DIR_ID)->first();
                } else {
                    $dataDir = new DistributorDirector;
                }
                $dataDir->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataDir->DIR_SALUTATION = $item->DIR_SALUTATION;
                $dataDir->DIR_NAME = $item->DIR_NAME;
                $dataDir->DIR_NRIC = str_replace("-", "", $item->DIR_NRIC);
                $dataDir->DIR_DATE_EFFECTIVE = $item->DIR_DATE_EFFECTIVE;
                $dataDir->DIR_DATE_END = $item->DIR_DATE_END;
                if (isset($item->DIR_PASSPORT)) {
                    $dataDir->DIR_PASS_NUM = $item->DIR_PASSPORT;
                }
                if (isset($item->DIR_PASSPORT_EXPIRY)) {
                    $dataDir->DIR_PASS_EXPIRY = $item->DIR_PASSPORT_EXPIRY;
                }
                $dataDir->save();
            }

            if ($request->AR_REPR_TYPE) {
                $dataAR = DistributorRepresentative::where('DIST_ID', $data->DISTRIBUTOR_ID)
                    ->where('REPR_TYPE', $request->AR_REPR_TYPE)
                    ->first();
                if (!$dataAR) {
                    $dataAR = new DistributorRepresentative;
                }

                $dataAR->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataAR->REPR_TYPE = $request->AR_REPR_TYPE;
                $dataAR->REPR_SALUTATION = $request->AR_REPR_SALUTATION;
                $dataAR->REPR_NAME = $request->AR_REPR_NAME;
                $dataAR->REPR_POSITION = $request->AR_REPR_POSITION;
                $dataAR->REPR_CITIZEN = $request->AR_REPR_CITIZEN;
                $dataAR->REPR_NRIC = $request->AR_REPR_NRIC;
                $dataAR->REPR_PASS_NUM = $request->AR_REPR_PASS_NUM;
                $dataAR->REPR_PASS_EXP = $request->AR_REPR_PASS_EXP;
                $dataAR->REPR_TELEPHONE = $request->AR_REPR_TELEPHONE;
                $dataAR->REPR_PHONE_EXTENSION = $request->AR_REPR_PHONE_EXTENSION;
                $dataAR->REPR_MOBILE_NUMBER = $request->AR_REPR_MOBILE_NUMBER;
                $dataAR->REPR_EMAIL = $request->AR_REPR_EMAIL;
                $dataAR->save();
            }

            if ($request->AAR_REPR_TYPE) {
                $dataAAR = DistributorRepresentative::where('DIST_ID', $data->DISTRIBUTOR_ID)
                    ->where('REPR_TYPE', $request->AAR_REPR_TYPE)
                    ->first();
                if (!$dataAAR) {
                    $dataAAR = new DistributorRepresentative;
                }
                $dataAAR->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataAAR->REPR_TYPE = $request->AAR_REPR_TYPE;
                $dataAAR->REPR_SALUTATION = $request->AAR_REPR_SALUTATION;
                $dataAAR->REPR_NAME = $request->AAR_REPR_NAME;
                $dataAAR->REPR_POSITION = $request->AAR_REPR_POSITION;
                $dataAAR->REPR_CITIZEN = $request->AAR_REPR_CITIZEN;
                $dataAAR->REPR_NRIC = $request->AAR_REPR_NRIC;
                $dataAAR->REPR_PASS_NUM = $request->AAR_REPR_PASS_NUM;
                $dataAAR->REPR_PASS_EXP = $request->AAR_REPR_PASS_EXP;
                $dataAAR->REPR_TELEPHONE = $request->AAR_REPR_TELEPHONE;
                $dataAAR->REPR_PHONE_EXTENSION = $request->AAR_REPR_PHONE_EXTENSION;
                $dataAAR->REPR_MOBILE_NUMBER = $request->AAR_REPR_MOBILE_NUMBER;
                $dataAAR->REPR_EMAIL = $request->AAR_REPR_EMAIL;
                $dataAAR->save();
            }
            if ($request->COMPL_TYPE) {
                $dataAICompl = DistributorAdditionalInfo::where('DIST_ID', $data->DISTRIBUTOR_ID)
                    ->where('ADD_TYPE', $request->COMPL_TYPE)
                    ->first();
                if (!$dataAICompl) {
                    $dataAICompl = new DistributorAdditionalInfo;
                }
                $dataAICompl->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataAICompl->ADD_TYPE = $request->COMPL_TYPE;
                $dataAICompl->ADD_SALUTATION = $request->COMPL_SALUTATION;
                $dataAICompl->ADD_NAME = $request->COMPL_NAME;
                $dataAICompl->ADD_TELEPHONE = $request->COMPL_TELEPHONE;
                $dataAICompl->ADD_PHONE_EXTENSION = $request->COMPL_PHONE_EXTENSION;
                $dataAICompl->ADD_EMAIL = $request->COMPL_EMAIL;
                $dataAICompl->ADD_MOBILE_NUMBER = $request->COMPL_MOBILE_NUMBER;
                $dataAICompl->save();
            }

            if ($request->UTS_TYPE) {
                $dataAIUts = DistributorAdditionalInfo::where('DIST_ID', $data->DISTRIBUTOR_ID)
                    ->where('ADD_TYPE', $request->UTS_TYPE)
                    ->first();
                if (!$dataAIUts) {
                    $dataAIUts = new DistributorAdditionalInfo;
                }
                $dataAIUts->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataAIUts->ADD_TYPE = $request->UTS_TYPE;
                $dataAIUts->ADD_SALUTATION = $request->UTS_SALUTATION;
                $dataAIUts->ADD_NAME = $request->UTS_NAME;
                $dataAIUts->ADD_TELEPHONE = $request->UTS_TELEPHONE;
                $dataAIUts->ADD_PHONE_EXTENSION = $request->UTS_PHONE_EXTENSION;
                $dataAIUts->ADD_EMAIL = $request->UTS_EMAIL;
                $dataAIUts->ADD_MOBILE_NUMBER = $request->UTS_MOBILE_NUMBER;
                $dataAIUts->save();
            }

            if ($request->PRS_TYPE) {
                $dataAIPrs = DistributorAdditionalInfo::where('DIST_ID', $data->DISTRIBUTOR_ID)
                    ->where('ADD_TYPE', $request->PRS_TYPE)
                    ->first();
                if (!$dataAIPrs) {
                    $dataAIPrs = new DistributorAdditionalInfo;
                }
                $dataAIPrs->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataAIPrs->ADD_TYPE = $request->PRS_TYPE;
                $dataAIPrs->ADD_SALUTATION = $request->PRS_SALUTATION;
                $dataAIPrs->ADD_NAME = $request->PRS_NAME;
                $dataAIPrs->ADD_TELEPHONE = $request->PRS_TELEPHONE;
                $dataAIPrs->ADD_PHONE_EXTENSION = $request->PRS_PHONE_EXTENSION;
                $dataAIPrs->ADD_EMAIL = $request->PRS_EMAIL;
                $dataAIPrs->ADD_MOBILE_NUMBER = $request->PRS_MOBILE_NUMBER;
                $dataAIPrs->save();
            }

            if ($request->TRAIN_TYPE) {
                $dataAITrain = DistributorAdditionalInfo::where('DIST_ID', $data->DISTRIBUTOR_ID)
                    ->where('ADD_TYPE', $request->TRAIN_TYPE)
                    ->first();
                if (!$dataAITrain) {
                    $dataAITrain = new DistributorAdditionalInfo;
                }
                $dataAITrain->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataAITrain->ADD_TYPE = $request->TRAIN_TYPE;
                $dataAITrain->ADD_SALUTATION = $request->TRAIN_SALUTATION;
                $dataAITrain->ADD_NAME = $request->TRAIN_NAME;
                $dataAITrain->ADD_TELEPHONE = $request->TRAIN_TELEPHONE;
                $dataAITrain->ADD_PHONE_EXTENSION = $request->TRAIN_PHONE_EXTENSION;
                $dataAITrain->ADD_EMAIL = $request->TRAIN_EMAIL;
                $dataAITrain->ADD_MOBILE_NUMBER = $request->TRAIN_MOBILE_NUMBER;
                $dataAITrain->save();
            }

            // Payment Details
            $dataLedger = DistributorLedger::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();

            if (!$dataLedger) {
                $dataLedger = new DistributorLedger;
            }
            $dataLedger->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataLedger->DIST_TYPE_ID = $request->DIST_TYPE;
            $dataLedger->DIST_TRANS_REF = $request->DIST_TRANS_REF;
            $dataLedger->DIST_TRANS_DATE = $request->DIST_TRANS_DATE;
            $dataLedger->DIST_TRANS_TYPE = $request->DIST_TRANS_TYPE;
            $dataLedger->DIST_ISSUEBANK = $request->DIST_ISSUE_BANK;
            $dataLedger->DIST_ACC_AMOUNT = str_replace([' ', 'RM', ','], '', $request->DIST_ACC_AMOUNT);
            $dataLedger->save();


            // Payment details in Finance Module;
            $financeLedger = TransactionLedger::where('DISTRIBUTOR_ID', $data->DISTRIBUTOR_ID)->first();
            $acc_code_type = DB::table('admin_management.FINANCE_ACC_CODE')->where('FINANCE_ACC_CODE_NAME', 'DISTRIBUTOR REGISTRATION')->first();
            if (!$financeLedger) {
                $financeLedger = new TransactionLedger;
            }

            $financeLedger->DISTRIBUTOR_ID = $data->DISTRIBUTOR_ID;
            $financeLedger->DISTRIBUTOR_TYPE_ID = $request->DIST_TYPE;
            $financeLedger->FIN_TRANS_TYPE = 1;
            $financeLedger->DIST_TRANS_TYPE = $request->DIST_TRANS_TYPE;
            $financeLedger->ACC_CODE_TYPE = $acc_code_type->FINANCE_ACC_CODE_ID;
            $financeLedger->CURRENCY = null;
            $financeLedger->INVOICE_NUMBER = null;
            $financeLedger->DEBIT_AMOUNT = str_replace([' ', 'RM', ','], '', $request->DIST_ACC_AMOUNT);
            $financeLedger->CREDIT_AMOUNT = null;
            $financeLedger->OTHERS_AMOUNT = null;
            $financeLedger->PAYMENT_REFERENCE = $request->DIST_TRANS_REF;
            $financeLedger->TRANS_DATE = $request->DIST_TRANS_DATE;
            $financeLedger->TRANS_BANK = $request->DIST_ISSUE_BANK;
            $financeLedger->TRANS_REMARK = null;
            $financeLedger->RETURN_ACKNOWLEDGED_REMARKS = null;
            $financeLedger->OTHERS_REMARKS = null;
            $financeLedger->TRANS_STATUS = 15;
            $financeLedger->DIST_LEDGER_ID = null;
            $financeLedger->TOTAL_CONS = null;
            $financeLedger->CREATE_BY = $request->CREATE_BY;

            $finance_ledger_status = $financeLedger->save();

            if ($finance_ledger_status) {
                $financeLedgerFimm = TransactionLedgerFimm::where('DISTRIBUTOR_ID', $data->DISTRIBUTOR_ID)->first();
                if (!$financeLedgerFimm) {
                    $financeLedgerFimm = new TransactionLedgerFimm;
                }
                $financeLedgerFimm->DISTRIBUTOR_ID = $data->DISTRIBUTOR_ID;
                $financeLedgerFimm->DISTRIBUTOR_TYPE_ID = $request->DIST_TYPE;
                $financeLedgerFimm->FIN_TRANS_TYPE = 1;
                $financeLedgerFimm->DIST_TRANS_TYPE = $request->DIST_TRANS_TYPE;
                $financeLedgerFimm->ACC_CODE_TYPE = $acc_code_type->FINANCE_ACC_CODE_ID;
                $financeLedgerFimm->CURRENCY = null;
                $financeLedgerFimm->INVOICE_NUMBER = null;
                $financeLedgerFimm->DEBIT_AMOUNT = null;
                $financeLedgerFimm->CREDIT_AMOUNT = str_replace([' ', 'RM', ','], '', $request->DIST_ACC_AMOUNT);;
                $financeLedgerFimm->OTHERS_AMOUNT = null;
                $financeLedgerFimm->PAYMENT_REFERENCE = $request->DIST_TRANS_REF;
                $financeLedgerFimm->TRANS_DATE = $request->DIST_TRANS_DATE;
                $financeLedgerFimm->TRANS_BANK = $request->DIST_ISSUE_BANK;
                $financeLedgerFimm->TRANS_REMARK = null;
                $financeLedgerFimm->RETURN_ACKNOWLEDGED_REMARKS = null;
                $financeLedgerFimm->OTHERS_REMARKS = null;
                $financeLedgerFimm->TRANS_STATUS = 15;
                $financeLedgerFimm->DIST_LEDGER_ID = null;
                $financeLedgerFimm->TOTAL_CONS = null;
                $financeLedgerFimm->TRANSACTION_LEDGER_ID = $financeLedger->LEDGER_ID;
                $financeLedgerFimm->CREATE_BY = $request->CREATE_BY;

                $financeLedgerFimm->save();
            }


            // Financial Planner
            if ($request->DIST_TYPE == 4 || $request->DIST_TYPE == 5) {
                $dataFP = DistributorFinancialPlanner::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
                if (!$dataFP) {
                    $dataFP = new DistributorFinancialPlanner;
                }
                $dataFP->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataFP->DIST_TYPE_ID = $dataDistType->DIST_TYPE_ID;
                $dataFP->DIST_FINANCIAL_INSTITUTION = $request->DIST_FINANCIAL_INSTITUTION;
                $dataFP->DIST_FP_SALUTATION = $request->DIST_FP_SALUTATION;
                $dataFP->DIST_FP_NAME = $request->DIST_FP_NAME;
                $dataFP->DIST_FP_CSMRL = $request->DIST_FP_CSMRL;
                $dataFP->DIST_FP_CITIZEN = $request->DIST_FP_CITIZEN;
                $dataFP->DIST_FP_NRIC = $request->DIST_FP_NRIC;
                $dataFP->DIST_FP_PASS_NUM = $request->DIST_FP_PASS_NUM;
                $dataFP->DIST_FP_PASS_EXPIRY = $request->DIST_FP_PASS_EXPIRY;
                $dataFP->save();
            }

            $dataStatus = DistributorStatus::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
            if (!$dataStatus) {
                $dataStatus = new DistributorStatus;
            }
            $dataStatus->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataStatus->DIST_APPROVAL_STATUS = $request->DIST_PUBLISH_STATUS ?? 0;
            $dataStatus->DIST_PUBLISH_STATUS  = ($request->DIST_PUBLISH_STATUS == 1)  ? 0 : 1;
            $dataStatus->save();
            if ($request->DIST_PUBLISH_STATUS == 2) {
                foreach (json_decode($request->APPR_LIST) as $item) {
                    $dataApproval = DistributorApproval::where('DIST_ID', $data->DISTRIBUTOR_ID)
                        ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->where('APPROVAL_LEVEL_ID', 7)->first();
                    if (!$dataApproval) {
                        $dataApproval = new DistributorApproval;
                    }
                    $dataApproval->DIST_ID = $data->DISTRIBUTOR_ID;
                    $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                    $dataApproval->APPROVAL_LEVEL_ID = 7;
                    $dataApproval->APPROVAL_INDEX = 1;
                    $dataApproval->APPROVAL_STATUS = 2;
                    $dataApproval->save();

                    $notification = new ManageNotification();

                    $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, "(DIST) New registration (" . $data->DIST_NAME . " ) for RD Approval", "distributor-SubmissionList-rdApproval");
                }
            }

            http_response_code(200);
            return response([
                'message' => 'Data successfully updated.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4101
            ], 400);
        }
    }

    public function create(Request $request)
    {
        try {
            DB::enableQueryLog();


            $data = new Distributor;
            $data->DIST_NAME = $request->DIST_NAME;
            $data->DIST_CODE = $request->DIST_CODE;
            $data->DIST_REGI_NUM1 = $request->DIST_REGI_NUM1;
            $data->DIST_REGI_NUM2 = $request->DIST_REGI_NUM2;
            $data->DIST_DATE_INCORP = $request->DIST_DATE_INCORP;
            $data->DIST_TYPE_SETUP = $request->DIST_TYPE_SETUP;
            if ($request->DIST_PHONE_NUMBER == null) {
                $data->DIST_PHONE_NUMBER = "-";
            } else {
                $data->DIST_PHONE_NUMBER = $request->DIST_PHONE_NUMBER;
            }
            if ($request->DIST_PHONE_EXTENSION == null) {
                $data->DIST_PHONE_EXTENSION = "-";
            } else {
                $data->DIST_PHONE_EXTENSION = $request->DIST_PHONE_EXTENSION;
            }
            if ($request->DIST_MOBILE_NUMBER == null) {
                $data->DIST_PHONE_NUMBER = "-";
            } else {
                $data->DIST_MOBILE_NUMBER = $request->DIST_MOBILE_NUMBER;
            }
            if ($request->DIST_EMAIL == null) {
                $data->DIST_EMAIL = "-";
            } else {
                $data->DIST_EMAIL = $request->DIST_EMAIL;
            }
            $data->DIST_FAX_NUMBER = $request->DIST_FAX_NUMBER ?? "";
            $data->DIST_COMPANY_WEBSITE = $request->DIST_COMPANY_WEBSITE;
            $data->save();

            //ID MASKING INTEGRATION By Tahmina
            $datamasking = DB::table('admin_management.DISTRIBUTOR_ID_MASKING_SETTING as ID_MASKING_SETTING')
                ->select('ID_MASKING_SETTING.CURRENT_RUN_NO AS CURRENT_RUN_NO', 'ID_MASKING_SETTING.DISTRIBUTOR_MASKING_ID AS DISTRIBUTOR_MASKING_ID', 'ID_MASKING_SETTING.PREFIX AS PREFIX')
                ->where(['ID_MASKING_SETTING.STATUS' => 'ACTIVE'])
                ->first();

            $d_run_no = $datamasking->CURRENT_RUN_NO + 1;

            $update = DB::table('distributor_management.DISTRIBUTOR as DISTRIBUTOR')
                ->where('DISTRIBUTOR.DISTRIBUTOR_ID', '=', $data->DISTRIBUTOR_ID)
                ->update(['DISTRIBUTOR.DIST_RUN_NO' => $d_run_no]);

            $updatesetting = DB::table('admin_management.DISTRIBUTOR_ID_MASKING_SETTING as ID_MASKING_SETTING')
                ->where('ID_MASKING_SETTING.DISTRIBUTOR_MASKING_ID', '=', $datamasking->DISTRIBUTOR_MASKING_ID)
                ->update(['ID_MASKING_SETTING.CURRENT_RUN_NO' => $d_run_no]);
            // End of Masking




            $user = User::where('USER_ID', $request->USER_ID)->first();
            $user->USER_DIST_ID = $data->DISTRIBUTOR_ID;
            $user->save();


            // set distributor status
            $dataStatus = new DistributorStatus;
            $dataStatus->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataStatus->DIST_PUBLISH_STATUS = $request->DIST_PUBLISH_STATUS;
            $dataStatus->save();

            // 1.create distributor profile
            $file = $request->FILEOBJECT;
            $fileId = $request->FILEOBJECTID;


            if ($file != null) {
                foreach ($file as $key => $item) {
                    $itemFile = $item;

                    $contents = $itemFile->openFile()->fread($itemFile->getSize());

                    $doc = new DistributorDocument;

                    $doc->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc->DOCU_GROUP = 1;
                    $doc->DOCU_BLOB = $contents;
                    $doc->REQ_DOCU_ID = $fileId[$key];
                    $doc->DOCU_ORIGINAL_NAME = $itemFile->getClientOriginalName();
                    $doc->DOCU_FILESIZE = $itemFile->getSize();
                    $doc->DOCU_FILETYPE = $itemFile->getClientOriginalExtension();
                    $doc->DOCU_MIMETYPE = $itemFile->getMimeType();
                    $doc->save();
                }
            }

            if ($request->FILEOBJECT2 != null) {
                $file2 = $request->FILEOBJECT2;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 8;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            $dataAddr = new DistributorAddress;
            $dataAddr->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAddr->DIST_ADDR_1 = $request->DIST_ADDR_1;
            $dataAddr->DIST_ADDR_2 = $request->DIST_ADDR_2;
            $dataAddr->DIST_ADDR_3 = $request->DIST_ADDR_3;
            $dataAddr->DIST_POSTAL = $request->DIST_POSTAL;
            $dataAddr->DIST_CITY = $request->DIST_CITY;
            $dataAddr->DIST_STATE = $request->DIST_STATE;
            $dataAddr->DIST_COUNTRY = $request->DIST_COUNTRY;
            $dataAddr->DIST_BIZ_ADDR_1 = $request->DIST_BIZ_ADDR_1;
            $dataAddr->DIST_BIZ_ADDR_2 = $request->DIST_BIZ_ADDR_2;
            $dataAddr->DIST_BIZ_ADDR_3 = $request->DIST_BIZ_ADDR_3;
            $dataAddr->DIST_BIZ_POSTAL = $request->DIST_BIZ_POSTAL;
            $dataAddr->DIST_BIZ_CITY = $request->DIST_BIZ_CITY;
            $dataAddr->DIST_BIZ_STATE = $request->DIST_BIZ_STATE;
            $dataAddr->DIST_BIZ_COUNTRY = $request->DIST_BIZ_COUNTRY;
            $dataAddr->save();

            $dataDistType = new DistributorType;
            $dataDistType->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataDistType->DIST_TYPE = $request->DIST_TYPE;
            $dataDistType->save();

            //2. Details Information
            $dataInfo = new DistributorDetailInfo;
            $dataInfo->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataInfo->DIST_PAID_UP_CAPITAL = $request->DIST_PAID_UP_CAPITAL;
            $dataInfo->DIST_TYPE_STRUCTURE = $request->DIST_TYPE_STRUCTURE;
            $dataInfo->DIST_MARKETING_APPROACH = $request->DIST_MARKETING_APPROACH;
            $dataInfo->DIST_NUM_DIST_POINT = $request->DIST_NUM_DIST_POINT;
            $dataInfo->DIST_NUM_CONSULTANT = $request->DIST_NUM_CONSULTANT;
            $dataInfo->DIST_INSURANCE = $request->DIST_INSURANCE;
            $dataInfo->DIST_EXPIRED_DATE = $request->DIST_EXPIRED_DATE;
            $dataInfo->save();

            // 3.CEO And Director
            $dataCEO = new DistributorRepresentative;
            $dataCEO->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataCEO->REPR_TYPE = 'CEO';
            $dataCEO->REPR_SALUTATION = $request->CEO_REPR_SALUTATION;
            $dataCEO->REPR_NAME = $request->CEO_REPR_NAME;
            $dataCEO->REPR_POSITION = $request->CEO_REPR_POSITION;
            $dataCEO->REPR_CITIZEN = $request->CEO_REPR_CITIZEN;
            $dataCEO->REPR_NRIC = $request->CEO_REPR_NRIC;
            $dataCEO->REPR_PASS_NUM = $request->CEO_REPR_PASS_NUM;
            $dataCEO->REPR_PASS_EXP = $request->CEO_REPR_PASS_EXP;
            if ($request->CEO_REPR_TELEPHONE == null) {
                $dataCEO->REPR_TELEPHONE = "-";
            } else {
                $dataCEO->REPR_TELEPHONE = $request->CEO_REPR_TELEPHONE;
            }
            if ($request->CEO_REPR_PHONE_EXTENSION == null) {
                $dataCEO->REPR_PHONE_EXTENSION = "-";
            } else {
                $dataCEO->REPR_PHONE_EXTENSION = $request->CEO_REPR_PHONE_EXTENSION;
            }
            if ($request->CEO_REPR_MOBILE_NUMBER == null) {
                $dataCEO->REPR_MOBILE_NUMBER = "-";
            } else {
                $dataCEO->REPR_MOBILE_NUMBER = $request->CEO_REPR_MOBILE_NUMBER;
            }
            if ($request->CEO_REPR_EMAIL == null) {
                $dataCEO->REPR_EMAIL = "-";
            } else {
                $dataCEO->REPR_EMAIL = $request->CEO_REPR_EMAIL;
            }
            $dataCEO->save();

            //Bankruptcy Check

            // try {
            //     $bankruptcy_result_CEO = new BankruptcySearch;
            //     $bankruptcy_result_CEO->DIST_ID = $data->DISTRIBUTOR_ID;
            //     $bankruptcy_result_CEO->TYPE = 'CEO';

            //     $bankruptcy_result_CEO->save();
            // } catch (RequestException $r) {
            //     http_response_code(400);
            //     return response([
            //     'message' => 'Failed to retrieve data.',
            //     'errorCode' => 4103
            // ], 400);
            // }

            foreach (json_decode($request->DIR_LIST) as $item) {
                $dataDIR = new DistributorDirector;
                $dataDIR->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataDIR->DIR_SALUTATION = $item->DIR_SALUTATION;
                $dataDIR->DIR_NAME = $item->DIR_NAME;
                $dataDIR->DIR_NRIC = $item->DIR_NRIC;
                $dataDIR->DIR_PASS_NUM = $item->DIR_PASSPORT;
                // $dataDIR->DIR_PASS_EXPIRY = $item->DIR_PASSPORT_EXPIRY_DATE;
                $dataDIR->DIR_DATE_EFFECTIVE = $item->DIR_DATE_EFFECTIVE;
                $dataDIR->DIR_DATE_END = $item->DIR_DATE_END;
                $dataDIR->save();
            }

            // 4.AR and AAR
            $dataAR = new DistributorRepresentative;
            $dataAR->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAR->REPR_TYPE = 'AR';
            $dataAR->REPR_SALUTATION = $request->AR_REPR_SALUTATION;
            $dataAR->REPR_NAME = $request->AR_REPR_NAME;
            $dataAR->REPR_POSITION = $request->AR_REPR_POSITION;
            $dataAR->REPR_CITIZEN = $request->AR_REPR_CITIZEN;
            $dataAR->REPR_NRIC = $request->AR_REPR_NRIC;
            $dataAR->REPR_PASS_NUM = $request->AR_REPR_PASS_NUM;
            $dataAR->REPR_PASS_EXP = $request->AR_REPR_PASS_EXP;
            if ($request->AR_REPR_TELEPHONE == null) {
                $dataAR->REPR_TELEPHONE = "-";
            } else {
                $dataAR->REPR_TELEPHONE = $request->AR_REPR_TELEPHONE;
            }
            if ($request->AR_REPR_PHONE_EXTENSION == null) {
                $dataAR->REPR_PHONE_EXTENSION = "-";
            } else {
                $dataAR->REPR_PHONE_EXTENSION = $request->AR_REPR_PHONE_EXTENSION;
            }
            if ($request->AR_REPR_MOBILE_NUMBER == null) {
                $dataAR->REPR_MOBILE_NUMBER = "-";
            } else {
                $dataAR->REPR_MOBILE_NUMBER = $request->AR_REPR_MOBILE_NUMBER;
            }
            if ($request->AR_REPR_EMAIL == null) {
                $dataAR->REPR_EMAIL = "-";
            } else {
                $dataAR->REPR_EMAIL = $request->AR_REPR_EMAIL;
            }
            // $dataAR->REPR_TELEPHONE = $request->AR_REPR_TELEPHONE;
            // $dataAR->REPR_PHONE_EXTENSION = $request->AR_REPR_PHONE_EXTENSION;
            // $dataAR->REPR_MOBILE_NUMBER = $request->AR_REPR_MOBILE_NUMBER;
            // $dataAR->REPR_EMAIL = $request->AR_REPR_EMAIL;
            $dataAR->save();

            //Bankruptcy Check

            // try {
            //     $bankruptcy_result_CEO = new BankruptcySearch;
            //     $bankruptcy_result_CEO->DIST_ID = $data->DISTRIBUTOR_ID;
            //     $bankruptcy_result_CEO->TYPE = 'AR';

            //     $bankruptcy_result_CEO->save();
            // } catch (RequestException $r) {
            //     http_response_code(400);
            //     return response([
            //     'message' => 'Failed to retrieve data.',
            //     'errorCode' => 4103
            // ], 400);
            // }


            $dataAAR = new DistributorRepresentative;
            $dataAAR->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAAR->REPR_TYPE = 'AAR';
            $dataAAR->REPR_SALUTATION = $request->AAR_REPR_SALUTATION;
            $dataAAR->REPR_NAME = $request->AAR_REPR_NAME;
            $dataAAR->REPR_POSITION = $request->AAR_REPR_POSITION;
            $dataAAR->REPR_CITIZEN = $request->AAR_REPR_CITIZEN;
            $dataAAR->REPR_NRIC = $request->AAR_REPR_NRIC;
            $dataAAR->REPR_PASS_NUM = $request->AAR_REPR_PASS_NUM;
            $dataAAR->REPR_PASS_EXP = $request->AAR_REPR_PASS_EXP;
            if ($request->AAR_REPR_TELEPHONE == null) {
                $dataAAR->REPR_TELEPHONE = "-";
            } else {
                $dataAAR->REPR_TELEPHONE = $request->AAR_REPR_TELEPHONE;
            }
            if ($request->AAR_REPR_PHONE_EXTENSION == null) {
                $dataAAR->REPR_PHONE_EXTENSION = "-";
            } else {
                $dataAAR->REPR_PHONE_EXTENSION = $request->AAR_REPR_PHONE_EXTENSION;
            }
            if ($request->AAR_REPR_MOBILE_NUMBER == null) {
                $dataAAR->REPR_MOBILE_NUMBER = "-";
            } else {
                $dataAAR->REPR_MOBILE_NUMBER = $request->AAR_REPR_MOBILE_NUMBER;
            }
            if ($request->AAR_REPR_EMAIL == null) {
                $dataAAR->REPR_EMAIL = "-";
            } else {
                $dataAAR->REPR_EMAIL = $request->AAR_REPR_EMAIL;
            }
            // $dataAAR->REPR_TELEPHONE = $request->AAR_REPR_TELEPHONE;
            // $dataAAR->REPR_PHONE_EXTENSION = $request->AAR_REPR_PHONE_EXTENSION;
            // $dataAAR->REPR_MOBILE_NUMBER = $request->AAR_REPR_MOBILE_NUMBER;
            // $dataAAR->REPR_EMAIL = $request->AAR_REPR_EMAIL;
            $dataAAR->save();

            //Bankruptcy Check

            // try {
            //     $bankruptcy_result_CEO = new BankruptcySearch;
            //     $bankruptcy_result_CEO->DIST_ID = $data->DISTRIBUTOR_ID;
            //     $bankruptcy_result_CEO->TYPE = 'AAR';

            //     $bankruptcy_result_CEO->save();
            // } catch (RequestException $r) {
            //     http_response_code(400);
            //     return response([
            //     'message' => 'Failed to retrieve data.',
            //     'errorCode' => 4103
            // ], 400);
            // }


            // 5. Additional Info
            //     HOD COMPL
            $dataAICompl = new DistributorAdditionalInfo;
            $dataAICompl->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAICompl->ADD_TYPE = $request->COMPL_TYPE;
            $dataAICompl->ADD_SALUTATION = $request->COMPL_SALUTATION;
            $dataAICompl->ADD_NAME = $request->COMPL_NAME;
            if ($request->COMPL_TELEPHONE == null) {
                $dataAICompl->ADD_TELEPHONE = "-";
            } else {
                $dataAICompl->ADD_TELEPHONE = $request->COMPL_TELEPHONE;
            }
            if ($request->COMPL_PHONE_EXTENSION == null) {
                $dataAICompl->ADD_PHONE_EXTENSION = "-";
            } else {
                $dataAICompl->ADD_PHONE_EXTENSION = $request->COMPL_PHONE_EXTENSION;
            }
            if ($request->COMPL_MOBILE_NUMBER == null) {
                $dataAICompl->ADD_MOBILE_NUMBER = "-";
            } else {
                $dataAICompl->ADD_MOBILE_NUMBER = $request->COMPL_MOBILE_NUMBER;
            }
            if ($request->COMPL_EMAIL == null) {
                $dataAICompl->ADD_EMAIL = "-";
            } else {
                $dataAICompl->ADD_EMAIL = $request->COMPL_EMAIL;
            }
            // $dataAICompl->ADD_TELEPHONE = $request->COMPL_TELEPHONE;
            // $dataAICompl->ADD_PHONE_EXTENSION = $request->COMPL_PHONE_EXTENSION;
            // $dataAICompl->ADD_EMAIL = $request->COMPL_EMAIL;
            // $dataAICompl->ADD_MOBILE_NUMBER = $request->COMPL_MOBILE_NUMBER;
            $dataAICompl->save();
            //HOD UTS
            $dataAIUts = new DistributorAdditionalInfo;
            $dataAIUts->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAIUts->ADD_TYPE = $request->UTS_TYPE;
            $dataAIUts->ADD_SALUTATION = $request->UTS_SALUTATION;
            $dataAIUts->ADD_NAME = $request->UTS_NAME;
            if ($request->UTS_TELEPHONE == null) {
                $dataAIUts->ADD_TELEPHONE = "-";
            } else {
                $dataAIUts->ADD_TELEPHONE = $request->UTS_TELEPHONE;
            }
            if ($request->UTS_PHONE_EXTENSION == null) {
                $dataAIUts->ADD_PHONE_EXTENSION = "-";
            } else {
                $dataAIUts->ADD_PHONE_EXTENSION = $request->UTS_PHONE_EXTENSION;
            }
            if ($request->UTS_MOBILE_NUMBER == null) {
                $dataAIUts->ADD_MOBILE_NUMBER = "-";
            } else {
                $dataAIUts->ADD_MOBILE_NUMBER = $request->UTS_MOBILE_NUMBER;
            }
            if ($request->UTS_EMAIL == null) {
                $dataAIUts->ADD_EMAIL = "-";
            } else {
                $dataAIUts->ADD_EMAIL = $request->UTS_EMAIL;
            }
            // $dataAIUts->ADD_TELEPHONE = $request->UTS_TELEPHONE;
            // $dataAIUts->ADD_PHONE_EXTENSION = $request->UTS_PHONE_EXTENSION;
            // $dataAIUts->ADD_EMAIL = $request->UTS_EMAIL;
            // $dataAIUts->ADD_MOBILE_NUMBER = $request->UTS_MOBILE_NUMBER;
            $dataAIUts->save();
            //HOD PRS
            $dataAIPrs = new DistributorAdditionalInfo;
            $dataAIPrs->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAIPrs->ADD_TYPE = $request->PRS_TYPE;
            $dataAIPrs->ADD_SALUTATION = $request->PRS_SALUTATION;
            $dataAIPrs->ADD_NAME = $request->PRS_NAME;
            if ($request->PRS_TELEPHONE == null) {
                $dataAIPrs->ADD_TELEPHONE = "-";
            } else {
                $dataAIPrs->ADD_TELEPHONE = $request->PRS_TELEPHONE;
            }
            if ($request->PRS_PHONE_EXTENSION == null) {
                $dataAIPrs->ADD_PHONE_EXTENSION = "-";
            } else {
                $dataAIPrs->ADD_PHONE_EXTENSION = $request->PRS_PHONE_EXTENSION;
            }
            if ($request->PRS_MOBILE_NUMBER == null) {
                $dataAIPrs->ADD_MOBILE_NUMBER = "-";
            } else {
                $dataAIPrs->ADD_MOBILE_NUMBER = $request->PRS_MOBILE_NUMBER;
            }
            if ($request->PRS_EMAIL == null) {
                $dataAIPrs->ADD_EMAIL = "-";
            } else {
                $dataAIPrs->ADD_EMAIL = $request->PRS_EMAIL;
            }
            // $dataAIPrs->ADD_TELEPHONE = $request->PRS_TELEPHONE;
            // $dataAIPrs->ADD_PHONE_EXTENSION = $request->PRS_PHONE_EXTENSION;
            // $dataAIPrs->ADD_EMAIL = $request->PRS_EMAIL;
            // $dataAIPrs->ADD_MOBILE_NUMBER = $request->PRS_MOBILE_NUMBER;
            $dataAIPrs->save();
            //HOD Train
            $dataAITrain = new DistributorAdditionalInfo;
            $dataAITrain->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataAITrain->ADD_TYPE = $request->TRAIN_TYPE;
            $dataAITrain->ADD_SALUTATION = $request->TRAIN_SALUTATION;
            $dataAITrain->ADD_NAME = $request->TRAIN_NAME;

            if ($request->TRAIN_TELEPHONE != null || $request->TRAIN_TELEPHONE != "") {
                $dataAITrain->ADD_TELEPHONE = $request->TRAIN_TELEPHONE;
            } else {
                $dataAITrain->ADD_TELEPHONE = "-";
            }
            if ($request->TRAIN_PHONE_EXTENSION == null) {
                $dataAITrain->ADD_PHONE_EXTENSION = "-";
            } else {
                $dataAITrain->ADD_PHONE_EXTENSION = $request->TRAIN_PHONE_EXTENSION;
            }
            if ($request->TRAIN_MOBILE_NUMBER == null) {
                $dataAITrain->ADD_MOBILE_NUMBER = "-";
            } else {
                $dataAITrain->ADD_MOBILE_NUMBER = $request->TRAIN_MOBILE_NUMBER;
            }
            if ($request->TRAIN_EMAIL == null) {
                $dataAITrain->ADD_EMAIL = "-";
            } else {
                $dataAITrain->ADD_EMAIL = $request->TRAIN_EMAIL;
            }
            // $dataAITrain->ADD_TELEPHONE = $request->TRAIN_TELEPHONE;
            // $dataAITrain->ADD_PHONE_EXTENSION = $request->TRAIN_PHONE_EXTENSION;
            // $dataAITrain->ADD_EMAIL = $request->TRAIN_EMAIL;
            // $dataAITrain->ADD_MOBILE_NUMBER = $request->TRAIN_MOBILE_NUMBER;
            $dataAITrain->save();

            // 6. Payment Detail
            $dataLedger = new DistributorLedger;
            $dataLedger->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataLedger->DIST_TRANS_REF = $request->DIST_TRANS_REF;
            $dataLedger->DIST_TRANS_DATE = $request->DIST_TRANS_DATE;
            $dataLedger->DIST_TRANS_TYPE = $request->DIST_TRANS_TYPE;
            $dataLedger->DIST_ISSUEBANK = $request->DIST_ISSUE_BANK;
            $dataLedger->DIST_ACC_AMOUNT = $request->DIST_ACC_AMOUNT;
            $dataLedger->save();

            // $dataledgerFinance = DB::table('finance_management.TRANSACTION_LEDGER')->Insert([
            //     'DISTRIBUTOR_ID' => $data->DISTRIBUTOR_ID,
            //     'DISTRIBUTOR_TYPE_ID'
            //     'CURRENCY'
            // ])

            // Financial Planner
            if ($request->DIST_TYPE == 4 || $request->DIST_TYPE == 5) {
                $dataFP = new DistributorFinancialPlanner;
                $dataFP->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataFP->DIST_TYPE_ID = $dataDistType->DIST_TYPE_ID;
                $dataFP->DIST_FINANCIAL_INSTITUTION = $request->DIST_FINANCIAL_INSTITUTION;
                $dataFP->DIST_FP_SALUTATION = $request->DIST_FP_SALUTATION;
                $dataFP->DIST_FP_NAME = $request->DIST_FP_NAME;
                $dataFP->DIST_FP_CSMRL = $request->DIST_FP_CSMRL;
                $dataFP->DIST_FP_CITIZEN = $request->DIST_FP_CITIZEN;
                $dataFP->DIST_FP_NRIC = $request->DIST_FP_NRIC;
                $dataFP->DIST_FP_PASS_NUM = $request->DIST_FP_PASS_NUM;
                $dataFP->DIST_FP_PASS_EXPIRY = $request->DIST_FP_PASS_EXPIRY;
                $dataFP->save();
            }

            // // File Upload
            if ($request->companyLogo != null) {
                $file2 = $request->companyLogo;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 3;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->DIST_PUBLISH_STATUS == 2) {
                foreach (json_decode($request->APPR_LIST) as $item) {

                    $dataApproval = DistributorApproval::where('DIST_ID', $request->DISTRIBUTOR_ID)
                        ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)
                        ->where('APPROVAL_LEVEL_ID', $item->APPROVAL_LEVEL_ID)
                        ->first();
                    if (!$dataApproval) {
                        $dataApproval = new DistributorApproval;
                    }
                    $dataApproval->DIST_ID = $data->DISTRIBUTOR_ID;
                    $dataApproval->APPR_GROUP_ID = 4;
                    $dataApproval->APPROVAL_LEVEL_ID = 7;
                    $dataApproval->APPROVAL_INDEX = 1;
                    $dataApproval->APPROVAL_STATUS = 2;
                    $dataApproval->save();
                }

                $dataStatus2 = DistributorStatus::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
                $dataStatus2->DIST_APPROVAL_STATUS = 2;
                $dataStatus2->save();
                $notification = new ManageNotification();

                $add = $notification->add(2, 1, "(DISTRIBUTOR) NEW DISTRIBUTOR REGISTRATION ENTRY", "fundCreation-Company");
                // $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
            } elseif ($request->DIST_PUBLISH_STATUS == 1) {
                $dataStatus2 = DistributorStatus::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
                $dataStatus2->DIST_APPROVAL_STATUS = 1;
                $dataStatus2->save();
            }


            // dd(DB::getQueryLog());
            http_response_code(200);
            return response([
                'message' => 'Data successfully created.',
                'data' => $data->DISTRIBUTOR_ID,
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be created.',
                'errorCode' => 4100
            ], 400);
        }
    }

    public function create_Doc(Request $request)
    {
        return $request->all();
        try {
            DB::enableQueryLog();

            // file delet

            // File Upload
            if ($request->hasFile('companyLogo')) {
                DistributorDocument::where('DOCU_GROUP', 3)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->companyLogo;
                $contents2 = $file2->openFile()->fread($file2->getSize());
                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 3;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }
            return $doc2;

            // file delet
            if ($request->hasFile('ssmForm8')) {
                DistributorDocument::where('DOCU_GROUP', 5)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm8;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 5;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->hasFile('ssmForm9')) {
                DistributorDocument::where('DOCU_GROUP', 4)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm9;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 4;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('ssmForm13')) {
                DistributorDocument::where('DOCU_GROUP', 9)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm13;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 9;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->hasFile('receipt')) {
                DistributorDocument::where('DOCU_GROUP', 7)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->receipt;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 7;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('BODApprove')) {
                DistributorDocument::where('DOCU_GROUP', 6)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->BODApprove;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 6;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }



            if ($request->hasFile('uploadCMSLOnly')) {
                DistributorDocument::where('DOCU_GROUP', 10)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->uploadCMSLOnly;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 10;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('uploadCMSLorMOFLicense')) {
                DistributorDocument::where('DOCU_GROUP', 11)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->uploadCMSLorMOFLicense;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 11;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }

            if ($request->hasFile('ssmForm49')) {
                DistributorDocument::where('DOCU_GROUP', 12)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm49;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 12;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->hasFile('complianceDeclaration')) {
                DistributorDocument::where('DOCU_GROUP', 13)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->complianceDeclaration;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 13;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }


            if ($request->hasFile('ssmForm24')) {
                DistributorDocument::where('DOCU_GROUP', 14)->where('DIST_ID', $request->DISTRIBUTOR_ID)->delete();
                $file2 = $request->ssmForm24;

                $contents2 = $file2->openFile()->fread($file2->getSize());

                $doc2 = new DistributorDocument;
                $doc2->DIST_ID = $request->DISTRIBUTOR_ID;
                $doc2->DOCU_GROUP = 14;
                $doc2->DOCU_BLOB = $contents2;
                $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                $doc2->DOCU_FILESIZE = $file2->getSize();
                $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                $doc2->save();
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be created.',
                'errorCode' => 4100
            ], 400);
        }
    }


    public function getByDisTemptId(Request $request)
    {
        try {
            DB::enableQueryLog();

            $data =  DistributorTemp::select(
                '*',
                'sg2.SET_PARAM AS SET_PARAM_STATE',
                'sg2.SETTING_GENERAL_ID AS STATE_ID',
                'sg2.SET_CODE AS SET_CODE_STATE',
                'sg1.SET_PARAM AS SET_PARAM_COUNTRY',
                'sg1.SETTING_GENERAL_ID AS COUNTRY_ID',
                'sg1.SET_CODE AS SET_CODE_COUNTRY',
                // 'bank.SET_PARAM as BANK_NAME'
            )
                ->join('DISTRIBUTOR_TEMP_ADDRESS', 'DISTRIBUTOR_TEMP_ADDRESS.DIST_TEMP_ID', '=', 'DISTRIBUTOR_TEMP.DIST_TEMP_ID')
                // ->join('DISTRIBUTOR_TEMP_TYPE', 'DISTRIBUTOR_TEMP_TYPE.DIST_TEMP_ID', '=', 'DISTRIBUTOR_TEMP.DIST_TEMP_ID')
                // ->join('DISTRIBUTOR_TEMP_LEDGER', 'DISTRIBUTOR_TEMP_LEDGER.DIST_TEMP_ID', '=', 'DISTRIBUTOR_TEMP.DIST_TEMP_ID')
                ->join('DISTRIBUTOR_TEMP_DETAIL_INFO', 'DISTRIBUTOR_TEMP_DETAIL_INFO.DIST_TEMP_ID', '=', 'DISTRIBUTOR_TEMP.DIST_TEMP_ID')
                ->leftJoin('admin_management.SETTING_GENERAL AS sg1', 'sg1.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_TEMP_ADDRESS.DIST_COUNTRY')
                ->leftJoin('admin_management.SETTING_CITY AS setting_city', 'setting_city.SETTING_CITY_ID', '=', 'DISTRIBUTOR_TEMP_ADDRESS.DIST_CITY')
                ->leftJoin('admin_management.SETTING_GENERAL AS sg2', 'sg2.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_TEMP_ADDRESS.DIST_STATE')
                ->leftJoin('admin_management.SETTING_POSTAL AS setting_postal', 'setting_postal.SETTING_POSTCODE_ID', '=', 'DISTRIBUTOR_TEMP_ADDRESS.DIST_POSTAL')
                // ->leftJoin('admin_management.DISTRIBUTOR_TYPE AS distributor_type', 'distributor_type.DISTRIBUTOR_TYPE_ID', '=', 'DISTRIBUTOR_TEMP_TYPE.DIST_TYPE')
                // ->leftJoin('admin_management.SETTING_GENERAL AS bank', 'bank.SETTING_GENERAL_ID', '=', 'DISTRIBUTOR_TEMP_LEDGER.DIST_ISSUEBANK')

                ->where('DISTRIBUTOR_TEMP.DIST_ID', $request->DIST_ID)
                ->where('DISTRIBUTOR_TEMP.DIST_TEMP_ID', $request->DIST_TEMP_ID)
                ->first();

            // dd(DB::getQueryLog());
            $dataRepr = DistributorTempRepresentative::where('DIST_TEMP_ID', $data->DIST_TEMP_ID)
                ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_TEMP_REPRESENTATIVE.REPR_SALUTATION')
                ->get();

            $dataAI = DistributorTempAdditionalInfo::where('DIST_TEMP_ID', $data->DIST_TEMP_ID)
                ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_TEMP_ADDITIONAL_INFO.ADD_SALUTATION')
                ->get();

            $dataDir = DistributorTempDirector::where('DIST_TEMP_ID', $data->DIST_TEMP_ID)
                ->leftJoin('USER_SALUTATION', 'USER_SALUTATION.USER_SAL_ID', '=', 'DISTRIBUTOR_TEMP_DIRECTOR.DIR_SALUTATION')
                ->orderBy('CREATE_TIMESTAMP', 'desc')
                ->get();

            foreach ($dataDir as $element) {
                $element->DIR_DATE_EFFECTIVE_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_EFFECTIVE));
                $element->DIR_DATE_END_DISPLAY = date('d-m-Y', strtotime($element->DIR_DATE_END));
                $element->DIR_NAME_DISPLAY = $element->USER_SAL_NAME . ' ' . $element->DIR_NAME;
            };

            // $dataFP = DistributorFinancialPlanner::where('DIST_TEMP_ID', $data->DIST_TEMP_ID)
            //     ->select('*')
            //     ->first();

            $dataDoc = DistributorTempDocument::where('DIST_TEMP_ID', $data->DIST_TEMP_ID)
                ->where('DOCU_GROUP', '!=', 1)
                ->where('DOCU_GROUP', '!=', 2)
                //->join('DISTRIBUTOR_APPROVAL_DOCUMENT', 'DISTRIBUTOR_APPROVAL_DOCUMENT.DIST_DOC_ID', '=', 'DIST_DOCU_ID')
                ->get();

            foreach ($dataDoc as $element) {
                $element->DOCU_BLOB = base64_encode($element->DOCU_BLOB);
            };

            $dist_id = $request->DIST_ID;
            $group_id = $request->APPR_GROUP_ID;

            $dataApprovalLog = DB::table('distributor_management.DISTRIBUTOR_UPDATE_APPROVAL AS DIST_APPROVAL')
                ->select('*')
                ->join('admin_management.TASK_STATUS AS task_status', 'task_status.TS_ID', '=', 'DIST_APPROVAL.APPROVAL_STATUS')
                //->leftjoin('admin_management.USER AS user', 'user.USER_ID', '=', 'DIST_APPROVAL.APPROVAL_USER')
                // ->join('admin_management.MANAGE_GROUP AS group', 'group.MANAGE_GROUP_ID', '=', 'DIST_APPROVAL.APPR_GROUP_ID')

                //->leftjoin('distributor_management.DISTRIBUTOR_TEMP_DOCUMENT_REMARK AS docRemark', 'docRemark.DIST_UPDATE_APPROVAL_ID', '=', 'DIST_APPROVAL.DISTRIBUTOR_UPDATE_APPROVAL_ID')
                // ->leftjoin('admin_management.MANAGE_DEPARTMENT AS department', 'department.MANAGE_DEPARTMENT_ID', '=', 'group.MANAGE_DEPARTMENT_ID')
                // ->where('DIST_ID', $request->DIST_ID)
                // ->where('APPR_GROUP_ID', '!=', $request->APPR_GROUP_ID)
                // // ->whereIn('DIST_APPROVAL_ID', function ($query) use ($dist_id, $group_id) {
                // //     return $query->select(DB::raw('max(DA2.DIST_APPROVAL_ID) as DIST_APPROVAL_ID'))
                // //         ->from('DISTRIBUTOR_APPROVAL AS DA2')
                // //         ->where('DA2.DIST_ID','=', $dist_id)
                // //         ->where('DA2.APPR_GROUP_ID','!=',$group_id)
                // //         ->groupBy('DA2.APPR_GROUP_ID');
                // // })
                // //->groupBy('APPR_GROUP_ID')

                ->orderBy('DIST_APPROVAL.APPROVAL_DATE', 'asc')
                ->where('DIST_APPROVAL.DIST_TEMP_ID', $request->DIST_TEMP_ID)
                ->get();

            foreach ($dataApprovalLog as $appLog) {
                //Getting Group Name
                if ($appLog->APPR_GROUP_ID == 1) {
                    $appLog->GROUP_NAME = 'MANAGER OF DISTRIBUTOR';
                } else if ($appLog->APPR_GROUP_ID == 3) {
                    $appLog->GROUP_NAME = 'ADMINISTRATOR OF DISTRIBUTOR';
                } else if ($appLog->APPR_GROUP_ID == 4) {
                    $appLog->GROUP_NAME = 'STAFF';
                } else {
                    $appLog->GROUP_NAME = '';
                }

                //TASK STATUS
                if ($appLog->APPROVAL_STATUS == 15) { // 15=Reviewed by Dist Manager but Pending for Approval by RD.
                    $appLog->TS_PARAM = 'REVIEWED';
                }

                //Converting Date
                $appLog->APPROVAL_DATE = date('d-M-Y', strtotime($appLog->APPROVAL_DATE));

                // Getting Approval Docs
                $dataApprovalDoc = DistributorTempDocumentRemark::where('DIST_UPDATE_APPROVAL_ID', $appLog->DISTRIBUTOR_UPDATE_APPROVAL_ID)
                    ->get();
                foreach ($dataApprovalDoc as $item) {
                    $item->DOCU_BLOB = base64_encode($item->DOCU_BLOB);
                };
                $appLog->APPROVAL_DOC = $dataApprovalDoc;
            };

            $data->APPROVAL_DATE = date('d-M-Y', strtotime($data->APPROVAL_DATE));
            $data->DATAREPR = $dataRepr;
            $data->DATAAI = $dataAI;
            $data->DATADIR = $dataDir;
            $data->DATADOC = $dataDoc;
            // $data->DATAFP = $dataFP;
            $data->APPRLOG = $dataApprovalLog;

            // dd(DB::getQueryLog());
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

    public function getByDistId(Request $request)
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
                'bank.SET_PARAM as BANK_NAME',
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
                $element->DIR_DATE_EFFECTIVE_DISPLAY = date('d-M-Y', strtotime($element->DIR_DATE_EFFECTIVE));
                $element->DIR_DATE_END_DISPLAY = date('d-M-Y', strtotime($element->DIR_DATE_END));
                $element->DIR_NAME_DISPLAY = $element->USER_SAL_NAME . ' ' . $element->DIR_NAME;
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
            $dataApprovalLog = DistributorApproval::join('admin_management.TASK_STATUS AS task_status', 'task_status.TS_ID', '=', 'APPROVAL_STATUS')
                ->leftjoin('admin_management.USER AS user', 'user.USER_ID', '=', 'APPROVAL_FIMM_USER')
                ->join('admin_management.MANAGE_GROUP AS group', 'group.MANAGE_GROUP_ID', '=', 'APPR_GROUP_ID')
                //->leftjoin('DISTRIBUTOR_DOCUMENT_REMARK AS docRemark', 'docRemark.DIST_APPR_ID', '=', 'DIST_APPROVAL_ID')
                ->leftjoin('admin_management.MANAGE_DEPARTMENT AS department', 'department.MANAGE_DEPARTMENT_ID', '=', 'group.MANAGE_DEPARTMENT_ID')
                ->where('DIST_ID', $request->DIST_ID)
                ->where('APPR_GROUP_ID', '!=', $request->APPR_GROUP_ID)
                // ->whereIn('DIST_APPROVAL_ID', function ($query) use ($dist_id, $group_id) {
                //     return $query->select(DB::raw('max(DA2.DIST_APPROVAL_ID) as DIST_APPROVAL_ID'))
                //         ->from('DISTRIBUTOR_APPROVAL AS DA2')
                //         ->where('DA2.DIST_ID','=', $dist_id)
                //         ->where('DA2.APPR_GROUP_ID','!=',$group_id)
                //         ->groupBy('DA2.APPR_GROUP_ID');
                // })
                //->groupBy('APPR_GROUP_ID')
                ->orderBy('DIST_APPROVAL_ID', 'asc')
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


            // PAYMENT STATUS;
            $payment_status = DB::table('finance_management.TRANSACTION_LEDGER as TL')
                ->select('TS.TS_PARAM')
                ->where('TL.DISTRIBUTOR_ID', $request->DIST_ID)
                ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', 'TL.TRANS_STATUS')
                ->first();

            $data->PAYMENT_STATUS = $payment_status->TS_PARAM ?? '-';


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

    public function getDocumentAdditional(Request $request)
    {
        try {
            $data = ManageRequiredDocument::where('REQ_DOCU_TYPE', $request->REQ_DOCU_TYPE)
                ->where('MANAGE_SUBMODULE_ID', $request->MANAGE_SUBMODULE_ID)
                ->leftJoin('distributor_management.DISTRIBUTOR_DOCUMENT as distDoc', 'distDoc.REQ_DOCU_ID', '=', 'MANAGE_REQUIRED_DOCUMENT_ID')
                ->get();

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => ([
                    'data' => $data,
                ]),
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function getDocumentProposal(Request $request)
    {
        try {
            DB::enableQueryLog();
            $dataRaw = DB::table('admin_management.MANAGE_REQUIRED_DOCUMENT AS A')
                ->select('*')
                ->where('A.REQ_DOCU_TYPE', $request->REQ_DOCU_TYPE)
                ->where('distDoc.DIST_ID', $request->DISTRIBUTOR_ID)
                ->where('A.MANAGE_SUBMODULE_ID', $request->MANAGE_SUBMODULE_ID)
                ->leftJoin('distributor_management.DISTRIBUTOR_DOCUMENT as distDoc', 'distDoc.REQ_DOCU_ID', '=', 'MANAGE_REQUIRED_DOCUMENT_ID')
                //->join('distributor_management.DISTRIBUTOR_APPROVAL_DOCUMENT as dist_appr_doc', 'dist_appr_doc.DIST_DOC_ID', '=', 'distDoc.DIST_DOCU_ID')

                ->get();

            foreach ($dataRaw as $docs) {
                $docs->DOCU_BLOB = base64_encode($docs->DOCU_BLOB);
            }

            $data = DB::table('admin_management.MANAGE_REQUIRED_DOCUMENT')
                ->select('REQ_DOCU_SECTION')
                ->where('REQ_DOCU_TYPE', $request->REQ_DOCU_TYPE)
                ->where('MANAGE_SUBMODULE_ID', $request->MANAGE_SUBMODULE_ID)
                ->groupBy('REQ_DOCU_SECTION')
                ->get();

            foreach ($data as $item) {
                $document = array();
                foreach ($dataRaw as $element) {
                    if ($item->REQ_DOCU_SECTION === $element->REQ_DOCU_SECTION) {
                        $document[] = $element;
                    }
                    $element->attributes['fileRecords'] = [];
                    $element->attributes['fileRecordsForUpload'] = [];
                    $element->DOCU_REMARK = null;
                }
                $item->list = $document;
            }
            // this has issues

            $dataAdditional = DB::table('admin_management.MANAGE_REQUIRED_DOCUMENT')
                ->select('*')
                ->where('REQ_DOCU_TYPE', 2)
                ->where('MANAGE_SUBMODULE_ID', $request->MANAGE_SUBMODULE_ID)
                ->leftJoin('distributor_management.DISTRIBUTOR_DOCUMENT as distDoc', 'distDoc.REQ_DOCU_ID', '=', 'MANAGE_REQUIRED_DOCUMENT_ID')
                // ->join('distributor_management.DISTRIBUTOR_APPROVAL_DOCUMENT as dist_appr_doc', 'dist_appr_doc.DIST_DOC_ID', '=', 'distDoc.DIST_DOCU_ID')
                ->get();

            foreach ($dataAdditional as $docs) {
                $docs->DOCU_BLOB = base64_encode($docs->DOCU_BLOB);
                //     $docs->setFileRecords([]);
                //     $docs->setFileRecordsForUpload([]);
                $docs->DOCU_REMARK = null;
            }

            // dd(DB::getQueryLog());
            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => ([
                    'dataProposal' => $data,
                    'dataAdditional' => $dataAdditional
                ]),
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ], 400);
        }
    }


    public function getDocumentProposal1(Request $request)
    {
        try {
            DB::enableQueryLog();
            $dataRaw = DistributorDocument::where('DOCU_GROUP', $request->REQ_DOCU_TYPE)
                ->where('reqDoc.MANAGE_SUBMODULE_ID', $request->MANAGE_SUBMODULE_ID)
                ->where('DIST_ID', $request->DISTRIBUTOR_ID)
                ->join('DISTRIBUTOR_APPROVAL_DOCUMENT', 'DISTRIBUTOR_APPROVAL_DOCUMENT.REQUIRED_DOC_ID', '=', 'REQ_DOCU_ID')
                ->join('admin_management.MANAGE_REQUIRED_DOCUMENT as reqDoc', 'reqDoc.MANAGE_REQUIRED_DOCUMENT_ID', '=', 'REQ_DOCU_ID', 'left outer')
                // ->where('distDoc.DIST_ID', $request->DISTRIBUTOR_ID)
                ->get();


            foreach ($dataRaw as $docs) {
                $docs->DOCU_BLOB = base64_encode($docs->DOCU_BLOB);
            }

            $data = DB::table('admin_management.MANAGE_REQUIRED_DOCUMENT AS req_doc') //ManageRequiredDocument::select('REQ_DOCU_SECTION')
                ->select('req_doc.REQ_DOCU_SECTION')
                ->where('req_doc.REQ_DOCU_TYPE', $request->REQ_DOCU_TYPE)
                ->where('req_doc.MANAGE_SUBMODULE_ID', $request->MANAGE_SUBMODULE_ID)
                ->groupBy('req_doc.REQ_DOCU_SECTION')
                ->get();


            foreach ($data as $item) {
                $document = array();
                foreach ($dataRaw as $element) {
                    if ($item->REQ_DOCU_SECTION === $element->REQ_DOCU_SECTION) {
                        $document[] = $element;
                    }

                    $element->fileRecords = [];
                    //  $element->setFileRecordsForUpload([]);
                }
                $item->list = $document;
            }

            $dataAdditional = DB::table('admin_management.MANAGE_REQUIRED_DOCUMENT AS req_doc')
                ->where('req_doc.REQ_DOCU_TYPE', 2)
                ->where('req_doc.MANAGE_SUBMODULE_ID', $request->MANAGE_SUBMODULE_ID)
                ->where('distDoc.DIST_ID', $request->DISTRIBUTOR_ID)
                ->leftjoin('distributor_management.DISTRIBUTOR_DOCUMENT as distDoc', 'distDoc.REQ_DOCU_ID', '=', 'req_doc.MANAGE_REQUIRED_DOCUMENT_ID')
                ->get();
            // dd(DB::getQueryLog());
            foreach ($dataAdditional as $docs) {
                $docs->DOCU_BLOB = base64_encode($docs->DOCU_BLOB);
                $docs->fileRecords = [];
            }


            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => ([
                    'dataProposal' => $data,
                    'dataAdditional' => $dataAdditional
                ]),
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ], 400);
        }
    }

    public function getDistributorFee()
    {
        try {
            $data = DB::table('admin_management.DISTRIBUTOR_FEE AS dist_fee')
                ->select('*')
                ->first();

            // foreach ($data as $fee){
            //     $fee->TOTAL_AMOUNT_APPLICATION = number_format($fee->TOTAL_AMOUNT_APPLICATION,2,".",",");
            // }

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
    public function updateDistStatus(Request $request)
    {
        try {
            $data = DistributorStatus::where('DIST_ID', $request->DIST_ID)->first();
            $data->DIST_VALID_STATUS = 1; //nama column
            $data->DIST_APPROVAL_STATUS = 2;
            $data->save();

            http_response_code(200);
            return response([
                'message' => 'Data has been updated successfully'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4101
            ], 400);
        }
    }

    public function getAll()
    {
        try {
            $data = Distributor::all();

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

    public function getDistributor(Request $request)
    {
        try {
            $data = DB::table('distributor_management.DISTRIBUTOR AS A')
                ->select('*', 'A.DISTRIBUTOR_ID', 'A.DIST_NAME', 'TS.TS_PARAM')
                ->join('DISTRIBUTOR_STATUS AS B', 'B.DIST_ID', '=', 'A.DISTRIBUTOR_ID')
                ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', '=', 'B.DIST_APPROVAL_STATUS')
                ->where('B.DIST_VALID_STATUS', 1)
                ->orderBy('A.CREATE_TIMESTAMP', 'DESC');

            // filters
            if ($params = $request->get('filters')) {
                $filters = json_decode($params, true);

                if (array_key_exists('DIST_NAME', $filters) && $filters['DIST_NAME']) {
                    $data->where('A.DIST_NAME', $filters['DIST_NAME']);
                }

                if (array_key_exists('REG_NUM', $filters) && $filters['REG_NUM']) {
                    $data->where('A.DIST_REGI_NUM1', $filters['REG_NUM']);
                }

                if (array_key_exists('NEW_REG_NUM', $filters) && $filters['NEW_REG_NUM']) {
                    $data->where('A.DIST_REGI_NUM2', $filters['NEW_REG_NUM']);
                }
                if (array_key_exists('STATUS', $filters) && $filters['STATUS']) {
                    $data->where('TS.TS_PARAM', $filters['STATUS']);
                }
            }


            $data = $data->orderBy('A.CREATE_TIMESTAMP')->get();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $data,
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }
    public function distributorSaveDraftReg(Request $request)
    {
        try {
            if ($request->Form == 1) {
                $data = new Distributor;
                $data->DIST_NAME = $request->DIST_NAME;
                $data->DIST_CODE = $request->DIST_CODE;
                $data->DIST_REGI_NUM1 = $request->DIST_REGI_NUM1;
                $data->DIST_REGI_NUM2 = $request->DIST_REGI_NUM2;
                $data->DIST_DATE_INCORP = $request->DIST_DATE_INCORP;
                $data->DIST_TYPE_SETUP = $request->DIST_TYPE_SETUP;
                if ($request->DIST_PHONE_NUMBER == null) {
                    $data->DIST_PHONE_NUMBER = "-";
                } else {
                    $data->DIST_PHONE_NUMBER = $request->DIST_PHONE_NUMBER;
                }
                if ($request->DIST_PHONE_EXTENSION == null) {
                    $data->DIST_PHONE_EXTENSION = "-";
                } else {
                    $data->DIST_PHONE_EXTENSION = $request->DIST_PHONE_EXTENSION;
                }
                if ($request->DIST_MOBILE_NUMBER == null) {
                    $data->DIST_PHONE_NUMBER = "-";
                } else {
                    $data->DIST_MOBILE_NUMBER = $request->DIST_MOBILE_NUMBER;
                }
                if ($request->DIST_EMAIL == null) {
                    $data->DIST_EMAIL = "-";
                } else {
                    $data->DIST_EMAIL = $request->DIST_EMAIL;
                }
                $data->DIST_FAX_NUMBER = $request->DIST_FAX_NUMBER;
                $data->DIST_COMPANY_WEBSITE = $request->DIST_COMPANY_WEBSITE;
                $data->save();

                $user = User::where('USER_ID', $request->USER_ID)->first();
                $user->USER_DIST_ID = $data->DISTRIBUTOR_ID;
                $user->save();

                if ($request->companyLogo != null) {
                    $file2 = $request->companyLogo;

                    $contents2 = $file2->openFile()->fread($file2->getSize());

                    $doc2 = new DistributorDocument;
                    $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc2->DOCU_GROUP = 3;
                    $doc2->DOCU_BLOB = $contents2;
                    $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                    $doc2->DOCU_FILESIZE = $file2->getSize();
                    $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                    $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                    $doc2->save();
                }
                if ($request->ssmForm8 != null) {
                    $file2 = $request->ssmForm8;

                    $contents2 = $file2->openFile()->fread($file2->getSize());

                    $doc2 = new DistributorDocument;
                    $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc2->DOCU_GROUP = 5;
                    $doc2->DOCU_BLOB = $contents2;
                    $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                    $doc2->DOCU_FILESIZE = $file2->getSize();
                    $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                    $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                    $doc2->save();
                }
                if ($request->ssmForm9 != null) {
                    $file2 = $request->ssmForm9;

                    $contents2 = $file2->openFile()->fread($file2->getSize());

                    $doc2 = new DistributorDocument;
                    $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc2->DOCU_GROUP = 4;
                    $doc2->DOCU_BLOB = $contents2;
                    $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                    $doc2->DOCU_FILESIZE = $file2->getSize();
                    $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                    $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                    $doc2->save();
                }
                if ($request->ssmForm13 != null) {
                    $file2 = $request->ssmForm13;

                    $contents2 = $file2->openFile()->fread($file2->getSize());

                    $doc2 = new DistributorDocument;
                    $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc2->DOCU_GROUP = 9;
                    $doc2->DOCU_BLOB = $contents2;
                    $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                    $doc2->DOCU_FILESIZE = $file2->getSize();
                    $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                    $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                    $doc2->save();
                }
                if ($request->uploadCMSLOnly != null) {
                    $file2 = $request->uploadCMSLOnly;

                    $contents2 = $file2->openFile()->fread($file2->getSize());

                    $doc2 = new DistributorDocument;
                    $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc2->DOCU_GROUP = 10;
                    $doc2->DOCU_BLOB = $contents2;
                    $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                    $doc2->DOCU_FILESIZE = $file2->getSize();
                    $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                    $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                    $doc2->save();
                }
                if ($request->uploadCMSLorMOFLicense != null) {
                    $file2 = $request->uploadCMSLorMOFLicense;

                    $contents2 = $file2->openFile()->fread($file2->getSize());

                    $doc2 = new DistributorDocument;
                    $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
                    $doc2->DOCU_GROUP = 11;
                    $doc2->DOCU_BLOB = $contents2;
                    $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
                    $doc2->DOCU_FILESIZE = $file2->getSize();
                    $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
                    $doc2->DOCU_MIMETYPE = $file2->getMimeType();
                    $doc2->save();
                }
                // set distributor status
                $dataStatus = new DistributorStatus;
                $dataStatus->DIST_ID = $data->DISTRIBUTOR_ID;
                $dataStatus->DIST_PUBLISH_STATUS = $request->DIST_PUBLISH_STATUS;
                $dataStatus->save();
                $distID = $data->DISTRIBUTOR_ID;

                http_response_code(200);
                return response([
                    'message' => 'Data successfully created.',
                    'data' => $distID
                ]);
            } elseif ($request->Form == 1) {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully created.'
                ]);
            } elseif ($request->Form == 2) {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully created.'
                ]);
            } elseif ($request->Form == 3) {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully created.'
                ]);
            } elseif ($request->Form == 4) {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully created.'
                ]);
            } elseif ($request->Form == 5) {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully created.'
                ]);
            } elseif ($request->Form == 6) {
                http_response_code(200);
                return response([
                    'message' => 'Data successfully created.'
                ]);
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to save data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function manage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'DIST_REGI_ID' => 'integer|nullable',
            'DIST_NAME' => 'string|nullable',
            'DIST_CODE' => 'string|nullable',
            'DIST_REGI_NUM1' => 'string|nullable',
            'DIST_REGI_NUM2' => 'string|nullable',
            'DIST_DATE_INCORP' => 'string|nullable',
            'DIST_TYPE_SETUP' => 'integer|nullable',
            'DIST_PHONE_NUMBER' => 'integer|nullable',
            'DIST_PHONE_EXTENSION' => 'integer|nullable',
            'DIST_MOBILE_NUMBER' => 'integer|nullable',
            'DIST_EMAIL' => 'string|nullable',
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

    //Old Function
    // public function distributor_update(Request $request)
    // {
    //     //dd($request->all());
    //     DB::beginTransaction();
    //     try {
    //         DB::enableQueryLog();

    //         $file = $request->FILEOBJECT;
    //         $fileId = $request->FILEOBJECTID;

    //          if($request->DIST_PUBLISH_STATUS==1){ //Save as Draft
    //             $dataUpdate = DistributorTemp::where('DIST_ID', $request->DISTRIBUTOR_ID)->first();

    //             if (!$dataUpdate) {
    //                 $dataUpdate = new DistributorTemp;
    //             }
    //         }else{
    //             $dataUpdate = new DistributorTemp;
    //         }

    //         //$dataUpdate = new DistributorTemp;
    //         $dataUpdate->DIST_ID = $request->DISTRIBUTOR_ID;
    //         $dataUpdate->DIST_NAME = $request->DIST_NAME;
    //         $dataUpdate->DIST_REGI_NUM1 = $request->DIST_REGI_NUM1;
    //         $dataUpdate->DIST_REGI_NUM2 = $request->DIST_REGI_NUM2;
    //         if ($request->DIST_PHONE_NUMBER == null) {
    //             $dataUpdate->DIST_PHONE_NUMBER = "-";
    //         } else {
    //             $dataUpdate->DIST_PHONE_NUMBER = $request->DIST_PHONE_NUMBER;
    //         }
    //         if ($request->DIST_PHONE_EXTENSION == null) {
    //             $dataUpdate->DIST_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataUpdate->DIST_PHONE_EXTENSION = $request->DIST_PHONE_EXTENSION;
    //         }
    //         if ($request->DIST_MOBILE_NUMBER == null) {
    //             $dataUpdate->DIST_PHONE_NUMBER = "-";
    //         } else {
    //             $dataUpdate->DIST_MOBILE_NUMBER = $request->DIST_MOBILE_NUMBER;
    //         }
    //         if ($request->DIST_EMAIL == null) {
    //             $dataUpdate->DIST_EMAIL = "-";
    //         } else {
    //             $dataUpdate->DIST_EMAIL = $request->DIST_EMAIL;
    //         }
    //         $dataUpdate->DIST_FAX_NUMBER = $request->DIST_FAX_NUMBER;
    //         $dataUpdate->DIST_COMPANY_WEBSITE = $request->DIST_COMPANY_WEBSITE;
    //         $dataUpdate->TS_ID = $request->TS_ID;
    //         $dataUpdate->PUBLISH_STATUS = $request->PUBLISH_STATUS;
    //         $dataUpdate->save();

    //         $getTempDistId = DistributorTemp::where('DIST_ID', $request->DISTRIBUTOR_ID)
    //         ->select('DIST_TEMP_ID')
    //         ->orderBy('CREATE_TIMESTAMP', 'desc')
    //         ->first();

    //         // foreach ($file as $key => $item) {

    //         //     $itemFile = $item;

    //         //     $contents = $itemFile->openFile()->fread($itemFile->getSize());

    //         //     $doc = new DistributorDocument;

    //         //     $doc->DIST_ID = $data->DISTRIBUTOR_ID;
    //         //     $doc->DOCU_GROUP = 1;
    //         //     $doc->DOCU_BLOB = $contents;
    //         //     $doc->REQ_DOCU_ID = $fileId[$key];
    //         //     $doc->DOCU_ORIGINAL_NAME = $itemFile->getClientOriginalName();
    //         //     $doc->DOCU_FILESIZE = $itemFile->getSize();
    //         //     $doc->DOCU_FILETYPE = $itemFile->getClientOriginalExtension();
    //         //     $doc->DOCU_MIMETYPE = $itemFile->getMimeType();
    //         //     $doc->save();
    //         // }

    //         // if ($request->FILEOBJECT2 != null) {
    //         //     $file2 = $request->FILEOBJECT2;

    //         //     $contents2 = $file2->openFile()->fread($file2->getSize());

    //         //     $doc2 = new DistributorDocument;
    //         //     $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
    //         //     $doc2->DOCU_GROUP = 8;
    //         //     $doc2->DOCU_BLOB = $contents2;
    //         //     $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
    //         //     $doc2->DOCU_FILESIZE = $file2->getSize();
    //         //     $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
    //         //     $doc2->DOCU_MIMETYPE = $file2->getMimeType();
    //         //     $doc2->save();
    //         // }

    //         if($request->DIST_PUBLISH_STATUS==1){ //Save as Draft
    //             $dataAddr = DistributorTempAddress::where('DIST_TEMP_ID', $getTempDistId->DIST_TEMP_ID)->first();
    //             if (!$dataAddr) {
    //                 $dataAddr = new DistributorTempAddress;
    //             }
    //         }else{
    //             $dataAddr = new DistributorTempAddress;
    //         }

    //         //$dataAddr = new DistributorTempAddress;
    //         $dataAddr->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataAddr->DIST_ADDR_1 = $request->DIST_ADDR_1;
    //         $dataAddr->DIST_ADDR_2 = $request->DIST_ADDR_2;
    //         $dataAddr->DIST_ADDR_3 = $request->DIST_ADDR_3;
    //         $dataAddr->DIST_POSTAL = $request->DIST_POSTAL;
    //         $dataAddr->DIST_CITY = $request->DIST_CITY;
    //         $dataAddr->DIST_STATE = $request->DIST_STATE;
    //         $dataAddr->DIST_COUNTRY = $request->DIST_COUNTRY;
    //         $dataAddr->DIST_BIZ_ADDR_1 = $request->DIST_BIZ_ADDR_1;
    //         $dataAddr->DIST_BIZ_ADDR_2 = $request->DIST_BIZ_ADDR_2;
    //         $dataAddr->DIST_BIZ_ADDR_3 = $request->DIST_BIZ_ADDR_3;
    //         $dataAddr->DIST_BIZ_POSTAL = $request->DIST_BIZ_POSTAL;
    //         $dataAddr->DIST_BIZ_CITY = $request->DIST_BIZ_CITY;
    //         $dataAddr->DIST_BIZ_STATE = $request->DIST_BIZ_STATE;
    //         $dataAddr->DIST_BIZ_COUNTRY = $request->DIST_BIZ_COUNTRY;
    //         $dataAddr->save();


    //         //2. Details Information
    //         if($request->DIST_PUBLISH_STATUS==1){ //Save as Draft
    //             $dataInfo = DistributorTempDetailInfo::where('DIST_TEMP_ID', $getTempDistId->DIST_TEMP_ID)->first();

    //             if (!$dataInfo) {
    //                 $dataInfo = new DistributorTempDetailInfo;
    //             }
    //         }else{
    //             $dataInfo = new DistributorTempDetailInfo;
    //         }
    //         //$dataInfo = new DistributorTempDetailInfo;
    //         $dataInfo->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataInfo->DIST_PAID_UP_CAPITAL = $request->DIST_PAID_UP_CAPITAL;
    //         $dataInfo->DIST_TYPE_STRUCTURE = $request->DIST_TYPE_STRUCTURE;
    //         $dataInfo->DIST_MARKETING_APPROACH = $request->DIST_MARKETING_APPROACH;
    //         $dataInfo->DIST_NUM_DIST_POINT = $request->DIST_NUM_DIST_POINT;
    //         $dataInfo->DIST_NUM_CONSULTANT = $request->DIST_NUM_CONSULTANT;
    //         $dataInfo->DIST_INSURANCE = $request->DIST_INSURANCE;
    //         $dataInfo->DIST_EXPIRED_DATE = $request->DIST_EXPIRED_DATE;
    //         $dataInfo->save();

    //         // 3.CEO And Director
    //         if($request->DIST_PUBLISH_STATUS==1){ //Save as Draft
    //             $dataCEO = DistributorTempRepresentative::where('DIST_TEMP_ID', $getTempDistId->DIST_TEMP_ID)->first();

    //             if (!$dataCEO) {
    //                 $dataCEO = new DistributorTempRepresentative;
    //             }
    //         }else{
    //             $dataCEO = new DistributorTempRepresentative;
    //         }
    //         //$dataCEO = new DistributorTempRepresentative;
    //         $dataCEO->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataCEO->REPR_TYPE = 'CEO';
    //         $dataCEO->REPR_SALUTATION = $request->CEO_REPR_SALUTATION;
    //         $dataCEO->REPR_NAME = $request->CEO_REPR_NAME;
    //         $dataCEO->REPR_POSITION = $request->CEO_REPR_POSITION;
    //         $dataCEO->REPR_CITIZEN = $request->CEO_REPR_CITIZEN;
    //         $dataCEO->REPR_NRIC = $request->CEO_REPR_NRIC;
    //         $dataCEO->REPR_PASS_NUM = $request->CEO_REPR_PASS_NUM;
    //         $dataCEO->REPR_PASS_EXP = $request->CEO_REPR_PASS_EXP;
    //         if ($request->CEO_REPR_TELEPHONE == null) {
    //             $dataCEO->REPR_TELEPHONE = "-";
    //         } else {
    //             $dataCEO->REPR_TELEPHONE = $request->CEO_REPR_TELEPHONE;
    //         }
    //         if ($request->REPR_PHONE_EXTENSION == null) {
    //             $dataCEO->REPR_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataCEO->REPR_PHONE_EXTENSION = $request->CEO_REPR_PHONE_EXTENSION;
    //         }
    //         if ($request->REPR_MOBILE_NUMBER == null) {
    //             $dataCEO->REPR_MOBILE_NUMBER = "-";
    //         } else {
    //             $dataCEO->REPR_MOBILE_NUMBER = $request->CEO_REPR_MOBILE_NUMBER;
    //         }
    //         if ($request->REPR_EMAIL == null) {
    //             $dataCEO->REPR_EMAIL = "-";
    //         } else {
    //             $dataCEO->REPR_EMAIL = $request->REPR_EMAIL;
    //         }
    //         $dataCEO->save();

    //         foreach (json_decode($request->DIR_LIST) as $item) {
    //             $dataDIR = new DistributorTempDirector;
    //             $dataDIR->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //             $dataDIR->DIR_SALUTATION = $item->DIR_SALUTATION;
    //             $dataDIR->DIR_NAME = $item->DIR_NAME;
    //             $dataDIR->DIR_NRIC = $item->DIR_NRIC;
    //             // $dataDIR->DIR_PASS_NUM = $item->DIR_PASSPORT;
    //             // $dataDIR->DIR_PASS_EXPIRY = $item->DIR_PASSPORT_EXPIRY_DATE;
    //             $dataDIR->DIR_DATE_EFFECTIVE = $item->DIR_DATE_EFFECTIVE;
    //             $dataDIR->DIR_DATE_END = $item->DIR_DATE_END;
    //             $dataDIR->save();
    //         }

    //         // 4.AR and AAR
    //         $dataAR = new DistributorTempRepresentative;
    //         $dataAR->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataAR->REPR_TYPE = 'AR';
    //         $dataAR->REPR_SALUTATION = $request->AR_REPR_SALUTATION;
    //         $dataAR->REPR_NAME = $request->AR_REPR_NAME;
    //         $dataAR->REPR_POSITION = $request->AR_REPR_POSITION;
    //         $dataAR->REPR_CITIZEN = $request->AR_REPR_CITIZEN;
    //         $dataAR->REPR_NRIC = $request->AR_REPR_NRIC;
    //         $dataAR->REPR_PASS_NUM = $request->AR_REPR_PASS_NUM;
    //         $dataAR->REPR_PASS_EXP = $request->AR_REPR_PASS_EXP;
    //         if ($request->AR_REPR_TELEPHONE == null) {
    //             $dataAR->REPR_TELEPHONE = "-";
    //         } else {
    //             $dataAR->REPR_TELEPHONE = $request->AR_REPR_TELEPHONE;
    //         }
    //         if ($request->AR_REPR_PHONE_EXTENSION == null) {
    //             $dataAR->REPR_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataAR->REPR_PHONE_EXTENSION = $request->AR_REPR_PHONE_EXTENSION;
    //         }
    //         if ($request->AR_REPR_MOBILE_NUMBER == null) {
    //             $dataAR->REPR_MOBILE_NUMBER = "-";
    //         } else {
    //             $dataAR->REPR_MOBILE_NUMBER = $request->AR_REPR_MOBILE_NUMBER;
    //         }
    //         if ($request->AR_REPR_EMAIL == null) {
    //             $dataAR->REPR_EMAIL = "-";
    //         } else {
    //             $dataAR->REPR_EMAIL = $request->AR_REPR_EMAIL;
    //         }
    //         // $dataAR->REPR_TELEPHONE = $request->AR_REPR_TELEPHONE;
    //         // $dataAR->REPR_PHONE_EXTENSION = $request->AR_REPR_PHONE_EXTENSION;
    //         // $dataAR->REPR_MOBILE_NUMBER = $request->AR_REPR_MOBILE_NUMBER;
    //         // $dataAR->REPR_EMAIL = $request->AR_REPR_EMAIL;
    //         $dataAR->save();

    //         $dataAAR = new DistributorTempRepresentative;
    //         $dataAAR->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataAAR->REPR_TYPE = 'AAR';
    //         $dataAAR->REPR_SALUTATION = $request->AAR_REPR_SALUTATION;
    //         $dataAAR->REPR_NAME = $request->AAR_REPR_NAME;
    //         $dataAAR->REPR_POSITION = $request->AAR_REPR_POSITION;
    //         $dataAAR->REPR_CITIZEN = $request->AAR_REPR_CITIZEN;
    //         $dataAAR->REPR_NRIC = $request->AAR_REPR_NRIC;
    //         $dataAAR->REPR_PASS_NUM = $request->AAR_REPR_PASS_NUM;
    //         $dataAAR->REPR_PASS_EXP = $request->AAR_REPR_PASS_EXP;
    //         if ($request->AAR_REPR_TELEPHONE == null) {
    //             $dataAR->REPR_TELEPHONE = "-";
    //         } else {
    //             $dataAR->REPR_TELEPHONE = $request->AAR_REPR_TELEPHONE;
    //         }
    //         if ($request->AAR_REPR_PHONE_EXTENSION == null) {
    //             $dataAR->REPR_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataAR->REPR_PHONE_EXTENSION = $request->AAR_REPR_PHONE_EXTENSION;
    //         }
    //         if ($request->AAR_REPR_MOBILE_NUMBER == null) {
    //             $dataAR->REPR_MOBILE_NUMBER = "-";
    //         } else {
    //             $dataAR->REPR_MOBILE_NUMBER = $request->AAR_REPR_MOBILE_NUMBER;
    //         }
    //         if ($request->AAR_REPR_EMAIL == null) {
    //             $dataAR->REPR_EMAIL = "-";
    //         } else {
    //             $dataAR->REPR_EMAIL = $request->AAR_REPR_EMAIL;
    //         }
    //         // $dataAAR->REPR_TELEPHONE = $request->AAR_REPR_TELEPHONE;
    //         // $dataAAR->REPR_PHONE_EXTENSION = $request->AAR_REPR_PHONE_EXTENSION;
    //         // $dataAAR->REPR_MOBILE_NUMBER = $request->AAR_REPR_MOBILE_NUMBER;
    //         // $dataAAR->REPR_EMAIL = $request->AAR_REPR_EMAIL;
    //         $dataAAR->save();

    //         // 5. Additional Info
    //         //     // Financial Planner
    //         // $dataAIFP = new DistributorAdditionalInfo;
    //         // $dataAIFP->DIST_ID = $data->DISTRIBUTOR_ID;
    //         // $dataAIFP->ADD_TYPE = $request->FP_TYPE;
    //         // $dataAIFP->ADD_SALUTATION = $request->FP_SALUTATION;
    //         // $dataAIFP->ADD_NAME = $request->FP_NAME;
    //         // $dataAIFP->ADD_TELEPHONE = $request->FP_TELEPHONE;
    //         // $dataAIFP->ADD_PHONE_EXTENSION = $request->FP_PHONE_EXTENSION;
    //         // $dataAIFP->ADD_EMAIL = $request->FP_EMAIL;
    //         // $dataAIFP->ADD_MOBILE_NUMBER = $request->FP_MOBILE_NUMBER;
    //         // $dataAIFP->save();
    //         //     HOD COMPL

    //         $dataAICompl = new DistributorTempAdditionalInfo;
    //         $dataAICompl->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataAICompl->ADD_TYPE = $request->COMPL_TYPE;
    //         $dataAICompl->ADD_SALUTATION = $request->COMPL_SALUTATION;
    //         $dataAICompl->ADD_NAME = $request->COMPL_NAME;
    //         if ($request->COMPL_TELEPHONE == null) {
    //             $dataAR->ADD_TELEPHONE = "-";
    //         } else {
    //             $dataAR->ADD_TELEPHONE = $request->COMPL_TELEPHONE;
    //         }
    //         if ($request->COMPL_PHONE_EXTENSION == null) {
    //             $dataAR->ADD_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataAR->ADD_PHONE_EXTENSION = $request->COMPL_PHONE_EXTENSION;
    //         }
    //         if ($request->COMPL_MOBILE_NUMBER == null) {
    //             $dataAR->ADD_MOBILE_NUMBER = "-";
    //         } else {
    //             $dataAR->ADD_MOBILE_NUMBER = $request->COMPL_MOBILE_NUMBER;
    //         }
    //         if ($request->COMPL_EMAIL == null) {
    //             $dataAR->ADD_EMAIL = "-";
    //         } else {
    //             $dataAR->ADD_EMAIL = $request->COMPL_EMAIL;
    //         }
    //         // $dataAICompl->ADD_TELEPHONE = $request->COMPL_TELEPHONE;
    //         // $dataAICompl->ADD_PHONE_EXTENSION = $request->COMPL_PHONE_EXTENSION;
    //         // $dataAICompl->ADD_EMAIL = $request->COMPL_EMAIL;
    //         // $dataAICompl->ADD_MOBILE_NUMBER = $request->COMPL_MOBILE_NUMBER;
    //         $dataAICompl->save();
    //         //HOD UTS
    //         $dataAIUts = new DistributorTempAdditionalInfo;
    //         $dataAIUts->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataAIUts->ADD_TYPE = $request->UTS_TYPE;
    //         $dataAIUts->ADD_SALUTATION = $request->UTS_SALUTATION;
    //         $dataAIUts->ADD_NAME = $request->UTS_NAME;
    //         if ($request->UTS_TELEPHONE == null) {
    //             $dataAR->ADD_TELEPHONE = "-";
    //         } else {
    //             $dataAR->ADD_TELEPHONE = $request->UTS_TELEPHONE;
    //         }
    //         if ($request->UTS_PHONE_EXTENSION == null) {
    //             $dataAR->ADD_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataAR->ADD_PHONE_EXTENSION = $request->UTS_PHONE_EXTENSION;
    //         }
    //         if ($request->UTS_MOBILE_NUMBER == null) {
    //             $dataAR->ADD_MOBILE_NUMBER = "-";
    //         } else {
    //             $dataAR->ADD_MOBILE_NUMBER = $request->UTS_MOBILE_NUMBER;
    //         }
    //         if ($request->UTS_EMAIL == null) {
    //             $dataAR->ADD_EMAIL = "-";
    //         } else {
    //             $dataAR->ADD_EMAIL = $request->UTS_EMAIL;
    //         }
    //         // $dataAIUts->ADD_TELEPHONE = $request->UTS_TELEPHONE;
    //         // $dataAIUts->ADD_PHONE_EXTENSION = $request->UTS_PHONE_EXTENSION;
    //         // $dataAIUts->ADD_EMAIL = $request->UTS_EMAIL;
    //         // $dataAIUts->ADD_MOBILE_NUMBER = $request->UTS_MOBILE_NUMBER;
    //         $dataAIUts->save();
    //         //HOD PRS
    //         $dataAIPrs = new DistributorTempAdditionalInfo;
    //         $dataAIPrs->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataAIPrs->ADD_TYPE = $request->PRS_TYPE;
    //         $dataAIPrs->ADD_SALUTATION = $request->PRS_SALUTATION;
    //         $dataAIPrs->ADD_NAME = $request->PRS_NAME;
    //         if ($request->PRS_TELEPHONE == null) {
    //             $dataAR->ADD_TELEPHONE = "-";
    //         } else {
    //             $dataAR->ADD_TELEPHONE = $request->PRS_TELEPHONE;
    //         }
    //         if ($request->PRS_PHONE_EXTENSION == null) {
    //             $dataAR->ADD_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataAR->ADD_PHONE_EXTENSION = $request->PRS_PHONE_EXTENSION;
    //         }
    //         if ($request->PRS_MOBILE_NUMBER == null) {
    //             $dataAR->ADD_MOBILE_NUMBER = "-";
    //         } else {
    //             $dataAR->ADD_MOBILE_NUMBER = $request->PRS_MOBILE_NUMBER;
    //         }
    //         if ($request->PRS_EMAIL == null) {
    //             $dataAR->ADD_EMAIL = "-";
    //         } else {
    //             $dataAR->ADD_EMAIL = $request->PRS_EMAIL;
    //         }
    //         // $dataAIPrs->ADD_TELEPHONE = $request->PRS_TELEPHONE;
    //         // $dataAIPrs->ADD_PHONE_EXTENSION = $request->PRS_PHONE_EXTENSION;
    //         // $dataAIPrs->ADD_EMAIL = $request->PRS_EMAIL;
    //         // $dataAIPrs->ADD_MOBILE_NUMBER = $request->PRS_MOBILE_NUMBER;
    //         $dataAIPrs->save();
    //         //HOD Train
    //         $dataAITrain = new DistributorTempAdditionalInfo;
    //         $dataAITrain->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //         $dataAITrain->ADD_TYPE = $request->TRAIN_TYPE;
    //         $dataAITrain->ADD_SALUTATION = $request->TRAIN_SALUTATION;
    //         $dataAITrain->ADD_NAME = $request->TRAIN_NAME;
    //         if ($request->TRAIN_TELEPHONE == null) {
    //             $dataAR->ADD_TELEPHONE = "-";
    //         } else {
    //             $dataAR->ADD_TELEPHONE = $request->TRAIN_TELEPHONE;
    //         }
    //         if ($request->TRAIN_PHONE_EXTENSION == null) {
    //             $dataAR->ADD_PHONE_EXTENSION = "-";
    //         } else {
    //             $dataAR->ADD_PHONE_EXTENSION = $request->TRAIN_PHONE_EXTENSION;
    //         }
    //         if ($request->TRAIN_MOBILE_NUMBER == null) {
    //             $dataAR->ADD_MOBILE_NUMBER = "-";
    //         } else {
    //             $dataAR->ADD_MOBILE_NUMBER = $request->TRAIN_MOBILE_NUMBER;
    //         }
    //         if ($request->TRAIN_EMAIL == null) {
    //             $dataAR->ADD_EMAIL = "-";
    //         } else {
    //             $dataAR->ADD_EMAIL = $request->TRAIN_EMAIL;
    //         }
    //         // $dataAITrain->ADD_TELEPHONE = $request->TRAIN_TELEPHONE;
    //         // $dataAITrain->ADD_PHONE_EXTENSION = $request->TRAIN_PHONE_EXTENSION;
    //         // $dataAITrain->ADD_EMAIL = $request->TRAIN_EMAIL;
    //         // $dataAITrain->ADD_MOBILE_NUMBER = $request->TRAIN_MOBILE_NUMBER;
    //         $dataAITrain->save();


    //         // // Financial Planner
    //         // if ($request->DIST_DIST_TYPE == 4 || $request->DIST_DIST_TYPE == 5) {
    //         //     $dataFP = new DistributorFinancialPlanner;
    //         //     $dataFP->DIST_ID = $data->DISTRIBUTOR_ID;
    //         //     // $dataFP->DIST_TYPE_ID = $dataDistType->DIST_TYPE_ID;
    //         //     $dataFP->DIST_FINANCIAL_INSTITUTION = $request->DIST_FINANCIAL_INSTITUTION;
    //         //     $dataFP->DIST_FP_SALUTATION = $request->DIST_FP_SALUTATION;
    //         //     $dataFP->DIST_FP_NAME = $request->DIST_FP_NAME;
    //         //     $dataFP->DIST_FP_CSMRL = $request->DIST_FP_CSMRL;
    //         //     $dataFP->DIST_FP_CITIZEN = $request->DIST_FP_CITIZEN;
    //         //     $dataFP->DIST_FP_NRIC = $request->DIST_FP_NRIC;
    //         //     $dataFP->DIST_FP_PASS_NUM = $request->DIST_FP_PASS_NUM;
    //         //     $dataFP->DIST_FP_PASS_EXPIRY = $request->DIST_FP_PASS_EXPIRY;
    //         //     $dataFP->save();
    //         // }

    //         // File Upload
    //         // if ($request->companyLogo != null) {
    //         //     $file2 = $request->companyLogo;

    //         //     $contents2 = $file2->openFile()->fread($file2->getSize());

    //         //     $doc2 = new DistributorDocument;
    //         //     $doc2->DIST_ID = $data->DISTRIBUTOR_ID;
    //         //     $doc2->DOCU_GROUP = 3;
    //         //     $doc2->DOCU_BLOB = $contents2;
    //         //     $doc2->DOCU_ORIGINAL_NAME = $file2->getClientOriginalName();
    //         //     $doc2->DOCU_FILESIZE = $file2->getSize();
    //         //     $doc2->DOCU_FILETYPE = $file2->getClientOriginalExtension();
    //         //     $doc2->DOCU_MIMETYPE = $file2->getMimeType();
    //         //     $doc2->save();
    //         // }

    //         // // set distributor status
    //         // $dataStatus = new DistributorStatus;
    //         // $dataStatus->DIST_ID = $data->DISTRIBUTOR_ID;
    //         // $dataStatus->DIST_PUBLISH_STATUS = $request->DIST_PUBLISH_STATUS;
    //         // $dataStatus->save();

    //         if ($request->DIST_PUBLISH_STATUS == 2) {
    //             $dataApproval = new DistributorUpdateApproval;
    //             $dataApproval->DIST_TEMP_ID = $getTempDistId->DIST_TEMP_ID;
    //             $dataApproval->DIST_ID = $request->DISTRIBUTOR_ID;
    //             $dataApproval->APPR_GROUP_ID = 1;
    //             $dataApproval->APPROVAL_LEVEL_ID = 2;
    //             $dataApproval->APPROVAL_INDEX = 1;
    //             $dataApproval->APPROVAL_STATUS = 2;
    //             $dataApproval->save();

    //             $notification = new ManageDistributorNotification();
    //             // $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $returnSubmission->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
    //             $add = $notification->add(1, 2, $request->DISTRIBUTOR_ID, "(DIST) NEW DISTRIBUTOR UPDATE", "distributor-UpdateDetails-SubmissionList-secondApproval");



    //         // $dataStatus2 = DistributorStatus::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
    //             // $dataStatus2->DIST_APPROVAL_STATUS = 2;
    //             // $dataStatus2->save();
    //         } elseif ($request->DIST_PUBLISH_STATUS == 1) {
    //             // $dataStatus2 = DistributorStatus::where('DIST_ID', $data->DISTRIBUTOR_ID)->first();
    //             // $dataStatus2->DIST_APPROVAL_STATUS = 1;
    //             // $dataStatus2->save();
    //         }


    //         // $distTempId = DistributorTemp::where('DIST_ID', $request->DISTRIBUTOR_ID)->first();

    //         // dd(DB::getQueryLog());

    //         DB::commit();

    //         http_response_code(200);
    //         return response([
    //             'message' => 'Data successfully saved.'
    //         ]);
    //     } catch (RequestException $r) {
    //         DB::rollback();
    //         http_response_code(400);
    //         return response([
    //             'message' => 'Data failed to be saved.',
    //             'errorCode' => 4101
    //         ], 400);
    //     }
    // }

    //New Updated Function
    public function distributor_update(Request $request)
    {

        DB::beginTransaction();
        try {
            DB::enableQueryLog();

            //1. Save Tab 1 Datas - Distributor Profile
            $fieldsProfile =  [
                'DIST_NAME',
                'DIST_REGI_NUM1',
                'DIST_REGI_NUM2',
                'DIST_TYPE_SETUP',
                'DIST_PHONE_NUMBER',
                'DIST_PHONE_EXTENSION',
                'DIST_MOBILE_NUMBER',
                'DIST_FAX_NUMBER',
                'DIST_EMAIL',
                'DIST_COMPANY_WEBSITE',
            ];

            $oldDistributorData = Distributor::where('DISTRIBUTOR_ID', $request->DISTRIBUTOR_ID)->first();
            $newData = $request->all();

            if ($request->DIST_PUBLISH_STATUS == 1 || $request->DIST_PUBLISH_STATUS == 2) { //1 = Save as Draft , 2 = Submit
                $distributorUpdateProfile = DistributorTemp::where(['DIST_TEMP_CATEGORY' => $request->DIST_TEMP_CATEGORY, 'PUBLISH_STATUS' => 0, 'DIST_ID' => $request->DISTRIBUTOR_ID])->first();

                if (!$distributorUpdateProfile) {
                    $distributorUpdateProfile = new DistributorTemp;
                }
                $distributorUpdateProfile->DIST_ID = $request->DISTRIBUTOR_ID;
                if ($request->TS_ID) {
                    $distributorUpdateProfile->TS_ID = $request->TS_ID;
                }
                $distributorUpdateProfile->PUBLISH_STATUS = $request->PUBLISH_STATUS;
                $distributorUpdateProfile->DIST_TEMP_CATEGORY = $request->DIST_TEMP_CATEGORY;
                $distributorUpdateProfile->CREATE_TIMESTAMP = Carbon::now();

                foreach ($fieldsProfile as $field) {
                    if ($oldDistributorData->$field != $newData[$field]) {
                        $distributorUpdateProfile->$field = $newData[$field];
                    }
                }
                $distributorUpdateProfile->save();

                // 1. Save Tab 1 Datas - Addresses
                $this->distributorProfileUpdate->updateDistributorAddress($distributorUpdateProfile, $newData);

                // 2. Save Tab 2 Datas - Details Information
                $this->distributorProfileUpdate->updateDistributorDetails($distributorUpdateProfile, $newData);

                // 3. Save Tab 3 Datas - CEO And Director
                $this->distributorProfileUpdate->updateDistributorCEOandDirector($distributorUpdateProfile, $newData);

                // 4. Save Tab 4 Datas - AR and AAR
                $this->distributorProfileUpdate->updateDistributorAR($distributorUpdateProfile, $newData);
                $this->distributorProfileUpdate->updateDistributorAAR($distributorUpdateProfile, $newData);

                // 5. Save Tab 5 Datas - HOD_COMPL, HOD_UTS, HOD_PRS and HOD_TRAIN
                $this->distributorProfileUpdate->updateDistributorHOD_COMPL($distributorUpdateProfile, $newData);
                $this->distributorProfileUpdate->updateDistributorHOD_UTS($distributorUpdateProfile, $newData);
                $this->distributorProfileUpdate->updateDistributorHOD_PRS($distributorUpdateProfile, $newData);
                $this->distributorProfileUpdate->updateDistributorHOD_TRAIN($distributorUpdateProfile, $newData);

                // Upload Documents and Infos//
                //1-proposal, 2-required, 3-image(Company Logo), 4-SSMForm9, 5-SSMForm8, 6-BODApprove,
                // 7-receipt, 9-SSMForm13, 12=ssmForm49, 13=complianceDeclaration, 14=ssmForm24, 8=additionalDoc(Additional Documents)
                /** 3 - image(Company Logo) */
                if ($request->companyLogo != null && $request->hasFile('companyLogo')) {
                    $docFile = $request->companyLogo;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 3, $distributorUpdateProfile);
                }
                /** 5 - SSMForm8 */
                if ($request->ssmForm8 != null && $request->hasFile('ssmForm8')) {
                    $docFile = $request->ssmForm8;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 5, $distributorUpdateProfile);
                }
                /** 4 - SSMForm9 */
                if ($request->ssmForm9 != null && $request->hasFile('ssmForm9')) {
                    $docFile = $request->ssmForm9;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 4, $distributorUpdateProfile);
                }
                /** 9 - SSMForm13 */
                if ($request->ssmForm13 != null && $request->hasFile('ssmForm13')) {
                    $docFile = $request->ssmForm13;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 9, $distributorUpdateProfile);
                }
                /** 12 - ssmForm49 */
                if ($request->ssmForm49 != null && $request->hasFile('ssmForm49')) {
                    $docFile = $request->ssmForm49;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 12, $distributorUpdateProfile);
                }
                /** 13 - complianceDeclaration */
                if ($request->complianceDeclaration != null && $request->hasFile('complianceDeclaration')) {
                    $docFile = $request->complianceDeclaration;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 13, $distributorUpdateProfile);
                }
                /** 14 - ssmForm24 */
                if ($request->ssmForm24 != null && $request->hasFile('ssmForm24')) {
                    $docFile = $request->ssmForm24;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 14, $distributorUpdateProfile);
                }
                /** 8 - additionalDoc */
                if ($request->additionalDoc != null && $request->hasFile('additionalDoc')) {
                    $docFile = $request->additionalDoc;
                    $this->distributorProfileUpdate->uploadDocumentFiles($docFile, $docGroup = 8, $distributorUpdateProfile);
                }

                if ($request->DIST_PUBLISH_STATUS == 2) {
                    $processFlow = ProcessFlow::where(['PROCESS_FLOW_NAME' => 'UPDATE DISTRIBUTOR DETAILS - A(1)(b)'])->first();

                    //Create Approval Log Data
                    $updateDistributorProfileapproval = DistributorUpdateApproval::updateOrCreate(
                        [
                            'DIST_TEMP_ID' => $distributorUpdateProfile->DIST_TEMP_ID,
                        ],
                        [
                            'DIST_ID' => $request->DISTRIBUTOR_ID,
                            'APPR_GROUP_ID' => 3, //Approval Group ID for ADMIN OF DISTRIBUTOR in am/DISTRIBUTOR_MANAGE_GROUP
                            'APPROVAL_LEVEL_ID' => $processFlow->PROCESS_FLOW_ID ?? null,
                            'APPROVAL_INDEX' => null,
                            'APPROVAL_STATUS' => $request->TS_ID,
                            'APPROVAL_USER' => $request->USER_ID,
                            'APPROVAL_REMARK_PROFILE' => null,
                            'APPROVAL_REMARK_DETAILINFO' => null,
                            'APPROVAL_REMARK_CEOnDIR' => null,
                            'APPROVAL_REMARK_ARnAAR' => null,
                            'APPROVAL_REMARK_PAYMENT' => null,
                        ]
                    );

                    //Send Notification to Distributor Manager
                    $data = [
                        'group_id' => $request->APPR_GROUP_ID_MANAGER,
                        'flow_id' => $processFlow->PROCESS_FLOW_ID ?? null,
                        'distributor_id' => $request->DISTRIBUTOR_ID,
                        'status_id' => 0,
                        'remark' => '(DIST) Update profile pending for approval',
                        'vue_url' => 'distributor-UpdateDetails-SubmissionList-secondApproval',
                    ];
                    $notification = new ManageDistributorNotification();
                    $notification->add($data['group_id'], $data['flow_id'], $data['distributor_id'], $data['remark'], $data['vue_url']);
                }
            }

            // dd(DB::getQueryLog());

            DB::commit();

            http_response_code(200);
            return response([
                'message' => 'Data successfully saved.'
            ]);
        } catch (RequestException $r) {
            DB::rollback();
            http_response_code(400);
            return response([
                'message' => 'Data failed to be saved.',
                'errorCode' => 4101
            ], 400);
        }
    }

    public function delete($id)
    {
        try {
            $data = Distributor::find($id);
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
            'DIST_REGI_ID' => 'integer|nullable',
            'DIST_NAME' => 'string|nullable',
            'DIST_CODE' => 'string|nullable',
            'DIST_REGI_NUM1' => 'string|nullable',
            'DIST_REGI_NUM2' => 'string|nullable',
            'DIST_DATE_INCORP' => 'string|nullable',
            'DIST_TYPE_SETUP' => 'integer|nullable',
            'DIST_PHONE_NUMBER' => 'integer|nullable',
            'DIST_PHONE_EXTENSION' => 'integer|nullable',
            'DIST_MOBILE_NUMBER' => 'integer|nullable',
            'DIST_EMAIL' => 'string|nullable',
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

    public function getDistributorMedia(Request $request)
    {
        try {
            $data = DB::table('distributor_management.DISTRIBUTOR AS A')
                ->select('A.DISTRIBUTOR_ID', 'A.DIST_NAME')
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

    public function sendEmailNotification(Request $request)
    {
        try {

            $url = env('URL_SERVER') . '/api/module0/send_dist_reg_email';

            DB::enableQueryLog();
            $DIST_EMAIL = $request->EMAIL;
            $DIST_NAME = $request->DIST_NAME;


            // $response = Curl::to('http://fimmserv_module0/api/module0/send_email')
            //$response = Curl::to('http://localhost:7000/api/module0/send_dist_reg_email')
            // $response = Curl::to('http://192.168.3.24/api/module0/send_dist_reg_email')
            $response =  Curl::to($url)
                ->withData(['email' => $DIST_EMAIL, 'distName' => $DIST_NAME])
                ->returnResponseObject()
                ->post();

            $content = json_decode($response->content);

            if ($response->status != 200) {
                http_response_code(400);

                return response([
                    'message' => 'Failed to send email.',
                    'errorCode' => 4100
                ], 400);
            } else {
                return response([

                    'message' => 'Email notification has been sent to Admin Distributor',
                ]);
            }
            // dd(DB::getQueryLog());
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Email failed to be send : ' + $r,
                'errorCode' => 4100
            ], 400);
        }
    }
    public function createDummyDistributor(Request $request)
    {
        Log::info("test" . $request);
        try {
            DB::enableQueryLog();
            $data = new Distributor;
            $data->DIST_NAME = $request->DIST_NAME;
            $data->DIST_CODE = 0;
            $data->save();

            $dataDistType = new DistributorType;
            $dataDistType->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataDistType->DIST_TYPE = $request->DIST_TYPE;
            $dataDistType->save();

            // dd(DB::getQueryLog());
            http_response_code(200);
            return response([
                'message' => 'Data successfully created.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be created.',
                'errorCode' => 4100
            ], 400);
        }
    }

    public function getAllDummyDistributor(Request $request)
    {
        try {
            DB::enableQueryLog();

            $data = DB::table('distributor_management.DISTRIBUTOR AS DISTRI')
                ->select('DISTRI.DISTRIBUTOR_ID as DISTRIBUTOR_ID', 'DISTRI.DIST_NAME as DIST_NAME', 'ADMIN_DIST_TYPE.DISTRIBUTOR_TYPE_ID as DISTRIBUTOR_TYPE_ID', 'ADMIN_DIST_TYPE.DIST_TYPE_NAME as DIST_TYPE_NAME', 'DIST_TYPE.DIST_TYPE_ID as DIST_TYPE_ID')
                ->where('DISTRI.DIST_CODE', '=', 0)
                ->leftJoin('distributor_management.DISTRIBUTOR_TYPE as DIST_TYPE', 'DISTRI.DISTRIBUTOR_ID', '=', 'DIST_TYPE.DIST_ID')
                ->leftJoin('admin_management.DISTRIBUTOR_TYPE as ADMIN_DIST_TYPE', 'DIST_TYPE.DIST_TYPE', '=', 'ADMIN_DIST_TYPE.DISTRIBUTOR_TYPE_ID')
                ->get();
            /*  $data =  Distributor::select('*')
                  ->where('DIST_CODE', 0)
                  ->join('distributor_management.DISTRIBUTOR_TYPE as DIST_TYPE', 'DISTRIBUTOR_ID', '=', 'DIST_TYPE.DIST_ID')
                  ->leftJoin('admin_management.DISTRIBUTOR_TYPE as ADMIN_DIST_TYPE', 'DIST_TYPE.DIST_TYPE_ID', '=', 'ADMIN_DIST_TYPE.DISTRIBUTOR_TYPE_ID')
                  ->get();*/
            //var_dump($data);
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

    public function deleteDummyDistributor(Request $request)
    {
        try {
            $data = Distributor::where('DISTRIBUTOR_ID', $request->DISTRIBUTOR_ID);
            $data->delete();

            $datatype = DistributorType::where('DIST_ID', $request->DISTRIBUTOR_ID);
            $datatype->delete();

            http_response_code(200);
            return response([
                'message' => 'Data successfully deleted1.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be deleted.',
                'errorCode' => 4102
            ], 400);
        }
    }
    public function updateDummyDistributor(Request $request)
    {
        try {
            DB::enableQueryLog();
            $data = Distributor::find($request->DISTRIBUTOR_ID);
            $data->DIST_NAME = $request->DIST_NAME;
            $data->DIST_CODE = 0;
            $data->save();

            $dataDistType = DistributorType::find($request->DIST_TYPE_ID);
            // $dataDistType->DIST_ID = $data->DISTRIBUTOR_ID;
            $dataDistType->DIST_TYPE = $request->DIST_TYPE;
            $dataDistType->save();

            // dd(DB::getQueryLog());
            http_response_code(200);
            return response([
                'message' => 'Data successfully created.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be created.',
                'errorCode' => 4100
            ], 400);
        }
    }
}
