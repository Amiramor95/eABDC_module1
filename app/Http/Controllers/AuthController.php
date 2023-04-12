<?php

namespace App\Http\Controllers;

use App\Models\KeycloakSettings;
use App\Models\ManageGroup;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Ixudra\Curl\Facades\Curl;
use LaravelKeycloakAdmin\Facades\KeycloakAdmin;
use App\Helpers\CurrentUser;
use Validator;
use Auth;
use DB;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\ThrottlesLogins;
use App\Models\UserIpBlock;

class AuthController extends Controller
{
    use ThrottlesLogins;
    protected $maxAttempts;
    protected $decayMinutes = 1; // Time for which user is going to be blocked in seconds
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string|max:255', //reg-client
            'USER_EMAIL' => 'required|string', //dummy
            'USER_PASS_NUM' => 'required|string' //@Bcd1234
        ]);

        DB::enableQueryLog();
        // Default  setting for block

        // $loginAttemp = DB::table('admin_management.LOGIN_SETTING AS LOGIN_SETTING')
        // ->select('LOGIN_SETTING.LOGIN_SETTING_NO AS LOGIN_SETTING_NO')
        // ->orderBy('LOGIN_SETTING.LOGIN_SETTING_ID', 'desc')
        // ->first();
        // $blockduration = DB::table('admin_management.SYSTEM_BLOCK_DURATION AS SYSTEM_BLOCK_DURATION')
        // ->select('SYSTEM_BLOCK_DURATION.SYSTEM_BLOCK_DURATION_DAYS AS SYSTEM_BLOCK_DURATION_DAYS')
        // ->orderBy('SYSTEM_BLOCK_DURATION.SYSTEM_BLOCK_DURATION_ID', 'desc')
        // ->first();

        // $loginAttempno=$loginAttemp->LOGIN_SETTING_NO;
        // $blockdurationhour=$blockduration->SYSTEM_BLOCK_DURATION_DAYS;
        // $minutes = ($blockdurationhour * 60);
        // $this->maxAttempts = $loginAttempno;
        // $reqthrotle=$this->throttleKey($request);
        // $paramstr= explode("|", $reqthrotle);
        // $blockUserName=$paramstr[0];
        // $blockIp=$paramstr[1];
        // $now = Carbon::now();
        // $request->blockUserName= $blockUserName;
        // $request->blockIp= $blockIp;
        // $request->minutes= $minutes;

        // $getBlockIP = UserIpBlock::where('USER_NAME', $blockUserName)->where('USER_IP', $blockIp)->where('BLOCK_STATUS', 1)->orderBy('BLOCK_ID', 'desc')->first();
        // if ($getBlockIP) {
        //     Log::info("Block");
        //     // $BLOCK_TIME = Carbon::parse($getBlockIP->BLOCK_TIME);

        //     // $remainingminute = $BLOCK_TIME->diffInMinutes($now, true);
        //     // $blockremainingminute = $minutes-$remainingminute;
        //     // Log::info("remainingminute11=".$remainingminute);
        //     if ($remainingminute >= $getBlockIP->BLOCK_DURATION) {
        //         $update=DB::table('distributor_management.USER_IP_BLOCK as AMUSERIPBLOCK')->where('AMUSERIPBLOCK.BLOCK_ID', $getBlockIP->BLOCK_ID)->update(['AMUSERIPBLOCK.BLOCK_STATUS' => 0,'AMUSERIPBLOCK.UNBLOCK_TIME' => $now]);
        //         $response= $this->loginSucess($request);
        //         return $response;
        //     } else {
        //         http_response_code(400);
        //         return response([
        //                 'message' => 'Your Account will be unlock after '.$blockremainingminute.' minutes',
        //                 'errorCode' => 4003
        //             ], 400);
        //     }
        // } else {
        Log::info("OK");
        $response = $this->loginSucess($request);
        return $response;
        // }
    }
    public function loginSucess(Request $request)
    {
        // $setting = KeycloakSettings::where('KEYCLOAK_CLIENT_ID', $request->client_id)
        //         ->first();
        // $userEmail = User::where('USER_USER_ID', $request->username)->select('USER_EMAIL')->first();
        // $response = Curl::to($setting->KEYCLOAK_TOKEN_URL)
        //         ->withData([
        //         'username' => $userEmail->USER_EMAIL,
        //         'password' => $request->USER_PASS_NUM,
        //         'client_id' => $setting->KEYCLOAK_CLIENT_ID,
        //         'grant_type' => 'password',
        //         'client_secret' => $setting->KEYCLOAK_CLIENT_SECRET,
        //         ])
        //         ->post();


        // $checkLoginId = '';
        // if ($this->hasTooManyLoginAttempts($request)) {
        //     $this->fireLockoutEvent($request);
        //     $slr =  $this->sendLockoutResponse($request);
        //     // USER IP BLOCK
        //     if ($slr == 429) {
        //         $blocktime = Carbon::now();
        //         $blockdata = new UserIpBlock;
        //         $blockdata->USER_NAME = $request->blockUserName;
        //         $blockdata->USER_IP = $request->blockIp;
        //         $blockdata->BLOCK_STATUS = 1;
        //         $blockdata->BLOCK_TIME = $blocktime;
        //         $blockdata->BLOCK_DURATION = $request->minutes;
        //         $blockdata->save();
        //     }
        //     http_response_code(400);
        //     return response([
        //         'message' => 'Too Many Wrong Attempts, Your Account block for '. $request->minutes.' minutes',
        //         'errorCode' => 4003
        //         ], 400);
        //     // return $slr;
        // }

        // $response = json_decode($response, true);
        // if (isset($response['error']) == "invalid_grant") {
        //     $this->incrementLoginAttempts($request);

        //     http_response_code(400);
        //     return response([
        //         'message' => 'Invalid login credentials.',
        //         'errorCode' => 4003
        //     ], 400);
        // } else {
        // $this->clearLoginAttempts($request);
        // DB::enableQueryLog(); // Enable query log
        // $CurrentUser = new CurrentUser();
        // $user = $CurrentUser->getUserDetails($response['access_token']);
        //get user type
        $userdetail = DB::table('distributor_management.USER AS user')
            ->select('*')
            // User::select('MANAGE_GROUP.GROUP_NAME', 'USER.USER_GROUP',
            // 'USER.USER_ID','MANAGE_DEPARTMENT.MANAGE_DEPARTMENT_ID',
            // 'MANAGE_DEPARTMENT.DPMT_NAME','MANAGE_DIVISION.MANAGE_DIVISION_ID','MANAGE_DIVISION.DIV_NAME')
            ->leftJoin('admin_management.MANAGE_DISTRIBUTOR_GROUP AS group', 'group.MANAGE_DISTRIBUTOR_GROUP_ID', '=', 'user.USER_GROUP') // different with DISTRIBUTOR_MANAGE_GROUP
            ->where('user.USER_USER_ID', $request->username)->first();

        $USER_GROUP_NAME = $userdetail->GROUP_NAME ?? 'undefined';
        // $USER_GROUP_ID = $userdetail->USER_GROUP;
        $USER_ID = $userdetail->USER_ID ?? 0;
        $USER_ISLOGIN = $userdetail->USER_ISLOGIN ?? 1; //$userdetail->USER_ISLOGIN ?? 1;
        $USER_DIST_ID = $userdetail->USER_DIST_ID ?? 0;
        $USER_ISADMIN = $userdetail->USER_ISADMIN ?? 0;
        $USERNAME = $userdetail->USER_NAME ?? 'USER';

        $data = array();
        $data['USER_GROUP_NAME'] = $USER_GROUP_NAME;
        $data['USER_GROUP_ID'] = '3';
        $data['USER_ISLOGIN'] = $USER_ISLOGIN;
        $data['USER_DIST_ID'] = $USER_DIST_ID;
        $data['USER_ISADMIN'] = $USER_ISADMIN;
        $data['user_type'] = 'DISTRIBUTOR';

        $data['user_id'] = $USER_ID;
        // $data['keycloak_id'] = $user['sub'];
        $data['name'] = $USERNAME;

        $data['email'] = $request->USER_EMAIL;
        // $data['access_token'] = $response['access_token'];
        // $data['refresh_token'] = $response['refresh_token'];
        $data['PANEL_TRACK'] = 'DISTRIBUTOR';

        //Set session
        session(['USER_ID' => $USER_ID]);
        session(['name' => $USERNAME]);
        session(['user_type' => 'DISTRIBUTOR']);

        // Set Last Login Time
        $now = Carbon::now();
        $lastlogtime = $now->format('Y-m-d H:i:s');
        User::where('USER_ID', $USER_ID)
            ->update(['LOGINTIME' =>  $lastlogtime, 'LAST_SEEN_AT' => $lastlogtime, 'ISLOGIN' => 1]);
        // Distributor User Log
        // $dist_log = DB::table('admin_management.DISTRIBUTOR_USER_LOG')->insert([
        //     'USER_ID' =>  $USER_ID,
        //     'LOGIN_TIMESTAMP' =>  $lastlogtime,
        //     'LOG_IP' => $request->blockIp,
        //     'STATUS' => 1
        // ]);
        http_response_code(200);
        return response([
            'message' => 'User successfully logged in.',
            'data' => $data
        ]);
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|max:255' //29bf07c1-487a-4725-992e-28a6a0b9ecec
        ]);

        try {
            KeycloakAdmin::addon()->logoutById([
                'id' => $request->user_id,
            ]);

            http_response_code(200);
            return response([
                'message' => 'User successfully logged out.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'User failed to log out.',
                'errorCode' => 4004
            ], 400);
        }
    }

    public function checkTokenValidation()
    {
        http_response_code(200);
        return response([
            'message' => 'Token validated.'
        ]);
    }

    public function getTokenInfo()
    {
        http_response_code(200);
        return response([
            'message' => json_decode(Auth::token(), true)
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|max:255',
            'new_password' => 'required|string|max:255',
            'username' => 'required|string|max:100',
        ]);

        try {
            KeycloakAdmin::addon()->logoutById([
                'id' => $request->user_id,
            ]);

            http_response_code(200);
            return response([
                'message' => 'User successfully logged out.'
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'User failed to log out.',
                'errorCode' => 4004
            ], 400);
        }
    }
}
