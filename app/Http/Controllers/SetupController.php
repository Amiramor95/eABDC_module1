<?php

namespace App\Http\Controllers;
use DB;
use Cache;
use App\Models\OauthClient;
use App\Models\Setting;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Artisan;
class SetupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function index(Request $request)
    {
        // if($request->name === 'fimmAdmin' && $request->secret === '@Bcd1234'){

            $key = 'KEYCLOAK_REALM_PUBLIC_KEYxxxxxxxxxx';
            // $key = 'KEYCLOAK_LOAD_USER_FROM_DATABASE';
            // $key = 'KEYCLOAK_LOAD_USER_FROM_DATABASE';
            // $key = 'KEYCLOAK_LOAD_USER_FROM_DATABASE';

            // $KEYCLOAK_REALM_PUBLIC_KEY = DB::table('keycloak_settings')->where('key', 'KEYCLOAK_REALM_PUBLIC_KEY')->value('value');
            $KEYCLOAK_REALM_PUBLIC_KEY = "aa";
            $path = '/var/www/html/.env'; 
            $escaped = preg_quote('='.env($key), '/');
    
            file_put_contents($path, preg_replace(
                "/^{$key}{$escaped}/m",
                "{$key}={$KEYCLOAK_REALM_PUBLIC_KEY}",
                file_get_contents($path)
            ));

            // shell_exec('cd /var/www && php -S localhost:8001 -t public');

        //     return response(['status' => 'success', 'message' => 'Credentials valid']);
        // }else{
        //     return response(['status' => 'failed', 'message' => 'Credentials not valid']);
        // }
        //return view('setup.install');
        // return response()->json('ss');
    }


    public function changeTitle(Request $request)
    {
        if($request->name === 'adminJKR' && $request->secret === '@Bcd1234'){

            $setting = Setting::find(1);
            $setting->appTitle = $request->appTitle;
            $setting->save();

            $nama = "\"".$request->appTitle."\"";
            $key = 'APP_NAME';

            $path = '/var/www/.env';
    
            $escaped = preg_quote('='.env($key), '/');
    
            file_put_contents($path, preg_replace(
                "/^{$key}{$escaped}/m",
                "{$key}={$nama}",
                file_get_contents($path)
            ));

            return response(['status' => 'success', 'message' => 'Credentials valid']);
        }else{
            return response(['status' => 'failed', 'message' => 'Credentials not valid']);
        }
        //return view('setup.install');
        // return response()->json('ss');
    }


    public function getClientSecret()
    {
        $key = 'PASSPORT_CLIENT_SECRET';
        // $secret = OauthClient::find(2, ['secret']); // get client id

        $secret = DB::table('oauth_clients')->where('id', 2)->value('secret');  

        $path = app()->environmentFilePath();

        $escaped = preg_quote('='.env($key), '/');

        file_put_contents($path, preg_replace(
            "/^{$key}{$escaped}/m",
            "{$key}={$secret}",
            file_get_contents($path)
        ));

    }

    //
}