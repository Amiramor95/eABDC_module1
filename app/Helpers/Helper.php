<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Ixudra\Curl\Facades\Curl;
use App\Models\Consultant;
use App\Models\Distributor;
use App\Models\UserDistributor;
use App\Models\SettingGeneral;
use App\Models\SettingCity;
use App\Models\SettingPostal;
use App\Http\Resources\ConsultantResource;
use App\Http\Resources\DistributorResource;

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

/**
 * Reformat request from uppercase to lowercase
 */
if (!function_exists('resetRequest')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function resetRequest($request = [])
    {
        $response = [];
        if ($request) {
            foreach ($request as $key => $data) {
                $response[strtolower($key)] = $data;
            }
        }
        return $response;
    }
}

/**
 * get setting general detail
 */
if (!function_exists('getSettingGeneralValue')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function getSettingGeneralValue($id = null)
    {
        $settingGeneral = null;
        if ($id) {
            $settingGeneral = SettingGeneral::find($id);
        }
        return $settingGeneral;
    }
}

/**
 * get setting city
 */
if (!function_exists('getSettingCityValue')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function getSettingCityValue($id = null)
    {
        $settingCity = null;
        if ($id) {
            $settingCity = SettingCity::find($id);
        }
        return $settingCity;
    }
}

/**
 * get setting postal
 */
if (!function_exists('getSettingPostalValue')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string $path
     * @return string
     */
    function getSettingPostalValue($id = null)
    {
        $settingPostal = null;
        if ($id) {
            $settingPostal = SettingPostal::find($id);
        }
        return $settingPostal;
    }
}

/**
 * generate keycloak URL
 */

if (!function_exists('getKeycloakUrl')) {
    /**
     * Get Keycloak end point URL from config.
     *
     * @param string $route_param
     *
     * @return string
     */
    function getKeycloakUrl(string $route_param, array $params = [], $master = false): string
    {
        // dd(config('params.keycloak.realm'));
        $realm = config('params.keycloak.realm');
        if ($master) {
            $realm = 'master';
        }
        $client = config('params.keycloak.client_uuid');
        $base_url = config('params.keycloak.base_url');
        $route = config('params.keycloak.routes.' . $route_param);

        $route = str_replace('{realm}', $realm, $route);
        $route = str_replace('{client}', $client, $route);
        if ($params) {
            foreach ($params as $key => $param) {
                $route = str_replace('{' . $key . '}', $param, $route);
            }
        }

        return "$base_url/$route";
    }
}

/**
 * get user keycloak by request token
 */
if (!function_exists('getDistributorAdminAndManager')) {
    function getDistributorAdminAndManager($distributorId): array
    {
        $response = [
            'admin' => [],
            'manager' => [],
        ];

        $userDistributors = UserDistributor::where(['USER_DIST_ID' => $distributorId])->get();

        foreach ($userDistributors as $key => $userDistributor) {
            $masterToken = getMasterToken();

            $responseRole = Curl::to(getKeycloakUrl('userRole', ['id' => $userDistributor->KEYCLOAK_ID]))
                ->withBearer($masterToken)
                // ->withHeader(['Accept: application/json', 'Content-Type: application/json'])
                ->withContentType('application/json')
                ->asJsonRequest()
                ->get();

            $responseRoles = json_decode($responseRole, true);
            if ($responseRoles && !isset($responseRoles['error'])) {
                foreach ($responseRoles as $key => $responseRole) {
                    if ($responseRole['name'] == 'distributor_admin') {
                        $response['admin'][] = $userDistributor;
                    } else if ($responseRole['name'] == 'distributor_fund_manager') {
                        $response['manager'][] = $userDistributor;
                    }
                }
            }
        }

        return $response;
    }
}

/**
 * get user keycloak by request token
 */
if (!function_exists('getMasterToken')) {
    function getMasterToken()
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
            return null;
        }

        return $response->object()->access_token;
    }
}

/**
 * get user keycloak by request token
 */
if (!function_exists('getUserKeycloak')) {
    function getUserKeycloak(): array
    {
        $response = [];
        if (Auth::check()) {
            $keycloakDetails = json_decode(Auth::token());

            $roles = [];
            foreach ($keycloakDetails->resource_access as $key => $resourceAccess) {
                if ($resourceAccess->roles) {
                    foreach ($resourceAccess->roles as $key => $role) {
                        $roles[] = $role;
                    }
                }
            }

            if (Auth::hasRole(config('params.keycloak.client_id'), 'distributor_admin') || Auth::hasRole(config('params.keycloak.client_id'), 'distributor_fund_manager')) {
                // dd($keycloakDetails);
                //get distributor
                $userDistributor = UserDistributor::where(['KEYCLOAK_ID' => $keycloakDetails->sub])->whereNotNull('USER_DIST_ID')->first();

                if (!$userDistributor || @$userDistributor->USER_DIST_ID == 0) {
                    return [
                        'message' => 'No distributor registered under this user.',
                        'errorCode' => 4103,
                    ];
                }

                $distributor = Distributor::where(['DISTRIBUTOR_ID' => $userDistributor->USER_DIST_ID])->first();
                if (!$distributor) {
                    return [
                        'message' => 'Invalid user.',
                        'errorCode' => 4103,
                    ];
                }

                $response = [
                    'keycloak_id' => $keycloakDetails->sub,
                    'keycloak_role' => $roles ? $roles[0] : null,
                    'email' => $keycloakDetails->email,
                    'username' => $keycloakDetails->username,
                    // 'first_login' => $consultant->CONSULTANT_ISLOGIN,
                    // 'first_login_value' => $consultant->CONSULTANT_ISLOGIN == 0 ? 'YES' : 'NO',
                    'resource_access' => $keycloakDetails->resource_access,
                    'roles' => $roles,
                    'email' => $keycloakDetails->email,
                    'distributor' => new DistributorResource($distributor),
                ];
            }

            if (Auth::hasRole(config('params.keycloak.client_id'), 'fimm_rd')) {
                return $response = [
                    'keycloak_id' => $keycloakDetails->sub,
                    'keycloak_role' => $roles ? $roles[0] : null,
                    'email' => $keycloakDetails->email,
                    'username' => $keycloakDetails->username,
                    'resource_access' => $keycloakDetails->resource_access,
                    'roles' => $roles,
                    'email' => $keycloakDetails->email,
                ];
            }

            if (Auth::hasRole(config('params.keycloak.client_id'), 'consultant')) {
                $consultant = Consultant::where(['KEYCLOAK_ID' => $keycloakDetails->sub])->first();
                if (!$consultant) {
                    return [
                        'message' => 'Invalid user.',
                        'errorCode' => 4103,
                    ];
                }

                $response = [
                    'keycloak_id' => $keycloakDetails->sub,
                    'keycloak_role' => $roles ? $roles[0] : null,
                    'email' => $keycloakDetails->email,
                    'username' => $keycloakDetails->username,
                    'first_login' => $consultant->CONSULTANT_ISLOGIN,
                    'first_login_value' => $consultant->CONSULTANT_ISLOGIN == 0 ? 'YES' : 'NO',
                    'resource_access' => $keycloakDetails->resource_access,
                    'roles' => $roles,
                    'email' => $keycloakDetails->email,
                    'consultant' => new ConsultantResource($consultant),
                ];
            }
        }
        return $response;
    }
}
