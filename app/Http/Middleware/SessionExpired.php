<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\Store;
use Auth;
use Session;
//use App\Models\User;
//use App\Models\LoginIdleSession;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
//use App\Helpers\CurrentUser;
use DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Auth\ThrottlesLogins;

class SessionExpired
{
    // protected $session;
    // protected $timeout = 5;
    use ThrottlesLogins;
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $AUTHUSERID=$request->header('Uid');
        $PANELTRACK=$request->header('PANELTRACK');
        $now = Carbon::now();
        $lastlogtime = $now->format('Y-m-d H:i:s');
        $uri = $request->path();
       // Log::info("PATH=============".$uri);

       $reqthrotle=$this->throttleKey($request);
       $paramstr= explode("|", $reqthrotle);
       $blockUserName=$paramstr[0];
       $blockIp=$paramstr[1];

        if ($uri != 'api/module0/get_fimm_login_status') {
            if ($PANELTRACK == 'STAFF') {
                // Log::info($next);
                // echo base_url();

               

               // Log::info("blockIp:".$blockIp);
                // GET USER INFO

                $auth_user = DB::table('admin_management.USER AS AUSER')
                ->select('AUSER.LAST_SEEN_AT AS LAST_SEEN_AT', 'AUSER.ISLOGIN AS ISLOGIN')
                ->where('AUSER.USER_ID', $AUTHUSERID)
                ->first();

                $idleSession = DB::table('admin_management.LOGIN_IDLE_SESSION AS IDLE')
                ->select('IDLE.LOGIN_IDLE_SESSION_MIN AS LOGIN_IDLE_SESSION_MIN')
                ->orderBy('IDLE.LOGIN_IDLE_SESSION_ID', 'desc')
                ->first();

                // Log::info("idleSession :".$idleSession->LOGIN_IDLE_SESSION_MIN);

                $last_seen = Carbon::parse($auth_user->LAST_SEEN_AT);
                $absence = $last_seen->diffInMinutes($now, true);
                $sessionidletime = $idleSession->LOGIN_IDLE_SESSION_MIN ?? config('session.lifetime');
                Log::info("absence:".$absence);
                if ($absence > $sessionidletime) {//$sessionidletime
                    $updatedata=DB::table('admin_management.USER as AMUSER')->where('AMUSER.USER_ID', $AUTHUSERID)->update(['AMUSER.ISLOGIN' => 0]);
                  // Fimm User Log
                    $getLogIP = DB::table('admin_management.FIMM_USER_LOG AS FIMMLOG')->where('FIMMLOG.USER_ID', $AUTHUSERID)->where('FIMMLOG.LOG_IP', $blockIp)->where('FIMMLOG.STATUS', 1)->orderBy('FIMMLOG.LOG_ID', 'desc')->first();
                    if($getLogIP){
                        $updatelog=DB::table('admin_management.FIMM_USER_LOG as FIMMLOG')
                                    ->where('FIMMLOG.USER_ID', $AUTHUSERID)
                                    ->where('FIMMLOG.LOG_IP', $blockIp)
                                    ->where('FIMMLOG.STATUS', 1)
                                    ->update(['FIMMLOG.LOGOUT_TIMESTAMP' => $lastlogtime,'FIMMLOG.STATUS' =>0]);

                    }

                    session()->flush();
                    //Log::info("absence111 :".$absence);
                    return $next($request);
                }
                $update=DB::table('admin_management.USER as AMUSER')->where('AMUSER.USER_ID', $AUTHUSERID)->update(['AMUSER.LAST_SEEN_AT' => $lastlogtime]);



                return $next($request);
            } elseif ($PANELTRACK == 'DISTRIBUTOR') {
                // Log::info("Module1ID1:".$AUTHUSERID);
                // GET USER INFO

                $auth_user = DB::table('distributor_management.USER AS DUSER')
                ->select('DUSER.LAST_SEEN_AT AS LAST_SEEN_AT')
                ->where('DUSER.USER_ID', $AUTHUSERID)
                ->first();

                $idleSession = DB::table('admin_management.LOGIN_IDLE_SESSION AS IDLE')
                ->select('IDLE.LOGIN_IDLE_SESSION_MIN AS LOGIN_IDLE_SESSION_MIN')
                ->orderBy('IDLE.LOGIN_IDLE_SESSION_ID', 'desc')
                ->first();

                // Log::info("idleSession :".$idleSession->LOGIN_IDLE_SESSION_MIN);

                $last_seen = Carbon::parse($auth_user->LAST_SEEN_AT);
                $absence = $last_seen->diffInMinutes($now, true);
                $sessionidletime = $idleSession->LOGIN_IDLE_SESSION_MIN ?? config('session.lifetime');
                if ($absence > $sessionidletime) {
                    $updatedata=DB::table('distributor_management.USER as DMUSER')->where('DMUSER.USER_ID', $AUTHUSERID)->update(['DMUSER.ISLOGIN' => 0]);

                    // Distributor User Log
                    $getLogIP1 = DB::table('admin_management.DISTRIBUTOR_USER_LOG AS DISTLOG')->where('DISTLOG.USER_ID', $AUTHUSERID)->where('DISTLOG.LOG_IP', $blockIp)->where('DISTLOG.STATUS', 1)->orderBy('DISTLOG.LOG_ID', 'desc')->first();
                    if($getLogIP1){
                        $updatelog1 = DB::table('admin_management.DISTRIBUTOR_USER_LOG as DISTLOG')
                                    ->where('DISTLOG.USER_ID', $AUTHUSERID)
                                    ->where('DISTLOG.LOG_IP', $blockIp)
                                    ->where('DISTLOG.STATUS', 1)
                                    ->update(['DISTLOG.LOGOUT_TIMESTAMP' => $lastlogtime,'DISTLOG.STATUS' =>0]);

                    }

                    session()->flush();
                    return $next($request);
                }
                $update=DB::table('distributor_management.USER as DMUSER')->where('DMUSER.USER_ID', $AUTHUSERID)->update(['DMUSER.LAST_SEEN_AT' => $lastlogtime]);
                return $next($request);
            } else if ($PANELTRACK == 'CONSULTANT') {
                //Log::info("Module1ID2:".$AUTHUSERID);
                // GET USER INFO


                $auth_user = DB::table('consultant_management.USER AS CUSER')
                    ->select('CUSER.LAST_SEEN_AT AS LAST_SEEN_AT')
                    ->where('CUSER.USER_ID', $AUTHUSERID)
                    ->first();

                $idleSession = DB::table('admin_management.LOGIN_IDLE_SESSION AS IDLE')
                    ->select('IDLE.LOGIN_IDLE_SESSION_MIN AS LOGIN_IDLE_SESSION_MIN')
                    ->orderBy('IDLE.LOGIN_IDLE_SESSION_ID', 'desc')
                    ->first();

                // Log::info("idleSession :".$idleSession->LOGIN_IDLE_SESSION_MIN);

                $last_seen = Carbon::parse($auth_user->LAST_SEEN_AT);
                $absence = $last_seen->diffInMinutes($now, true);
                $sessionidletime = $idleSession->LOGIN_IDLE_SESSION_MIN ?? config('session.lifetime');
                if ($absence > $sessionidletime) {
                    $updatedata=DB::table('consultant_management.USER as CMUSER')->where('CMUSER.USER_ID', $AUTHUSERID)->update(['CMUSER.ISLOGIN' => 0]);
                     // Consultant User Log
                     $getLogIP2 = DB::table('admin_management.CONSULTANT_USER_LOG AS CONLOG')->where('CONLOG.USER_ID', $AUTHUSERID)->where('CONLOG.LOG_IP', $blockIp)->where('CONLOG.STATUS', 1)->orderBy('CONLOG.LOG_ID', 'desc')->first();
                     if($getLogIP2){
                         $updatelog2 = DB::table('admin_management.CONSULTANT_USER_LOG as CONTLOG')
                                     ->where('CONTLOG.USER_ID', $AUTHUSERID)
                                     ->where('CONTLOG.LOG_IP', $blockIp)
                                     ->where('CONTLOG.STATUS', 1)
                                     ->update(['CONTLOG.LOGOUT_TIMESTAMP' => $lastlogtime,'CONTLOG.STATUS' =>0]);
 
                     }
                    session()->flush();
                    return $next($request);
                }
                $update=DB::table('consultant_management.USER as CMUSER')->where('CMUSER.USER_ID', $AUTHUSERID)->update(['CMUSER.LAST_SEEN_AT' => $lastlogtime]);
                return $next($request);
            } else if ($PANELTRACK == 'OTHERS') {
                //Log::info("Module1ID5:".$AUTHUSERID);
                // GET USER INFO
                //Log::info("Module1ID5:".$request);


                $auth_user = DB::table('funds_management.TP_USER AS TUSER')
                    ->select('TUSER.LAST_SEEN_AT AS LAST_SEEN_AT')
                    ->where('TUSER.TP_USER_ID', $AUTHUSERID)
                    ->first();

                $idleSession = DB::table('admin_management.LOGIN_IDLE_SESSION AS IDLE')
                    ->select('IDLE.LOGIN_IDLE_SESSION_MIN AS LOGIN_IDLE_SESSION_MIN')
                    ->orderBy('IDLE.LOGIN_IDLE_SESSION_ID', 'desc')
                    ->first();

                Log::info("Last Seen :".$auth_user->LAST_SEEN_AT);
                Log::info("Now :".$now);



                $last_seen = Carbon::parse($auth_user->LAST_SEEN_AT);
                $absence = $last_seen->diffInMinutes($now, true);
                Log::info("ABSENCE====".$absence);
                $sessionidletime = $idleSession->LOGIN_IDLE_SESSION_MIN ?? config('session.lifetime');
                if ($absence > $sessionidletime) {
                    Log::info("ABSENCE Inside====".$absence);
                    $updatedata=DB::table('funds_management.TP_USER as TMUSER')->where('TMUSER.TP_USER_ID', $AUTHUSERID)->update(['TMUSER.ISLOGIN' => 0]);

                    // Other User Log
                    $getLogIP3 = DB::table('admin_management.OTHERS_USER_LOG AS OTHERLOG')->where('OTHERLOG.USER_ID', $AUTHUSERID)->where('OTHERLOG.LOG_IP', $blockIp)->where('OTHERLOG.STATUS', 1)->orderBy('OTHERLOG.LOG_ID', 'desc')->first();
                    if($getLogIP3){
                        $updatelog3 = DB::table('admin_management.OTHERS_USER_LOG as OTHERUSERLOG')
                                    ->where('OTHERUSERLOG.USER_ID', $AUTHUSERID)
                                    ->where('OTHERUSERLOG.LOG_IP', $blockIp)
                                    ->where('OTHERUSERLOG.STATUS', 1)
                                    ->update(['OTHERUSERLOG.LOGOUT_TIMESTAMP' => $lastlogtime,'OTHERUSERLOG.STATUS' =>0]);

                    }
                    session()->flush();
                    return $next($request);
                }
                $update=DB::table('funds_management.TP_USER as TMUSER')->where('TMUSER.TP_USER_ID', $AUTHUSERID)->update(['TMUSER.LAST_SEEN_AT' => $lastlogtime]);
                return $next($request);
            } 
            else if ($PANELTRACK == 'ESC') {
                Log::info("Module1ID10:".$AUTHUSERID);
                // GET USER INFO
                //Log::info("Module1ID5:".$request);


                $auth_user = DB::table('exam_booking.ESC_USER AS EUSER')
                    ->select('EUSER.LAST_SEEN_AT AS LAST_SEEN_AT')
                    ->where('EUSER.ESC_USER_ID', $AUTHUSERID)
                    ->first();

                $idleSession = DB::table('admin_management.LOGIN_IDLE_SESSION AS IDLE')
                    ->select('IDLE.LOGIN_IDLE_SESSION_MIN AS LOGIN_IDLE_SESSION_MIN')
                    ->orderBy('IDLE.LOGIN_IDLE_SESSION_ID', 'desc')
                    ->first();

                Log::info("Last Seen :".$auth_user->LAST_SEEN_AT);
                Log::info("Now :".$now);



                $last_seen = Carbon::parse($auth_user->LAST_SEEN_AT);
                $absence = $last_seen->diffInMinutes($now, true);
                Log::info("ABSENCE====".$absence);
                $sessionidletime = $idleSession->LOGIN_IDLE_SESSION_MIN ?? config('session.lifetime');
                if ($absence > $sessionidletime) {
                   // Log::info("ABSENCE Inside====".$absence);
                    $updatedata=DB::table('exam_booking.ESC_USER as ESC_USER')->where('ESC_USER.ESC_USER_ID', $AUTHUSERID)->update(['ESC_USER.ISLOGIN' => 0]);

                    // Other User Log
                    $getLogIP3 = DB::table('admin_management.EXAM_BOOKING_USER_LOG AS EXAMUSERLOG')->where('EXAMUSERLOG.USER_ID', $AUTHUSERID)->where('EXAMUSERLOG.LOG_IP', $blockIp)->where('EXAMUSERLOG.STATUS', 1)->orderBy('EXAMUSERLOG.LOG_ID', 'desc')->first();
                    if($getLogIP3){
                        $updatelog3 = DB::table('admin_management.EXAM_BOOKING_USER_LOG as EUSERLOG')
                                    ->where('EUSERLOG.USER_ID', $AUTHUSERID)
                                    ->where('EUSERLOG.LOG_IP', $blockIp)
                                    ->where('EUSERLOG.STATUS', 1)
                                    ->update(['EUSERLOG.LOGOUT_TIMESTAMP' => $lastlogtime,'EUSERLOG.STATUS' =>0]);

                    }
                    session()->flush();
                    return $next($request);
                }
                $update=DB::table('exam_booking.ESC_USER as ESC_USER')->where('ESC_USER.ESC_USER_ID', $AUTHUSERID)->update(['ESC_USER.LAST_SEEN_AT' => $lastlogtime]);
                return $next($request);
            } 
            else {
                return $next($request);
            }
        } else {
            return $next($request);
        }
    }
}
