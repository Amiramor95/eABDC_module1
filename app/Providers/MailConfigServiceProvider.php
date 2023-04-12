<?php

namespace App\Providers;

use Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $str = file_get_contents(public_path('../app_status.json'));
        $json = json_decode($str, true); 
        $dbConnected = $json['isDBConnected'];

        if ($dbConnected) {
            if (\Schema::hasTable('SETTING_EMAIL')) {
                $settings = DB::table('SETTING_EMAIL')->first();
                if ($settings) //checking if table is not empty
                {
                    $config = array(
                        'driver'     => 'smtp',
                        'host'       => $settings->EMAIL_SMTP_SERVER,//'smtp.office365.com'
                        'port'       => $settings->EMAIL_SMTP_PORT,//'587'
                        'encryption' => $settings->EMAIL_SECURITY,
                        'username'   => $settings->EMAIL_FROM,//'hidayatul99@outlook.com'
                        'password'   => $settings->EMAIL_LOGIN_PASS,//'msqaggsidkphnncc'
                        'sendmail'   => '/usr/sbin/sendmail -bs',
                        'pretend'    => false,
                    );
                    Config::set('mail', $config);
                }
            }
        }else{
        }
    }
}