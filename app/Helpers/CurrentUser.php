<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Ixudra\Curl\Facades\Curl;

use App\Helpers\Decrypt;
use App\Models\KeycloakDefaultGroup;
use App\Models\KeycloakSettings;
use App\Models\PasswordHistory;
use App\Helpers\Helper\getKeycloakUrl;
use Illuminate\Support\Facades\Log;

use Config;

class CurrentUser
{
    public function getUserDetails($token)
    {
        $response = Curl::to(env('KEYCLOAK_BASE_URL', 0) . '/realms/ldap-realm/protocol/openid-connect/userinfo')
            ->withBearer($token)
            ->get();
        $response = json_decode($response, true);
        return $response;
    }

    public function changePassword(Request $request)
    {
        $token = $request->bearerToken();

        $response = Curl::to(env('KEYCLOAK_BASE_URL', 0) . '/realms/ldap-realm/account/credentials/password')
            ->withBearer($token)
            ->withData([
                'currentPassword' => $request->oldPassword,
                'newPassword' => $request->newPassword,
                'confirmation' => $request->confirmation,
            ])
            ->post();

        if ($response) { //if password successfully changed, then update PASSWORD_HISTORY table

            $secret = random_bytes(30);
            $data = new Decrypt();
            $hashedOldPassword = $data->hashingOldPass($request->oldPassword, $secret);

            $passwordLog = new PasswordHistory;
            $passwordLog->KEYCLOAK_ID = $request->KEYCLOAK_ID;
            $passwordLog->PASSWORD = $hashedOldPassword;
            $passwordLog->SECRET = $secret;
            $passwordLog->save();
        }

        return $response;
    }

    public function comparePreviousPassword(Request $request)
    {
        //get list of Password History of user
        $passwords = PasswordHistory::where('KEYCLOAK_ID', $request->KEYCLOAK_ID)->get();

        $checkIfValid = false;

        foreach ($passwords as $password) {
            $data = new Decrypt();
            $checkIfValid = $data->compareOldPass($password->PASSWORD, $request->password, $password->SECRET);

            if ($checkIfValid) {
                break;
            }
        }

        return $checkIfValid;
    }

    public function getUserByEmail(Request $request)
    {
        // $response= DB::table('keycloak2.USER_ENTITY')
        // ->select('ID')
        // -> where('EMAIL','=', $request-> email)
        // -> get();
        // return $response;

        $token = $request->bearerToken();
        $response = Curl::to(env('KEYCLOAK_BASE_URL', 0) . '/realms/ldap-realm/user?email=' . $request->email)
            ->get();
        return $response;
    }

    public static function userType()
    {
        if (session()->has('user')) {
            $user = session()->get('user', false);

            return $user->userType;
        } else {
            return null;
        }
    }
    private function getMasterToken()
    {
        $response =  Http::retry(3, 100)
        ->asForm()
        ->post(getKeycloakUrl('masterLogin'), [
                'grant_type' => 'password',
                'client_id' => config('params.keycloak.master_client_id'),
                'client_secret' => config('params.keycloak.master_client_secret'),
                'username' => config('params.keycloak.master_username'),
                'password' => config('params.keycloak.master_password'),
                'scope' => 'openid',
            ])
            ->throw();

        if (!$response->successful()) {
            abort(400, __('Failed to get master token'));
        }

        return $response->object()->access_token;
    }

    public function createUser($request)
    {
        $data = new Decrypt();
        $admin = $data->keycloakAdminPass();

        $setting = KeycloakSettings::where('KEYCLOAK_REALM_NAME', 'master')
            ->first();

        $response = Curl::to($setting->KEYCLOAK_TOKEN_URL)

            ->withData([
                'username' => $admin['username'],
                'password' => $admin['password'],
                'client_id' => $setting->KEYCLOAK_CLIENT_ID,
                'grant_type' => 'password',
                'client_secret' => $setting->KEYCLOAK_CLIENT_SECRET,
                'scope' => 'openid',
            ])
            ->post();

        $response = json_decode($response, true);

        $token = $this->getMasterToken();

        $responseCreateUser =  Http::withToken($token)->post(getKeycloakUrl('createUser', [], true), [
                'email' => $request->EMAIL,
                'enabled' => true,
                'username' => $request->USERNAME,
                'emailVerified' => false,
                'credentials' => [
                    [
                    'type' => 'password',
                    'value' => $request->PASSWORD,
                    'temporary' => false,
                ]
            ],
            ]);

        $response = json_decode($responseCreateUser, true);

        if ($response && @$response['errorMessage']) {
            abort(400, $response['errorMessage']);
        }

        $userEntity = DB::table(env('KEYCLOAK_DATABASE',0).'.USER_ENTITY')
            -> select('ID')
            -> where('EMAIL', '=', $request->EMAIL)
            -> first();

        $realmSet = 'ldap-realm';
        $updateRealm = DB::table(env('KEYCLOAK_DATABASE').'.USER_ENTITY')
                ->where('ID', $userEntity->ID)
                ->update(['REALM_ID' => $realmSet]);

        $this->addRoles($token, $userEntity, $request);

        return $userEntity;
    }

    private function addRoles(string $token, $userEntity, $request)
    {
        $roles = collect(config('params.keycloak.roles'));

        $filteredRoles = collect($request->userRoles)->map(function ($value) use ($roles) {
            if ($role = $roles->firstWhere('name', $value)) {
                return [
                    'id' => $role['id'],
                    'name' => $role['name']
                ];
            }
            return null;
        })->reject(function ($value) {
            return empty($value);
        });

        $payload = $filteredRoles->toArray();

        $response =  Http::withToken($token)
            ->post(getKeycloakUrl('mapRole', ['id' => $userEntity->ID]), $payload)
            ->throw();

        if (!$response->successful()) {
            abort(400, __('Failed to add roles'));
        }

        return true;
    }


    public function addToMainGroup($keycloakId, $group)
    {
        //decrypt Keycloak admin details
        $data = new Decrypt();
        $admin = $data->keycloakAdminPass();

        $groupDetails = KeycloakDefaultGroup::find($group); //1 : FiMM User , 2 : Distributor , 3 : Consultant , 4 : Training Provider , 5 : Third Party
        $groupId = $groupDetails->GROUP_ID;
        // dd($groupId);
        $setting = KeycloakSettings::where('KEYCLOAK_REALM_NAME', 'master')
            ->first();

        $response = Curl::to($setting->KEYCLOAK_TOKEN_URL)

            ->withData([
                'username' => $admin['username'],
                'password' => $admin['password'],
                'client_id' => $setting->KEYCLOAK_CLIENT_ID,
                'grant_type' => 'password',
                'client_secret' => $setting->KEYCLOAK_CLIENT_SECRET,
            ])
            ->post();

        $response = json_decode($response, true);

        $token = $response['access_token'];
        $response = Curl::to(env('KEYCLOAK_BASE_URL', 0) . '/admin/realms/' . env('KEYCLOAK_REALM', 0) . '/users/' . $keycloakId . '/groups/' . $groupId)
            ->withBearer($token)
            ->put();

        return $response;
    }

    public function resetPassword($parameter)
    {
        //decrypt Keycloak admin details
        $data = new Decrypt();
        $admin = $data->keycloakAdminPass();

        $setting = KeycloakSettings::where('KEYCLOAK_REALM_NAME', 'master')
            ->first();

        $response = Curl::to($setting->KEYCLOAK_TOKEN_URL)

            ->withData([
                'username' => $admin['username'],
                'password' => $admin['password'],
                'client_id' => $setting->KEYCLOAK_CLIENT_ID,
                'grant_type' => 'password',
                'client_secret' => $setting->KEYCLOAK_CLIENT_SECRET,
            ])
            ->post();

        $response = json_decode($response, true);

        $token = $response['access_token']; //get admin token
        /**
         * Paramater to pass : KEYCLOAK_ID ->Keycloak User Id
         */
        $response = Curl::to(env('KEYCLOAK_BASE_URL', 0) . '/admin/realms/' . env('KEYCLOAK_REALM', 0) . '/users/' . $parameter->KEYCLOAK_ID . '/reset-password')
            ->withBearer($token)
            ->withData(array('type' => 'password', 'temporary' => 'false', 'value' => $parameter->newPassword))
            ->asJson()
            ->put();

        return $response;
    }

    public function resetPasswordByTAC($parameter)
    {

        $token = $this->getMasterToken();
         $response = Curl::to(env('KEYCLOAK_BASE_URL', 0) . '/admin/realms/' . env('KEYCLOAK_REALM', 0) . '/users/' . $parameter->KEYCLOAK_ID . '/reset-password')
             ->withBearer($token)
             ->withData(array('type' => 'password', 'temporary' => 'false', 'value' => $parameter->PASSWORD))
             ->asJson()
             ->put();
         return $response;
    }

    public function resetPasswordByEmail($parameter)
    {
        $token = $request->bearerToken();
        $response = Curl::to(env('KEYCLOAK_BASE_URL', 0) . '/admin/realms/' . env('REALM', 0) . '/user/' . $parameter->KEYCLOAK_ID . '/execute-actions-email')
            ->withBearer($token)
            ->withData(["UPDATE_PASSWORD"])
            ->post();
        return $response;
    }

    public static function HasAccess($parameter)
    {
        $proceed = false;

        switch (self::UserType()) {
            case 'admin':
                $proceed = config('access.admin.' . $access, false);
                break;
            case 'parent':
                $proceed = config('access.parent.' . $access, false);
                break;
            case 'agent':
                $proceed = config('access.agent.' . $access, false);
                break;
            case 'saleOperator':
                $proceed = config('access.saleOperator.' . $access, false);
                break;
            case 'teacher':
                $proceed = config('access.teacher.' . $access, false);
                break;
            case 'bookshop':
                $proceed = config('access.bookshopAgent.' . $access, false);
                break;
            case 'serviceProvider':
                $proceed = config('access.serviceProvider.' . $access, false);
                break;
            case 'soleProprietor':
                $proceed = config('access.soleProprietor.' . $access, false);
                break;
            case 'moderator':
                $proceed = config('access.moderator.' . $access, false);
                break;
        }

        return $proceed;
    }
}
