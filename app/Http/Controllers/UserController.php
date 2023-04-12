<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserContact;
use App\Models\UserDivision;
use App\Models\UserPassport;
use App\Models\UserRegistration;
use App\Models\UserRegistrationDocument;
use App\Models\UserRegistrationApproval;
use App\Models\UserPasswordHistory;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Mail;
use Ixudra\Curl\Facades\Curl;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserSecurityQuestionController;
use LaravelKeycloakAdmin\Facades\KeycloakAdmin;
use App\Helpers\CurrentUser;

use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\KeycloakSettings;
use DB;
use File;
use Image;
use Compress\Compress;
use App\Helpers\Files;
use Carbon\Carbon;


class UserController extends Controller
{
    protected $AuthController;
    protected $UserSecurityQuestionController;
    public function __construct(AuthController $AuthController)
    {
        $this->AuthController = $AuthController;
    }

    public function getLastLogin(Request $request)
    {
        try {
            $user = KeycloakAdmin::user()->find([
                'query' => [
                    'id' => $request->user_id,
                ],
            ]);

            return $user;

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
    public function verifyUser(Request $request)
    {
        try {
            // User::where('USER_EMAIL', $request->LOGIN_ID)->first()
            $data = User::where('USER_USER_ID', $request->username)->first();

            if ($data == null) {
                http_response_code(400);
                return response([
                    'message' => 'User ID ' . $request->username . ' not found',
                    'errorCode' => 4003
                ], 400);
            }
            //Log::info($data);
            if ($data->USER_ISLOGIN == 0 || is_null($data->USER_ISLOGIN) || $data->USER_ISLOGIN == '') {
                if (Hash::check($request->USER_PASS_NUM, $data->USER_PASSWORD)) {
                    // The passwords match...
                    http_response_code(200);
                    return response([
                        'message' => 'First time user',
                        'data' => $data
                    ], 200);
                }
                http_response_code(400);
                return response([
                    'message' => 'First time user. OTP invalid.',
                    'errorCode' => 4003
                ], 400);
            } else {
                // $request->client_id =  'distributor-client';

                $request->client_id =  'fimm-app';

                $response = $this->AuthController->login($request);
                return $response;
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve data.',
                'errorCode' => 4103
            ], 400);
        }
    }
    public function checkDuplicateUserID(Request $request)
    {
        try {
            $data = User::where('USER_USER_ID', $request->USER_USER_ID)->get();
            if (count($data) != 0) {
                http_response_code(200);
                return response([
                    'message' => 'This user ID already exists in our record',
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
    public function checkDuplicateIC(Request $request)
    {
        try {
            $data = User::where('USER_NRIC', $request->USER_NRIC)->get();

            if (count($data) != 0) {
                http_response_code(200);
                return response([
                    'message' => 'This NRIC number already exists in our record',
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
    public function checkDuplicateEmail(Request $request)
    {
        try {
            $data = User::where('USER_EMAIL', $request->USER_EMAIL)->get();

            if (count($data) != 0) {
                http_response_code(200);
                return response([
                    'message' => 'This email already exists in our record',
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
    public function get(Request $request)
    {
        try {

            $data = DB::table('distributor_management.USER AS USER')
                ->select('USER.*', 'REGAPPR.*', 'USERADDR.*', 'SG.SET_PARAM AS COUNTRYNAME', 'SGSTATE.SET_PARAM AS STATENAME', 'SETTING_CITY.SET_CITY_NAME AS SET_CITY_NAME', 'SETTING_POSTAL.POSTCODE_NO', 'TS.TS_PARAM AS TS_PARAM', 'DISTRIBUTION_POINT.DIST_POINT_NAME AS DIST_POINT_NAME', 'DISTRIBUTOR.DIST_NAME AS DIST_NAME', 'GROUPUSER.DISTRIBUTOR_MANAGE_GROUP_NAME AS DISTRIBUTOR_MANAGE_GROUP_NAME')
                ->leftJoin('distributor_management.USER_REGISTRATION_APPROVAL AS REGAPPR', 'REGAPPR.USER_ID', '=', 'USER.USER_ID')
                ->leftJoin('distributor_management.USER_ADDRESS AS USERADDR', 'USER.USER_ID', '=', 'USERADDR.USER_ID')
                ->leftJoin('admin_management.DISTRIBUTOR_MANAGE_GROUP as GROUPUSER', 'GROUPUSER.DISTRIBUTOR_MANAGE_GROUP_ID', '=', 'USER.USER_GROUP')
                ->leftJoin('admin_management.TASK_STATUS as TS', 'TS.TS_ID', '=', 'REGAPPR.APPR_STATUS')
                ->leftJoin('distributor_management.DISTRIBUTOR AS DISTRIBUTOR', 'DISTRIBUTOR.DISTRIBUTOR_ID', '=', 'USER.USER_DIST_ID')
                ->leftJoin('distributor_management.DISTRIBUTION_POINT AS DISTRIBUTION_POINT', 'DISTRIBUTION_POINT.DISTRIBUTOR_ID', '=', 'USER.USER_DIST_ID')
                ->leftJoin('admin_management.SETTING_GENERAL as SG', 'SG.SETTING_GENERAL_ID', '=', 'USERADDR.USER_ADDR_COUNTRY')
                ->leftJoin('admin_management.SETTING_GENERAL as SGSTATE', 'SGSTATE.SETTING_GENERAL_ID', '=', 'USERADDR.USER_ADDR_STATE')
                ->leftJoin('admin_management.SETTING_CITY as SETTING_CITY', 'SETTING_CITY.SETTING_CITY_ID', '=', 'USERADDR.USER_ADDR_CITY')
                ->leftJoin('admin_management.SETTING_POSTAL as SETTING_POSTAL', 'SETTING_POSTAL.SETTING_POSTCODE_ID', '=', 'USERADDR.USER_ADDR_POSTAL')
                ->where('USER.USER_ID', $request->USER_ID)
                ->first();

            // $data = User::find($request->USER_ID);

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
            $data = User::all();

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
            $url = env('URL_SERVER') . '/api/module0/send_email';
            $email = $request->USER_EMAIL;
            $name = $request->USER_NAME;
            $userID = $request->USER_USER_ID;
            // $response = Curl::to('http://fimmserv_module0/api/module0/send_email')
            // $response = Curl::to('http://localhost:7000/api/module0/send_email')
            //$response = Curl::to('http://192.168.3.24/api/module0/send_email')
            $response =  Curl::to($url)
                ->withData(['email' => $email, 'name' => $name, 'userid' => $userID, 'loginUrl' => 'https://lfcs-dev.fimm.com.my/default'])
                ->returnResponseObject()
                ->post();

            $content = json_decode($response->content);

            if ($response->status != 200) {
                http_response_code(400);
                return response([
                    'message' => 'Failed to send email.',
                    'errorCode' => 4100
                ], 400);
            }
            $now = Carbon::now();
            $lastseentime = $now->format('Y-m-d H:i:s');
            $user = new User;
            $user->USER_NAME = $request->USER_NAME;
            $user->USER_USER_ID = $request->USER_USER_ID;
            $user->USER_CITIZEN = $request->USER_CITIZEN;
            $user->USER_NRIC = $request->USER_NRIC;
            $user->USER_PASS_NUM = $request->USER_PASS_NUM;
            $user->USER_PASS_EXP = $request->USER_PASS_EXP;
            $user->USER_EMAIL = $request->USER_EMAIL;
            $user->USER_PASSWORD = Hash::make($content->data); //hash($content->data);
            $user->USER_ISLOGIN = $request->USER_ISLOGIN;
            $user->USER_ISADMIN = $request->USER_ISADMIN;
            $user->LAST_SEEN_AT = $lastseentime;
            $user->save();

            http_response_code(200);
            return response([
                'message' => 'Data successfully created.'
            ]);

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

    public function manage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'KEYCLOAK_ID' => 'required|string',
            'USER_NAME' => 'required|string',
            'USER_CITIZEN' => 'required|integer',
            'USER_NRIC' => 'required|string',
            'USER_DOB' => 'required|string',
            'USER_DIVISION' => 'required|integer',
            'USER_DEPARTMENT' => 'required|integer',
            'USER_GROUP' => 'required|integer',
            'USER_USER_ID' => 'required|string',
            'USER_PASSWORD' => 'required|string',
            'USER_SECURITY_QUESTION' => 'required|string',
            'USER_SECURITY_ANSWER' => 'required|string',
            'USER_STATUS' => 'required|string',
            'USER_DIST_ID' => 'required|integer',
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

    public function update(Request $request)
    {
        try {
            $url = env('URL_SERVER') . '/api/module0/verify_TAC';
            // $response = Curl::to('http://fimmserv_module0/api/module0/verify_TAC')
            //$response = Curl::to('http://192.168.3.24/api/module0/verify_TAC')
            $response =  Curl::to($url)
                ->withData(['SMS_TAC_NUMBER' => $request->SMS_TAC_NUMBER, 'SMS_TAC_RECIPIENT' => $request->USER_MOBILE_NUM])
                ->returnResponseObject()
                ->get();

            $content = json_decode($response->content);

            if ($response->status == 200) {
                // if ($response->status == 200) {
                $data = User::where('USER_ID', $request->USER_ID)->first();
//                 $data = DB::table('distributor_management.USER')
//                 -> select('*')
//                 -> where('USER_ID','=', $request-> USER_ID)
//                 -> first();
                // KeycloakAdmin::user()->create([
                //         'body' => [
                //                 'username' => $data->USER_USER_ID,
                //                 'enabled' => true,
                //                 'emailVerified' => false,
                //                 'email' => $data->USER_EMAIL,
                //                 'credentials' => [[
                //                     'type' => 'password',
                //                     'value' => $request->USER_PASSWORD,
                //                     'temporary' => false
                //                 ]]
                //         ]
                //     ]);

                $request->EMAIL = $data->USER_EMAIL;
                $request->PASSWORD = $request->USER_PASSWORD;
                $request->USERNAME = $data->USER_USER_ID;
                $request->userRoles = $request->USER_ROLES;

                $user = new CurrentUser();
                $result = $user->createUser($request);
                // dd($result);

                $data->USER_MOBILE_NUM = $request->USER_MOBILE_NUM;
                $data->USER_SECURITY_QUESTION_ID = $request->USER_SECURITY_QUESTION_ID;
                $data->USER_SECURITY_ANSWER = $request->USER_SECURITY_ANSWER;
                // $data->KEYCLOAK_ID = $response[0]['id']; old

                $userEntity1 = DB::table(env('KEYCLOAK_DATABASE') . '.USER_ENTITY')
                    ->select('ID')
                    ->where('EMAIL', '=', $request->EMAIL)
                    ->first();
                $data->KEYCLOAK_ID = $userEntity1->ID;
                $data->USER_ISLOGIN = 1;
                $data->save();

              // Password History Limit Integration
                $passwordLog = new UserPasswordHistory;
                $passwordLog->KEYCLOAK_ID =$data->KEYCLOAK_ID;
                $passwordLog->USER_ID = $data->USER_ID;
                $passwordLog->USER_PASSWORD = Hash::make($request->USER_PASSWORD);
                $passwordLog->save();


                http_response_code(200);
                return response([
                    'message' => 'Data successfully updated'
                ]);
            } else {
                return json_encode($response);
            }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4101
            ], 400);
        }
    }
    public function update_password(Request $request)
    {
        try {
            $passSetting = DB::table('admin_management.PASSWORD_HISTORY AS PASSWORD_HISTORY')
            ->select('PASSWORD_HISTORY.PASSWORD AS PASSWORD')
            ->orderBy('PASSWORD_HISTORY.PASSWORD_HISTORY_ID', 'desc')
            ->first();
        
                $data = User::where('USER_USER_ID', $request->USER_ID)->first();
               // Log::info("data=".$passSetting->PASSWORD);
               // Password History

               $datahistory = UserPasswordHistory::where('USER_ID', $data->USER_ID)->get();
               $flag = 0;
               foreach ($datahistory as $datahist){
                if(Hash::check($request->USER_PASSWORD, $datahist->USER_PASSWORD)) {
                    // The passwords match...
                    Log::info("PasswordMatch");
                    http_response_code(200);
                    return response([
                        'message' => 'Password Exist',
                        'data' => 4707
                    ], 200);
                }
               }
              

                $request->EMAIL = $data->USER_EMAIL;
                $request->PASSWORD = $request->USER_PASSWORD;
                $request->KEYCLOAK_ID = $data->KEYCLOAK_ID;
                $request->USERNAME = $data->USER_USER_ID;
                $request->userRoles = $request->USER_ROLES;

                $user = new CurrentUser();
                $result = $user->changePasswordByTAC($request);
                $data->USER_PASSWORD = Hash::make($request->USER_PASSWORD);
                $data->save();

                if (count($datahistory) < $passSetting->PASSWORD) {
                   // Log::info("withlimit");
                    $passwordLog = new UserPasswordHistory;
                    $passwordLog->KEYCLOAK_ID =$data->KEYCLOAK_ID;
                    $passwordLog->USER_ID = $data->USER_ID;
                    $passwordLog->USER_PASSWORD = Hash::make($request->USER_PASSWORD);
                    $passwordLog->save();

                }else{
                  //  Log::info("withoutlimit");
                    $item = UserPasswordHistory::orderBy('HISTORY_ID', 'ASC')->where('USER_ID','=',$data->USER_ID)->first();
                    $item->delete();
                    $passwordLog = new UserPasswordHistory;
                    $passwordLog->KEYCLOAK_ID =$data->KEYCLOAK_ID;
                    $passwordLog->USER_ID = $data->USER_ID;
                    $passwordLog->USER_PASSWORD = Hash::make($request->USER_PASSWORD);
                    $passwordLog->save();
                }

                http_response_code(200);
                return response([
                    'message' => 'Data successfully updated'
                ]);
            // } else {
            //     return json_encode($response);
            // }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4101
            ], 400);
        }
    }
    // public function update_password(Request $request)
    // {
    //     try {
    //         Log::info("enter=");
    //         $url = env('URL_SERVER') . '/api/module0/verify_TAC';
    //         // $response = Curl::to('http://fimmserv_module0/api/module0/verify_TAC')
    //         //$response = Curl::to('http://192.168.3.24/api/module0/verify_TAC')
    //         $response =  Curl::to($url)
    //             ->withData(['SMS_TAC_NUMBER' => $request->SMS_TAC_NUMBER, 'SMS_TAC_RECIPIENT' => $request->USER_MOBILE_NUM])
    //             ->returnResponseObject()
    //             ->get();

    //         $content = json_decode($response->content);

    //         if ($response->status == 400) {
    //             // if ($response->status == 200) {
    //             $data = User::where('USER_USER_ID', $request->USER_ID)->first();
    //            // Log::info(print_r($data));

    //             $request->EMAIL = $data->USER_EMAIL;
    //             $request->PASSWORD = $request->USER_PASSWORD;
    //             $request->KEYCLOAK_ID = $data->KEYCLOAK_ID;
    //             $request->USERNAME = $data->USER_USER_ID;
    //             $request->userRoles = $request->USER_ROLES;

    //             $user = new CurrentUser();
    //             $result = $user->resetPasswordByTAC($request);

    //             http_response_code(200);
    //             return response([
    //                 'message' => 'Data successfully updated'
    //             ]);
    //         } else {
    //             return json_encode($response);
    //         }
    //     } catch (RequestException $r) {
    //         http_response_code(400);
    //         return response([
    //             'message' => 'Data failed to be updated.',
    //             'errorCode' => 4101
    //         ], 400);
    //     }
    // }
    public function get_distributor_last_pass(Request $request)
    {
        try {
            $data = User::where('USER_USER_ID', $request->username)->first();

            if ($data == null) {
                http_response_code(400);
                return response([
                    'message' => 'User ID ' . $request->username . ' not found',
                    'errorCode' => 4003
                ], 400);
            }
           
            $datahistory = UserPasswordHistory::where('USER_ID', $data->USER_ID)->get();

            //Log::info("data=".count($datahistory));
            $setting = KeycloakSettings::where('KEYCLOAK_CLIENT_ID', $request->client_id)
            ->first();
          //  $userEmail = User::where('USER_USER_ID', $request->username)->select('USER_EMAIL')->first();
            $response = Curl::to($setting->KEYCLOAK_TOKEN_URL)
            ->withData([
            'username' => $data->USER_EMAIL,
            'password' => $request->USER_PASS_NUM,
            'client_id' => $setting->KEYCLOAK_CLIENT_ID,
            'grant_type' => 'password',
            'client_secret' => $setting->KEYCLOAK_CLIENT_SECRET,
            ])
            ->post();
            $response = json_decode($response, true);

           if (count($datahistory) != 0){
                foreach ($datahistory as $datahist){
                    if(Hash::check($request->USER_PASS_NUM, $datahist->USER_PASSWORD)) {
                        // The passwords match...
                       // Log::info("PasswordMatch");
                       $request->EMAIL = $data->USER_EMAIL;
                       $request->PASSWORD = $request->USER_PASS_NUM;
                       $request->KEYCLOAK_ID = $data->KEYCLOAK_ID;
                       $request->USERNAME = $data->USER_USER_ID;
                       $request->userRoles = $request->USER_ROLES;

                       $user = new CurrentUser();
                       $result = $user->changePasswordByTAC($request);

                        http_response_code(200);
                        return response([
                            'message' => 'Password Exist',
                            'data' => 4707
                        ], 200);
                    }
                    else{
                        http_response_code(400);
                        return response([
                            'message' => 'No Matching Password',
                            'data' => 'NOTFOUND'
                        ]); 
                    }
                   }
             
            }
            else{
                if(isset($response['error']) == "invalid_grant") {
                    http_response_code(400);
                    return response([
                        'message' => 'No Matching Password',
                        'data' => 'NOTFOUND'
                    ]);
                }else{
                    http_response_code(200);
                    return response([
                        'message' => 'Password Exist',
                        'data' => 4707
                    ], 200);
                }
            }

            // $url = env('URL_SERVER') . '/api/module0/verify_TAC';
            // $response =  Curl::to($url)
            //     ->withData(['SMS_TAC_NUMBER' => $request->SMS_TAC_NUMBER, 'SMS_TAC_RECIPIENT' => $request->USER_MOBILE_NUM])
            //     ->returnResponseObject()
            //     ->get();

            // $content = json_decode($response->content);

            // if ($response->status == 400) {
            //     $data = User::where('USER_USER_ID', $request->USER_ID)->first();

            //     $request->EMAIL = $data->USER_EMAIL;
            //     $request->PASSWORD = $request->USER_PASSWORD;
            //     $request->KEYCLOAK_ID = $data->KEYCLOAK_ID;
            //     $request->USERNAME = $data->USER_USER_ID;
            //     $request->userRoles = $request->USER_ROLES;

            //     $user = new CurrentUser();
            //     $result = $user->resetPasswordByTAC($request);

            //     http_response_code(200);
            //     return response([
            //         'message' => 'Data successfully updated'
            //     ]);
            // } else {
            //     return json_encode($response);
            // }
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4101
            ], 400);
        }
    }
    public function get_distributor_user_info(Request $request)
    {
        try {
            //Log::info("parameter=".$request);
            $data = User::
            where('USER_USER_ID', $request->FORM_VALUE)
            ->orWhere('USER_MOBILE_NUM', $request->FORM_VALUE)
            ->orWhere('USER_EMAIL', $request->FORM_VALUE)
            ->get();
          //  Log::info(print_r($data));
            if (count($data) != 0) {
               // Log::info("parameter=".$request->FORM_VALUE);
                http_response_code(200);
                return response([
                    'message' => 'This user ID already exists in our record',
                    'data' => $data
                ]);
            }
            else{
                http_response_code(400);
                return response([
                    'message' => 'This user ID Not exists in our record',
                    'data' => 'NOTFOUND'
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

    public function UpdateUserProfile(Request $request)
    {
        try {
            // dd($request);


            $data = User::where('USER_ID', $request->USER_ID)->first();
            $data->USER_NAME = $request->USER_NAME;
            $data->USER_NRIC = $request->USER_NRIC;
            $data->USER_EMAIL = $request->USER_EMAIL;
            $data->USER_MOBILE_NUM = $request->USER_MOBILE_NUM;
            $data->USER_OFFICE_NUM = $request->USER_OFFICE_NUM;
            $data->USER_EXTENSION_NUM = $request->USER_EXTENSION_NUM;
            $data->DIST_POINT = $request->DIST_POINT;
            $data->USER_GROUP = $request->USER_GROUP;
            $data->USER_DIST_ID = $request->USER_DIST_ID;
            $data->save();

            $data2 = UserAddress::where('USER_ID', $request->USER_ID)->first();
            if (empty($data2)) {
                $data2 = new UserAddress;
                $data2->USER_ID = $request->USER_ID;
            }

            $data2->USER_ADDR_1 = $request->USER_ADDR_1;
            // if ($request->USER_ADDR_2 != '') {
            $data2->USER_ADDR_2 = $request->USER_ADDR_2;
            //    // } else {
            //         $data2->USER_ADDR_2 = " ";
            //     }
            // if ($request->USER_ADDR_3 != '') {
            $data2->USER_ADDR_3 = $request->USER_ADDR_3;
            // } else {
            //     $data2->USER_ADDR_3 = " ";
            // }
            $data2->USER_ADDR_COUNTRY = $request->USER_ADDR_COUNTRY;
            $data2->USER_ADDR_CITY = $request->USER_ADDR_CITY;
            $data2->USER_ADDR_STATE = $request->USER_ADDR_STATE;
            $data2->USER_ADDR_POSTAL = $request->USER_ADDR_POSTAL;
            $data2->save();

            $data7 = new UserRegistrationDocument;
            $data7->USER_ID = $request->USER_ID;
            if ($request->file('FILEOBJECT') != null) {
                $file = $request->file('FILEOBJECT');
                $blob = $file->openFile()->fread($file->getSize());
                //Log::info( "blob ===>" . $blob);
                $fileSize = $file->getSize();
                $data7->PHOTO_BLOB = $blob;
                $data7->PHOTO_MIMETYPE = $file->getMimeType();
                $data7->PHOTO_FILENAME = $file->getClientOriginalName();
                $data7->PHOTO_FILEEXTENSION = $file->getClientOriginalExtension();
                $data7->PHOTO_FILESIZE = $fileSize;
            }
            if ($request->file('FILEOBJECTDOC') != null) {
                $file1 = $request->file('FILEOBJECTDOC');
                $fileSize1 = $file1->getSize();
                $blob1 = $file1->openFile()->fread($file1->getSize());
                $data7->DOC_BLOB = $blob1;
                $data7->DOC_MIMETYPE = $file1->getMimeType();
                $data7->DOC_FILENAME = $file1->getClientOriginalName();
                $data7->DOC_FILEEXTENSION = $file1->getClientOriginalExtension();
                $data7->DOC_FILESIZE = $fileSize1;
            }
            $data7->save();


            $data5 = UserRegistrationApproval::where('USER_ID', $request->USER_ID)->first();

            $data5 = new UserRegistrationApproval;
            $data5->USER_ID = $request->USER_ID;
            $data5->APPR_STATUS = $request->APPR_STATUS;
            $data5->save();

            // $notification = new ManageNotification();
            // $add = $notification->add(
            //     $item->APPR_GROUP_ID ?? "",
            //     $item->APPR_PROCESSFLOW_ID ?? "",
            //     $request->NOTI_REMARK,
            //     $request->NOTI_LOCATION
            // );


            $notification = new ManageDistributorNotification();

            $add = $notification->add(2, 1, $request->USER_DIST_ID, "(DIST) NEW DISTRIBUTOR USER REGISTATION", "distributor-UpdateDetails-SubmissionList-secondApproval");





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
    public function UpdateUserProfilePost(Request $request)
    {
        try {
            $data = User::where('USER_ID', $request->USER_ID)->first();
            $data->USER_NAME = $request->USER_NAME;
            $data->USER_NRIC = $request->USER_NRIC;
            $data->USER_EMAIL = $request->USER_EMAIL;
            $data->USER_MOBILE_NUM = $request->USER_MOBILE_NUM;
            $data->USER_OFFICE_NUM = $request->USER_OFFICE_NUM;
            $data->USER_EXTENSION_NUM = $request->USER_EXTENSION_NUM;
            $data->DIST_POINT = $request->DIST_POINT;
            $data->USER_GROUP = $request->USER_GROUP;
            $data->USER_DIST_ID = $request->USER_DIST_ID;
            $data->save();

            $update = DB::table('distributor_management.USER_ADDRESS as USER_ADDRESS')->where('USER_ADDRESS.USER_ID', $request->USER_ID)->update(['USER_ADDRESS.USER_ADDR_1' => $request->USER_ADDR_1, 'USER_ADDRESS.USER_ADDR_2' => $request->USER_ADDR_2, 'USER_ADDRESS.USER_ADDR_3' => $request->USER_ADDR_3, 'USER_ADDRESS.USER_ADDR_COUNTRY' => $request->USER_ADDR_COUNTRY, 'USER_ADDRESS.USER_ADDR_CITY' => $request->USER_ADDR_CITY, 'USER_ADDRESS.USER_ADDR_STATE' => $request->USER_ADDR_STATE, 'USER_ADDRESS.USER_ADDR_POSTAL' => $request->USER_ADDR_POSTAL]);

            $data5 = UserRegistrationApproval::where('USER_ID', $request->USER_ID)->first();
            if (!$data5) {
                $data5 = new UserRegistrationApproval;
                $data5->USER_ID = $request->USER_ID;
                $data5->APPR_STATUS = 2;
                $data5->save();
            }
            $data2 = UserRegistrationDocument::where('USER_ID', $request->USER_ID)->first();

            if ($data2) {
                //Log::info( "Enter ===>");
                if ($request->file('FILEOBJECT') != null) {
                    $file = $request->file('FILEOBJECT');
                    $blob = $file->openFile()->fread($file->getSize());
                    // Log::info( "blob ===>" . $blob);
                    $fileSize = $file->getSize();
                    $update1 = DB::table('distributor_management.USER_REGISTRATION_DOCUMENT as USER_REGISTRATION_DOCUMENT')->where('USER_REGISTRATION_DOCUMENT.USER_ID', $request->USER_ID)->update(['USER_REGISTRATION_DOCUMENT.PHOTO_FILENAME' => $file->getClientOriginalName(), 'USER_REGISTRATION_DOCUMENT.PHOTO_FILEEXTENSION' => $file->getClientOriginalExtension(), 'USER_REGISTRATION_DOCUMENT.PHOTO_FILESIZE' => $fileSize, 'USER_REGISTRATION_DOCUMENT.PHOTO_BLOB' => $blob, 'USER_REGISTRATION_DOCUMENT.PHOTO_MIMETYPE' => $file->getMimeType()]);
                }

                if ($request->file('FILEOBJECTDOC') != null) {
                    $file1 = $request->file('FILEOBJECTDOC');
                    $blob1 = $file1->openFile()->fread($file1->getSize());
                    $fileSize1 = $file1->getSize();
                    $update2 = DB::table('distributor_management.USER_REGISTRATION_DOCUMENT as USER_REGISTRATION_DOCUMENT')->where('USER_REGISTRATION_DOCUMENT.USER_ID', $request->USER_ID)->update(['USER_REGISTRATION_DOCUMENT.DOC_FILENAME' => $file1->getClientOriginalName(), 'USER_REGISTRATION_DOCUMENT.DOC_FILEEXTENSION' => $file1->getClientOriginalExtension(), 'USER_REGISTRATION_DOCUMENT.DOC_FILESIZE' => $fileSize1, 'USER_REGISTRATION_DOCUMENT.DOC_BLOB' => $blob1, 'USER_REGISTRATION_DOCUMENT.DOC_MIMETYPE' => $file1->getMimeType()]);
                }
            } else {
                $data2 = new UserRegistrationDocument;
                $data2->USER_ID = $request->USER_ID;
                if ($request->file('FILEOBJECT') != null) {
                    $file = $request->file('FILEOBJECT');
                    $blob = $file->openFile()->fread($file->getSize());
                    // Log::info( "blob ===>" . $blob);
                    $fileSize = $file->getSize();
                    $data2->PHOTO_BLOB = $blob;
                    $data2->PHOTO_MIMETYPE = $file->getMimeType();
                    $data2->PHOTO_FILENAME = $file->getClientOriginalName();
                    $data2->PHOTO_FILEEXTENSION = $file->getClientOriginalExtension();
                    $data2->PHOTO_FILESIZE = $fileSize;
                }
                if ($request->file('FILEOBJECTDOC') != null) {
                    $file1 = $request->file('FILEOBJECTDOC');
                    $fileSize1 = $file1->getSize();
                    $blob1 = $file1->openFile()->fread($file1->getSize());
                    $data2->DOC_BLOB = $blob1;
                    $data2->DOC_MIMETYPE = $file1->getMimeType();
                    $data2->DOC_FILENAME = $file1->getClientOriginalName();
                    $data2->DOC_FILEEXTENSION = $file1->getClientOriginalExtension();
                    $data2->DOC_FILESIZE = $fileSize1;
                }
                $data2->save();
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



    public function delete($id)
    {
        try {
            $data = User::find($id);
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
            'KEYCLOAK_ID' => 'required|string',
            'USER_NAME' => 'required|string',
            'USER_CITIZEN' => 'required|integer',
            'USER_NRIC' => 'required|string',
            'USER_DOB' => 'required|string',
            'USER_DIVISION' => 'required|integer',
            'USER_DEPARTMENT' => 'required|integer',
            'USER_GROUP' => 'required|integer',
            'USER_USER_ID' => 'required|string',
            'USER_PASSWORD' => 'required|string',
            'USER_SECURITY_QUESTION' => 'required|string',
            'USER_SECURITY_ANSWER' => 'required|string',
            'USER_STATUS' => 'required|string',
            'USER_DIST_ID' => 'required|integer',
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
    public function getDistributorPoint(Request $request)
    {
        try {
            $data = DB::table('distributor_management.DISTRIBUTION_POINT AS A')
                ->select('A.DIST_POINT_ID AS DIST_POINT_ID', 'A.DIST_POINT_NAME AS DIST_POINT_NAME')
                ->where('A.DISTRIBUTOR_ID', '=', $request->DISTRIBUTOR_ID)
                ->get();

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
    public function get_email_tac(Request $request)
    {
        try {
             Log::info( "email ===>" . $request->useremail);
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $tac = substr(str_shuffle(str_repeat($pool, 5)), 0, 8);
            $url = env('URL_SERVER') . '/api/module0/send_email_tac';
            $email = $request->useremail;
           // $name = $request->USER_NAME;
            $userID = $request->userid;
            // $response = Curl::to('http://fimmserv_module0/api/module0/send_email')
            // $response = Curl::to('http://localhost:7000/api/module0/send_email')
            //$response = Curl::to('http://192.168.3.24/api/module0/send_email')
            $response =  Curl::to($url)
                ->withData(['email' => $email,'userid' => $userID, 'OTP' => $tac])
                ->returnResponseObject()
                ->post();

            $content = json_decode($response->content);
           // Log::info( "content ===>".$response);
          //  dd($response);

            if ($response->status != 200) {
                http_response_code(400);
                return response([
                    'message' => 'Failed to send email111.',
                    'errorCode' => 4100
                ], 400);
            }
            

            http_response_code(200);
            return response([
                'message' => 'Data successfully created.',
                'tac' => $tac
            ]);

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
}
