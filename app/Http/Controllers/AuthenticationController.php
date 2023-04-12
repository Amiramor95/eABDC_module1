<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Consultant;
use App\Models\SettingGeneral;
use App\Models\UserEntity;
use Illuminate\Support\Facades\Auth;
use Ixudra\Curl\Facades\Curl;
use App\Models\UserSecurityQuestion;
use App\Mail\NewRegistration;
use App\Models\SmsTac;
use App\Services\Sms;


class AuthenticationController extends Controller
{
    public function loginKeycloak(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'USERNAME' => 'required|string',
            'PASSWORD' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'The given data was invalid.',
                'errorCode' => 4106,
                'errors' => $validator->errors()
            ], 400);
        }

        $requestToken = Curl::to(getKeycloakUrl('login'))
            ->withData([
                'username' => $request->USERNAME,
                'password' => $request->PASSWORD,
                'client_id' => config('keycloakAdmin.client.id'),
                'grant_type' => 'password',
                'client_secret' => config('keycloakAdmin.client.secret'),
                'scope' => 'openid',
            ])
            ->post();
        $response = json_decode($requestToken, true);

        if ($response && @$response['error']) {
            $message = $response['error'];
            if ($response['error'] == 'invalid_grant') {
                $message = 'Please enter a valid username and password.';
            }
            return response([
                'message' => $message,
                'errorCode' => 4103,
            ], 400);
        }

        return response([
            'message' => 'User successfully logged in.',
            'data' => [
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_in' => $response['expires_in'],
                'refresh_expires_in' => $response['refresh_expires_in'],
            ],
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'USERNAME' => 'required|string',
            'PASSWORD' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'The given data was invalid.',
                'errorCode' => 4106,
                'errors' => $validator->errors()
            ], 400);
        }

        $requestToken = Curl::to(getKeycloakUrl('login'))
            ->withData([
                'username' => $request->USERNAME,
                'password' => $request->PASSWORD,
                'client_id' => config('keycloakAdmin.client.id'),
                'grant_type' => 'password',
                'client_secret' => config('keycloakAdmin.client.secret'),
                'scope' => 'openid',
            ])
            ->post();
        $response = json_decode($requestToken, true);

        if ($response && @$response['error']) {
            $message = $response['error'];
            if ($response['error'] == 'invalid_grant') {
                $message = 'Please enter a valid username and password.';
            }
            return response([
                'message' => $message,
                'errorCode' => 4103,
            ], 400);
        }

        $consultant = Consultant::where(['USER_ID' => $request->USERNAME])->whereNotNull('KEYCLOAK_ID')->first();
        if (!$consultant) {
            return response([
                'message' => 'Invalid username.',
                'errorCode' => 4103,
            ], 400);
        }

        return response([
            'message' => 'User successfully logged in.',
            'data' => [
                'first_login' => $consultant->CONSULTANT_ISLOGIN,
                'first_login_value' => $consultant->CONSULTANT_ISLOGIN == 0 ? 'YES' : 'NO',
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
                'expires_in' => $response['expires_in'],
                'refresh_expires_in' => $response['refresh_expires_in'],
            ],
        ]);
    }

    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'REFRESH_TOKEN' => 'required|string'
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'The given data was invalid.',
                'errorCode' => 4106,
                'errors' => $validator->errors()
            ], 400);
        }

        $requestToken = Curl::to(getKeycloakUrl('login'))
            ->withData([
                'client_id' => config('keycloakAdmin.client.id'),
                'client_secret' => config('keycloakAdmin.client.secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->REFRESH_TOKEN,
            ])
            ->post();
        $response = json_decode($requestToken, true);

        if ($response && @$response['error']) {
            return response([
                'message' => $response['error'],
                'errorCode' => 4103,
            ], 400);
        }

        return response([
            'message' => 'Successfully refresh token.',
            'data' => $response,
        ]);
    }

    public function getUser(Request $request)
    {
        if (Auth::check()) {
            return response([
                'message' => 'Data successfully retrieved.',
                'data' => getUserKeycloak(),
            ]);
        }

        return response([
            'message' => 'Invalid token.',
            'errorCode' => 4103,
        ], 401);
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'USER_ID' => ['required', 'string', 'unique:CONSULTANT,USER_ID'],
            'CONSULTANT_CITIZEN' => ['required', 'integer'],
            'CONSULTANT_NRIC' => ['nullable', 'string', 'unique:CONSULTANT,CONSULTANT_NRIC'],
            'CONSULTANT_NRIC_OLD' => ['nullable', 'string', 'unique:CONSULTANT,CONSULTANT_NRIC_OLD'],
            'CONSULTANT_PASSPORT_NO' => ['nullable', 'string', 'unique:CONSULTANT,CONSULTANT_PASSPORT_NO'],
            'CONSULTANT_PASSPORT_EXPIRY_NO' => ['nullable', 'string'],
            'CONSULTANT_EMAIL' => ['required', 'string', 'email', 'unique:CONSULTANT,CONSULTANT_EMAIL'],
            'CONSULTANT_EMAIL_CONFIRMATION' => ['required', 'string', 'email', 'same:CONSULTANT_EMAIL'],
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'The given data was invalid.',
                'errorCode' => 4106,
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            //generate OTP number
            $randomPassword = Str::random(8);

            $request->merge([
                'CONSULTANT_ISLOGIN' => 0, //0-first login 1-not first
                'OTP_PASSWORD' => Hash::make($randomPassword),
            ]);

            //create new user consultant
            $consultant = Consultant::create(
                $request->only(
                    'USER_ID',
                    'CONSULTANT_CITIZEN',
                    'CONSULTANT_NRIC',
                    'CONSULTANT_NRIC_OLD',
                    'CONSULTANT_PASSPORT_NO',
                    'CONSULTANT_PASSPORT_EXPIRY_NO',
                    'CONSULTANT_EMAIL',
                    'OTP_PASSWORD',
                    'CONSULTANT_ISLOGIN'
                )
            );

            //send OTP to email
            $data = array(
                'email' => $request->CONSULTANT_EMAIL,
                'name' => $request->USER_ID,
                'password' => $randomPassword,
                'loginUrl' => 'https://lfcs-dev.fimm.com.my',
            );

            Mail::to($data['email'])->send(new NewRegistration($data));

            //register user to keyclock
            $request->merge([
                'EMAIL' => $request->CONSULTANT_EMAIL,
                'USERNAME' => $request->USER_ID,
                'PASSWORD' => $randomPassword,
                'ROLES' => [
                    'consultant'
                ]
            ]);
            $masterToken = $this->getMasterToken();
            $userEntity = $this->createNewUser($masterToken, $request);
            if (!$userEntity) throw new \ErrorException('Failed to save user.');

            // map user to role
            $this->addRoles($masterToken, $userEntity, $request);

            //update keycloak id
            $consultant->KEYCLOAK_ID = $userEntity->ID;
            if (!$consultant->save()) throw new \ErrorException('Failed to update consultant.');

            DB::commit();

            http_response_code(201);
            return response([
                'message' => 'Successfully created.',
                'data' => null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            http_response_code(400);
            return response([
                'message' => $e->getMessage() ?? 'Failed to store data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    private function getMasterToken()
    {
        $response =  Http::retry(3, 100)->asForm()
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

    private function createNewUser(string $masterToken, $request): ?UserEntity
    {
        $responseCreateUser =  Http::withToken($masterToken)
            ->post(getKeycloakUrl('createUser', [], true), [
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

        $userEntity = UserEntity::updateOrcreate([
            'USERNAME' => $request->USERNAME
        ], [
            'REALM_ID' => config('params.keycloak.realm'),
        ]);
        return $userEntity;
    }

    private function addRoles(string $masterToken, UserEntity $userEntity, $request)
    {
        $roles = collect(config('params.keycloak.roles'));

        $filteredRoles = collect($request->ROLES)->map(function ($value) use ($roles) {
            if ($role = $roles->firstWhere('name',  $value)) {
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

        $response =  Http::withToken($masterToken)
            ->post(getKeycloakUrl('mapRole', ['id' => $userEntity->ID]), $payload)
            ->throw();

        if (!$response->successful()) {
            abort(400, __('Failed to add roles'));
        }

        return true;
    }

    public function getSecurityQuestion(Request $request)
    {
        $userSecurityQuestions = UserSecurityQuestion::all();
        return response([
            'message' => 'Data successfully retrieved.',
            'data' => $userSecurityQuestions,
        ]);
    }

    public function setupSecurity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'PASSWORD' => ['required', 'min:8'],
            'PASSWORD_CONFIRMATION' => ['required', 'min:8', 'same:PASSWORD'],
            'PHONE' => ['required', 'string', 'unique:CONSULTANT,CONSULTANT_MOBILE_NO'],
            'PHONE_TAC' => ['required', 'integer'],
            'SECURITY_QUESTION_ID' => ['required', 'integer'],
            'SECURITY_ANSWER' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'The given data was invalid.',
                'errorCode' => 4106,
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            //get keycloak id by token
            $user = getUserKeycloak();
            if (!$user) throw new \ErrorException('Invalid token.');

            //get consultant
            $consultant = Consultant::where(['KEYCLOAK_ID' => $user['keycloak_id'], 'CONSULTANT_ISLOGIN' => 0])->first();
            if (!$consultant) {
                return response([
                    'message' => 'You not first login.',
                    'errorCode' => 4103,
                ], 400);
            }

            //update consultant
            $consultant->CONSULTANT_MOBILE_NO = $request->PHONE;
            $consultant->CONSULTANT_ISLOGIN = 1; //0-first login 1-not first
            $consultant->SECURITY_QUESTION_ID = $request->SECURITY_QUESTION_ID;
            $consultant->SECURITY_ANSWER = $request->SECURITY_ANSWER;
            if (!$consultant->save()) throw new \ErrorException('Failed update consultant.');

            //update keycloak password
            $masterToken = $this->getMasterToken();
            $requestToken = Curl::to(getKeycloakUrl('resetPassword', ['id' => $consultant->KEYCLOAK_ID], false))
                ->withBearer($masterToken)
                // ->withHeader(['Accept: application/json', 'Content-Type: application/json'])
                ->withContentType('application/json')
                ->asJsonRequest()
                ->withData([
                    'temporary' => false,
                    'value' => $request->PASSWORD,
                    'type' => 'password',
                ])
                ->asJson()
                ->put();
            $responseResetPass = json_decode($requestToken, true);
            if ($responseResetPass && @$responseResetPass['error']) {
                throw new \ErrorException($responseResetPass['error']);
            }

            DB::commit();

            http_response_code(200);
            return response([
                'message' => 'Successfully setup account.',
                'data' => getUserKeycloak(),
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            http_response_code(400);
            return response([
                'message' => $e->getMessage() ?? 'Failed to store data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function phoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'PHONE' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'The given data was invalid.',
                'errorCode' => 4106,
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $sms = new Sms();
            $tac = rand(100000, 999999);
            $data = [
                'phone' => $request->PHONE,
                'message' => 'Your TAC code is ' . $tac,
            ];
            $responseSms = $sms->send($data);
            if ($responseSms && !@$responseSms['status']) throw new \ErrorException('Failed to send sms otp.' . '(' . $responseSms['message'] . ')');

            // $expired = \Carbon\Carbon::now()->addMinutes(60)->timestamp;
            // $smsTac = SmsTac::where(['SMS_TAC_RECIPIENT' => $request->PHONE])->first();
            // if (!$smsTac) {
            //     $smsTac = new SmsTac;
            // }
            // $smsTac->SMS_TAC_NUMBER    = $tac;
            // $smsTac->SMS_TAC_RECIPIENT = $request->PHONE;
            // $smsTac->SMS_TAC_END_TIME = $expired;
            // if (!$smsTac->save()) throw new \ErrorException('Failed save sms tac.');

            DB::commit();

            http_response_code(201);
            return response([
                'message' => 'OTP successfully delivered.',
                'data' => null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            http_response_code(400);
            return response([
                'message' => $e->getMessage() ?? 'Failed to store data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'PHONE' => ['required', 'string', 'exists:CONSULTANT,CONSULTANT_MOBILE_NO'],
            'PHONE_TAC' => ['required', 'integer'],
            'PASSWORD' => ['required', 'min:8'],
            'PASSWORD_CONFIRMATION' => ['required', 'min:8', 'same:PASSWORD'],
        ]);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'The given data was invalid.',
                'errorCode' => 4106,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            //check otp
            $responseVerifyOtp = Curl::to(config('params.module0.verifyTac'))
                ->withData(['SMS_TAC_NUMBER' => $request->PHONE_TAC, 'SMS_TAC_RECIPIENT' => $request->PHONE])
                ->get();
            $responseOtp = json_decode($responseVerifyOtp, true);
            if ($responseOtp && !@$responseOtp['data']) {
                $message = $responseOtp['message'] ?? 'Invalid otp.';
                throw new \ErrorException($message);
            }

            //get consultant
            $consultant = Consultant::where(['CONSULTANT_MOBILE_NO' => $request->PHONE, 'CONSULTANT_ISLOGIN' => 1])->whereNotNull('KEYCLOAK_ID')->first();
            if (!$consultant) {
                throw new \ErrorException('Invalid request. Please enter the correct data.');
            }

            //update keycloak password
            $masterToken = $this->getMasterToken();
            $requestToken = Curl::to(getKeycloakUrl('resetPassword', ['id' => $consultant->KEYCLOAK_ID], false))
                ->withBearer($masterToken)
                // ->withHeader(['Accept: application/json', 'Content-Type: application/json'])
                ->withContentType('application/json')
                ->asJsonRequest()
                ->withData([
                    'temporary' => false,
                    'value' => $request->PASSWORD,
                    'type' => 'password',
                ])
                ->asJson()
                ->put();
            $responseResetPass = json_decode($requestToken, true);
            if ($responseResetPass && @$responseResetPass['error']) {
                throw new \ErrorException($responseResetPass['error']);
            }

            http_response_code(201);
            return response([
                'message' => 'Successfully reset password.',
                'data' => null,
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            http_response_code(400);
            return response([
                'message' => $e->getMessage() ?? 'Failed to store data.',
                'errorCode' => 4103,
            ], 400);
        }
    }
}
