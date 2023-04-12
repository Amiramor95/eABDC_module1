<?php

namespace App\Console\Commands;

use App\Models\KeycloakSetting;
use Artisan;
use DB;
use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;

class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:fimm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize configuration';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        $maindb = $this->choice(
            'Please select main database for this module.',
            ['admin_management',
                'distributor_management',
                'consultant_management',
                'consultantAlert_management',
                'cpd_management',
                'funds_management',
                'finance_management',
                'annualFee_management',
            ],
            0
        );

        $webIP = $this->anticipate('Please enter the IP for Web Server', ['192.168.2.66']);

        if ($this->confirm('Do you want to generate API docs?')) {
            Artisan::call('generate:docs');

            echo 'API docs generated.';
        } else {

        }
        if ($this->confirm('Please ensure you already configured Keycloak before performing this request. Do you wish to continue?')) {

            // $webIP = $this->anticipate('Please enter the IP for Web Server', ['192.168.2.66']);

            $dbIP = $this->anticipate('Please enter the IP for DB Server', ['192.168.3.34']);
            $dbPort = $this->anticipate('Please enter the port for DB Server MySQL', ['3306']);

            $smsKey = $this->anticipate('Please enter the SMS Key for SMSOcean', ['4c9a1d80']);
            $smsSecret = $this->anticipate('Please enter the SMS Secret for SMSOcean', ['9c512533']);

            $copy = copy(public_path('../JWT.php'), public_path('../vendor/firebase/php-jwt/src/JWT.php'));
            $copy = copy(public_path('../KeycloakGuard.php'), public_path('../vendor/robsontenorio/laravel-keycloak-guard/src/KeycloakGuard.php'));
            // $copy = copy(public_path('../config/keycloakAdmin.php'), public_path('../vendor/haizad/laravel-keycloak-admin/src/Config/keycloakAdmin.php'));

            // $setting = KeycloakSetting::where('KEYCLOAK_CLIENT_ID', 'distributor-client')->first();
            $setting = KeycloakSetting::where('KEYCLOAK_CLIENT_ID', 'fimm-app')->first();

            $public_key_raw = Curl::to($setting->KEYCLOAK_REALM_URL)->get();
            $public_key = json_decode($public_key_raw, true)['public_key'];

            $client_secret = DB::table('keycloak.CLIENT')
                ->where('CLIENT_ID', $setting->KEYCLOAK_CLIENT_ID)
                ->value('SECRET');

            //->get();
            //->pluck('SECRET');

            //->first();
            // $updatePublic = KeycloakSetting::where('KEYCLOAK_CLIENT_ID', 'reg-client')->first();
            $updatePublic = KeycloakSetting::where('KEYCLOAK_CLIENT_ID', 'fimm-app')->first();
            $updatePublic->KEYCLOAK_REALM_PUBLIC_KEY = $public_key;
            $updatePublic->KEYCLOAK_CLIENT_SECRET = $client_secret;
            $updatePublic->save();

            $keyclock_info = array(
                'KEYCLOAK_BASE_URL' => $setting->KEYCLOAK_AUTH_URL,
                'KEYCLOAK_REALM_PUBLIC_KEY' => $public_key,
                'KEYCLOAK_CLIENT_ID' => $setting->KEYCLOAK_CLIENT_ID,
                'KEYCLOAK_REALM' => $setting->KEYCLOAK_REALM_NAME,
                'KEYCLOAK_CLIENT_SECRET' => $client_secret,
                'DB_HOST' => $dbIP,
                'DB_PORT' => $dbPort,
                'SMS_KEY' => $smsKey,
                'SMS_SECRET' => $smsSecret,
                'DB_DATABASE' => $maindb
            );

            $path = public_path('../.env');

            foreach ($keyclock_info as $key => $value) {
                $escaped = preg_quote('=' . env($key), '/');

                file_put_contents($path, preg_replace(
                    "/^{$key}{$escaped}/m",
                    "{$key}={$value}",
                    file_get_contents($path)
                ));
            }

            echo 'FiMM Registration setup has been completed.';

        } else {
            echo 'FiMM Registration setup failed.';
        }
    }
}
