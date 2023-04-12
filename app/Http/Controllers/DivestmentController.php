<?php

namespace App\Http\Controllers;

use App\Helpers\ManageDistributorNotification;
use App\Helpers\ManageNotification;
use App\Models\DistributorStatus;
use App\Models\Divestment;
use App\Models\DivestmentApprover;
use App\Models\DivestmentDistApprover;
use App\Models\DivestmentConsTemp;
use App\Models\DivestmentConsultant;
use App\Models\DivestmentDocument;
use App\Models\DivestmentFund;
use App\Models\DivestmentFundTemp;
use DateTime;
use DB;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\DateFormatter;
use Validator;

class DivestmentController extends Controller
{

    //Advancement Filter divestment Record
    public function filter(Request $request)
    {
        $timestamp = bcdiv($request->DATE_DISPLAY, '1000');

        $date = new DateTime('@' . $timestamp);
        $new_date_format = Carbon::parse($date);
        $new_date_format = $new_date_format->addDays(1);

        $display_date = $new_date_format->format('Y-m-d');
        try {
            $query = DB::table('distributor_management.DIVESTMENT AS A')
                ->select('*', 'A.CREATE_TIMESTAMP AS DATE', 'A.CESSATION_DATE AS CESS_DATE', 'A.LEGAL_DATE AS LEG_DATE')
                ->leftJoin('distributor_management.DISTRIBUTOR AS B', 'B.DISTRIBUTOR_ID', '=', 'A.DIST_ID_MERGE')
                ->leftJoin('admin_management.DISTRIBUTOR_SETTING AS C', 'C.DISTRIBUTOR_SETTING_ID', '=', 'A.DIVE_TYPE')
                ->leftJoin('admin_management.TASK_STATUS AS D', 'D.TS_ID', '=', 'A.TS_ID')
                ->leftJoin('distributor_management.USER AS E', 'E.USER_ID', '=', 'A.LATEST_UPDATE_BY');


            if ($request->DATE_DISPLAY != "") {
                $query->where('A.CREATE_TIMESTAMP', 'like', '%' . $display_date . '%');
            }
            if ($request->DIST_SET_PARAM != "") {
                $query->where('C.DIST_SET_PARAM', 'like', '%' . $request->DIST_SET_PARAM . '%');
            }
            if ($request->DIST_NAME != "") {
                $query->where('B.DIST_NAME', 'like', '%' . $request->DIST_NAME . '%');
            }

            if ($request->TS_PARAM != "") {
                $query->where('D.TS_PARAM', 'like', '%' . $request->TS_PARAM . '%');
            }



            $data = $query->get();


            foreach ($data as $element) {
                if ($element->USER_NAME == null) {
                    $element->USER_NAME = $element->USER_NAME ?? '-';
                }
                $element->LATEST_UPDATE = date('d-M-Y', strtotime($element->LATEST_UPDATE));
                $element->DATE_DISPLAY = date('d-M-Y', strtotime($element->DATE));
                $element->CESS_DATE_DISPLAY = date('d-M-Y', strtotime($element->CESS_DATE));
                $element->LEG_DATE_DISPLAY = date('d-M-Y', strtotime($element->LEG_DATE));
            };
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

    //divestment RD advance filter
    public function DivestmentFimmListFilter(Request $request)
    {

        $timestamp = bcdiv($request->DATE_DISPLAY, '1000');

        $date = new DateTime('@' . $timestamp);
        $new_date_format = Carbon::parse($date);
        $new_date_format = $new_date_format->addDays(1);

        $display_date = $new_date_format->format('Y-m-d');
        try {
            $query = DB::table('distributor_management.DIVESTMENT_APPROVAL')
                ->select('*', 'A.CREATE_TIMESTAMP AS DATE', 'A.CESSATION_DATE', 'A.LEGAL_DATE', 'C.DIST_NAME AS DIST_DIVE_NAME', 'B.DIST_NAME AS DIST_MERGE_NAME')
                ->leftJoin('DIVESTMENT AS A', 'A.DIVE_ID', '=', 'DIVESTMENT_APPROVAL.DIVE_ID')
                ->leftJoin('distributor_management.DISTRIBUTOR AS B', 'B.DISTRIBUTOR_ID', '=', 'A.DIST_ID_MERGE')
                ->leftJoin('distributor_management.DISTRIBUTOR AS C', 'C.DISTRIBUTOR_ID', '=', 'A.DIST_ID')
                ->leftJoin('admin_management.DISTRIBUTOR_SETTING AS C', 'C.DISTRIBUTOR_SETTING_ID', '=', 'A.DIVE_TYPE')
                ->leftJoin('admin_management.TASK_STATUS AS D', 'D.TS_ID', '=', 'DIVESTMENT_APPROVAL.TS_ID')
                ->leftJoin('distributor_management.USER AS E', 'E.USER_ID', '=', 'A.LATEST_UPDATE_BY');



            if ($request->DATE_DISPLAY != "") {
                $query->where('A.CREATE_TIMESTAMP', 'like', '%' . $display_date . '%');
            }
            if ($request->DIST_SET_PARAM != "") {
                $query->where('C.DIST_SET_PARAM', 'like', '%' . $request->DIST_SET_PARAM . '%');
            }
            if ($request->DIST_DIVE_NAME != "") {
                $query->where('C.DIST_NAME', 'like', '%' . $request->DIST_DIVE_NAME . '%');
            }

            if ($request->TS_PARAM != "") {
                $query->where('D.TS_PARAM', 'like', '%' . $request->TS_PARAM . '%');
            }



            $data = $query->get();

            foreach ($data as $element) {
                $element->LATEST_UPDATE = date('d-M-Y', strtotime($element->LATEST_UPDATE));
                $element->DATE_DISPLAY = date('d-M-Y', strtotime($element->DATE));
                $element->CESS_DATE_DISPLAY = date('d-M-Y', strtotime($element->CESSATION_DATE));
                $element->LEG_DATE_DISPLAY = date('d-M-Y', strtotime($element->LEGAL_DATE));
            };

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
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
    //divestment RD advance filter
    public function DivestmentApprovalFilter(Request $request)
    {


        $timestamp = bcdiv($request->DATE_DISPLAY, '1000');

        $date = new DateTime('@' . $timestamp);
        $new_date_format = Carbon::parse($date);
        $new_date_format = $new_date_format->addDays(1);

        $display_date = $new_date_format->format('Y-m-d');
        try {


            $query = DB::table('distributor_management.DIVESTMENT_APPROVAL')
                ->select('*', 'A.CREATE_TIMESTAMP AS DATE', 'A.CESSATION_DATE', 'A.LEGAL_DATE')
                ->leftJoin('DIVESTMENT AS A', 'A.DIVE_ID', '=', 'DIVESTMENT_APPROVAL.DIVE_ID')
                ->leftJoin('distributor_management.DISTRIBUTOR AS B', 'B.DISTRIBUTOR_ID', '=', 'A.DIST_ID_MERGE')
                ->leftJoin('admin_management.DISTRIBUTOR_SETTING AS C', 'C.DISTRIBUTOR_SETTING_ID', '=', 'A.DIVE_TYPE')
                ->leftJoin('admin_management.TASK_STATUS AS D', 'D.TS_ID', '=', 'DIVESTMENT_APPROVAL.TS_ID')
                ->leftJoin('distributor_management.USER AS E', 'E.USER_ID', '=', 'A.LATEST_UPDATE_BY');

            if ($request->DATE_DISPLAY != "") {
                $query->where('A.CREATE_TIMESTAMP', 'like', '%' . $display_date . '%');
            }
            if ($request->DIST_SET_PARAM != "") {
                $query->where('C.DIST_SET_PARAM', 'like', '%' . $request->DIST_SET_PARAM . '%');
            }
            if ($request->DIST_DIVE_NAME != "") {
                $query->where('C.DIST_NAME', 'like', '%' . $request->DIST_DIVE_NAME . '%');
            }

            if ($request->TS_PARAM != "") {
                $query->where('D.TS_PARAM', 'like', '%' . $request->TS_PARAM . '%');
            }



            $data = $query->get();
            foreach ($data as $element) {
                $element->LATEST_UPDATE = date('d-M-Y', strtotime($element->LATEST_UPDATE));
                $element->DATE_DISPLAY = date('d-M-Y', strtotime($element->DATE));
                $element->CESS_DATE_DISPLAY = date('d-M-Y', strtotime($element->CESSATION_DATE));
                $element->LEG_DATE_DISPLAY = date('d-M-Y', strtotime($element->LEGAL_DATE));
            };

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
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


    // fund divestment distributor list
    public function getdistributorfund(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            $distributortype = DB::table('distributor_management.DISTRIBUTOR_TYPE AS A')
                ->select('A.DIST_TYPE AS DIST_TYPE', 'A.DIST_ID AS DISTRIBUTOR_ID')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->get();

            $query = "SELECT DIST.DISTRIBUTOR_ID,DIST.DIST_NAME,A.DIST_VALID_STATUS,TYPE.DIST_TYPE from distributor_management.DISTRIBUTOR as DIST
                    left join distributor_management.DISTRIBUTOR_STATUS as A on A.DIST_ID=DIST.DISTRIBUTOR_ID
                    left join distributor_management.DISTRIBUTOR_TYPE as TYPE on TYPE.DIST_ID=DIST.DISTRIBUTOR_ID
                    where A.DIST_VALID_STATUS=1

                    and case when TYPE.DIST_TYPE=1 then TYPE.DIST_TYPE=1 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
                    when TYPE.DIST_TYPE=2 then TYPE.DIST_TYPE=2 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
                    when TYPE.DIST_TYPE=1 and TYPE.DIST_TYPE=2 then TYPE.DIST_TYPE=1 and TYPE.DIST_TYPE=2 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID' END
                    ";


            $data = DB::select($query);

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

    // fund-consultant divestment distributor list
    public function getdistributorfundConsultant(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            $distributortype = DB::table('distributor_management.DISTRIBUTOR_TYPE AS A')
                ->select('A.DIST_TYPE AS DIST_TYPE', 'A.DIST_ID AS DISTRIBUTOR_ID')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->get();

            foreach ($distributortype as $item) {
                if ($item->DIST_TYPE == 1 or $item->DIST_TYPE == 2) {
                    $query = "SELECT DIST.DISTRIBUTOR_ID,DIST.DIST_NAME,A.DIST_VALID_STATUS,TYPE.DIST_TYPE from distributor_management.DISTRIBUTOR as DIST
                                left join distributor_management.DISTRIBUTOR_STATUS as A on A.DIST_ID=DIST.DISTRIBUTOR_ID
                                left join distributor_management.DISTRIBUTOR_TYPE as TYPE on TYPE.DIST_ID=DIST.DISTRIBUTOR_ID
                                where A.DIST_VALID_STATUS=1

                                and case when TYPE.DIST_TYPE=1 then TYPE.DIST_TYPE=1 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
                                when TYPE.DIST_TYPE=2 then TYPE.DIST_TYPE=2 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
                                when TYPE.DIST_TYPE=1 and TYPE.DIST_TYPE=2 then TYPE.DIST_TYPE=1 and TYPE.DIST_TYPE=2 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID' END
                                ";
                } else {
                    $query = "SELECT DIST.DISTRIBUTOR_ID,DIST.DIST_NAME,A.DIST_VALID_STATUS, GROUP_CONCAT(distinct TYPE.DIST_TYPE) as DIST_TYPE_NAME from distributor_management.DISTRIBUTOR as DIST
                                left join distributor_management.DISTRIBUTOR_STATUS as A on A.DIST_ID=DIST.DISTRIBUTOR_ID
                                left join distributor_management.DISTRIBUTOR_TYPE as TYPE on TYPE.DIST_ID=DIST.DISTRIBUTOR_ID
                                where A.DIST_VALID_STATUS=1
                                GROUP BY DIST.DISTRIBUTOR_ID, DIST.DIST_NAME


                                and case when TYPE.DIST_TYPE=3 then TYPE.DIST_TYPE=3 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
                                when TYPE.DIST_TYPE=6 then TYPE.DIST_TYPE=6 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
                                when TYPE.DIST_TYPE=4 then TYPE.DIST_TYPE=4 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
                                when TYPE.DIST_TYPE=5 then TYPE.DIST_TYPE=5 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'

                                END
                                ";
                }
            }


            $data = DB::select($query);

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


    // divestment fund Status
    public function Divestment_FundStatus(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            //   dd($user);

            $data = DB::table('distributor_management.DIVESTMENT AS A')
                ->select('*', 'A.TS_ID')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->whereraw('(DIVE_TYPE=35 or DIVE_TYPE=37 ) ')
                ->where('TS_ID', 1)
                ->get();

            //  dd($data);

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

    // clear fund selection
    public function Clear_Fund_Selection(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            //   dd($user);

            // $fundselection=DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
            // ->leftJoin('funds_management.FUND_PROFILE AS FP','FP.FUND_PROFILE_ID','=','A.FUND_PROFILE_ID')
            // ->where('A.DIST_ID',$user->USER_DIST_ID)
            // ->where('A.DIVE_SELECTED',1)
            // ->get();

            //
            $update = DivestmentFundTemp::where('DIST_ID', $user->USER_DIST_ID)
                ->where('DIVE_SELECTED', 1)
                ->get();

            foreach ($update as $item) {
                $item->DIVE_SELECTED = 0;
                $item->save();
            }

            http_response_code(200);
            return response([
                'message' => 'All data successfully update.',
                'data' => $update,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // divestment Consultant Status
    public function Divestment_ConsultantStatus(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            //   dd($user);

            $data = DB::table('distributor_management.DIVESTMENT AS A')
                ->select('*', 'A.TS_ID')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->whereraw('(DIVE_TYPE=36 or DIVE_TYPE=37 ) ')
                ->where('TS_ID', 1)
                ->get();

            //  dd($data);

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

    // Select All Fund
    public function SelectAllFund(Request $request)
    {
        try {

            $SelectAll = DivestmentFundTemp::where('DIST_ID', $request->USER_DIST_ID)
                ->get();

            foreach ($SelectAll as $item) {
                $item->DIVE_SELECTED = 1;
                $item->save();
            }

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // unselect All Fund
    public function UnselectAllFund(Request $request)
    {
        try {

            // dd($request->USER_DIST_ID);
            $UnselectAll = DivestmentFundTemp::where('DIST_ID', $request->USER_DIST_ID)
                ->get();

            // dd($UnselectAll);

            foreach ($UnselectAll as $item) {
                $item->DIVE_SELECTED = 0;
                $item->save();
            }

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }


    // fund list to divest
    public function getfund(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            //semak temp table
            $fundtemp = DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->get();

            //  dd(count($fundtemp) );
            $counttempt = count($fundtemp);

            //start
            if ($counttempt != 0) {
            } else {
                // dd("masukkkk");

                $distributortype = DB::table('distributor_management.DISTRIBUTOR_TYPE AS A')
                    ->select('A.DIST_TYPE AS DIST_TYPE', 'A.DIST_ID AS DISTRIBUTOR_ID')
                    ->where('A.DIST_ID', $user->USER_DIST_ID)
                    ->get();
                //  dd($distributortype);

                $fund = DB::table('funds_management.FUND_PROFILE AS A')
                    ->select('A.FUND_PROFILE_ID', 'A.FUND_NAME', 'A.FUND_CODE_FIMM', 'A.FUND_ASEAN_CIS');
                // ->where('A.DIST_ID',$user->USER_DIST_ID);

                foreach ($distributortype as $item) {
                    if ($item->DIST_TYPE == 1) {
                        $fund->orwhereraw("(A.FUND_ASEAN_CIS='UTF' and A.DIST_ID = $user->USER_DIST_ID )");
                    } else
                    if ($item->DIST_TYPE == 2) {
                        $fund->orwhereraw("(A.FUND_ASEAN_CIS='PRS' and A.DIST_ID = $user->USER_DIST_ID )");
                    } else {
                        // dist type 0
                        $fund->orwhereraw("(A.FUND_ASEAN_CIS='0' and A.DIST_ID = $user->USER_DIST_ID )");
                    }
                }
                $data = $fund->get();

                // masukkan dalam temp table

                $totalcount = count($data);

                if ($data != null) {

                    for ($x = 0; $x < $totalcount; $x++) {
                        $inserttemp = new DivestmentFundTemp;
                        $inserttemp->DIST_ID = $user->USER_DIST_ID;
                        $inserttemp->FUND_PROFILE_ID = $data[$x]->FUND_PROFILE_ID;
                        $inserttemp->save();
                    }

                    // dd("masukkkk");

                }
            } //end

            $fundtemplist = DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
                ->leftJoin('funds_management.FUND_PROFILE AS FP', 'FP.FUND_PROFILE_ID', '=', 'A.FUND_PROFILE_ID')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->where('A.DIVE_SELECTED', 0)
                ->get();

            // dd($fundtemplist);

            // $fundtemplist = DB::table('funds_management.FUND_PROFILE AS A')
            // // ->leftJoin('funds_management.FUND_PROFILE AS FP', 'FP.FUND_PROFILE_ID', '=', 'A.FUND_PROFILE_ID')
            // ->where('A.DIST_ID', $user->USER_DIST_ID)
            // // ->where('A.DIVE_SELECTED', 0)
            // ->get();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $fundtemplist,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    //  display list selected
    public function getfundSelected(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            // 100

            $fundselection = DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
                ->leftJoin('funds_management.FUND_PROFILE AS FP', 'FP.FUND_PROFILE_ID', '=', 'A.FUND_PROFILE_ID')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->where('A.DIVE_SELECTED', 1)
                ->get();



            // dd($fundtemplist);

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $fundselection,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function convertFundToFundTemp(Request $request)
    {
        try {

            $divestmentFunds = DB::table('distributor_management.DIVESTMENT_FUND AS A')
                ->where('A.DIVE_ID', $request->DIVE_ID)
                ->get();


            foreach ($divestmentFunds as $item) {
                $divestment_fund_temp = new DivestmentFundTemp;
                $divestment_fund_temp->DIST_ID = $request->DIST_ID;
                $divestment_fund_temp->FUND_PROFILE_ID = $item->FUND_PROFILE_ID;
                $divestment_fund_temp->DIVE_SELECTED = 1;
                $divestment_fund_temp->save();

                DivestmentFund::findOrFail($item->DIVE_FUND_ID)->delete();
            }



            // dd($fundtemplist);

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $divestmentFunds,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    public function convertConsultantToConsultantTemp(Request $request)
    {
        try {

            $divestmentConsultants = DB::table('distributor_management.DIVESTMENT_CONSULTANT AS A')
                ->where('A.DIVE_ID', $request->DIVE_ID)
                ->get();


            if (count($divestmentConsultants) > 0) {
                foreach ($divestmentConsultants as $item) {
                    $divestment_cons_temp = new DivestmentConsTemp;
                    $divestment_cons_temp->DIST_ID = $request->DIST_ID;
                    $divestment_cons_temp->CONS_ID = $item->CONS_ID;
                    $divestment_cons_temp->DIVE_SELECTED = 1;
                    $divestment_cons_temp->save();

                    DivestmentConsultant::findOrFail($item->DIVE_CONS_ID)->delete();
                }
            }



            // dd($fundtemplist);

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $divestmentConsultants,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    //display submission fund
    public function Submission_fund_list(Request $request)
    {
        try {

            $fundselection = DB::table('distributor_management.DIVESTMENT_FUND AS A')
                ->leftJoin('funds_management.FUND_PROFILE AS FP', 'FP.FUND_PROFILE_ID', '=', 'A.FUND_PROFILE_ID')
                ->where('A.DIVE_ID', $request->DIVE_ID)
                ->get();

            // dd($fundtemplist);

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $fundselection,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // update selection
    public function fund_add(Request $request)
    {
        try {

            $update = DivestmentFundTemp::where('FUND_PROFILE_ID', $request->FUND_PROFILE_ID)
                ->where('DIST_ID', $request->DIST_ID)
                ->first();
            // dd($update);
            $update->DIVE_SELECTED = 1;
            $update->save();

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // update selection
    public function fund_remove(Request $request)
    {
        try {

            $update = DivestmentFundTemp::where('FUND_PROFILE_ID', $request->FUND_PROFILE_ID)
                ->where('DIST_ID', $request->DIST_ID)

                ->first();
            // dd($update);
            $update->DIVE_SELECTED = 0;
            $update->save();

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // consultant divestment distributor list
    public function getdistributorconsultant(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            $distributortype = DB::table('distributor_management.DISTRIBUTOR_TYPE AS A')
                ->select('A.DIST_TYPE AS DIST_TYPE', 'A.DIST_ID AS DISTRIBUTOR_ID')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->get();

            // dd($distributortype);

            $query = "SELECT DIST.DISTRIBUTOR_ID,DIST.DIST_NAME,A.DIST_VALID_STATUS, GROUP_CONCAT(distinct TYPE.DIST_TYPE) as DIST_TYPE_NAME from distributor_management.DISTRIBUTOR as DIST
            left join distributor_management.DISTRIBUTOR_STATUS as A on A.DIST_ID=DIST.DISTRIBUTOR_ID
            left join distributor_management.DISTRIBUTOR_TYPE as TYPE on TYPE.DIST_ID=DIST.DISTRIBUTOR_ID
            where A.DIST_VALID_STATUS=1
            GROUP BY DIST.DISTRIBUTOR_ID, DIST.DIST_NAME


            and case when TYPE.DIST_TYPE=1 or TYPE.DIST_TYPE=3 then (TYPE.DIST_TYPE=1 or TYPE.DIST_TYPE=3) and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
            when TYPE.DIST_TYPE=2 or TYPE.DIST_TYPE=6 then (TYPE.DIST_TYPE=2 or TYPE.DIST_TYPE=6) and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
            when TYPE.DIST_TYPE=4 then TYPE.DIST_TYPE=4 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'
            when TYPE.DIST_TYPE=5 then TYPE.DIST_TYPE=5 and DIST.DISTRIBUTOR_ID <> '$user->USER_DIST_ID'

            END
            ";


            $data = DB::select($query);



            // $distributor = DB::table('distributor_management.DISTRIBUTOR AS SET')
            //     ->select('SET.DISTRIBUTOR_ID AS DISTRIBUTOR_ID', 'SET.DIST_NAME AS DIST_NAME')
            //     ->selectRaw("group_concat(distinct TYPE.DIST_TYPE SEPARATOR ',') as DIST_TYPE_NAME")
            //     ->leftJoin('distributor_management.DISTRIBUTOR_TYPE AS TYPE', 'TYPE.DIST_ID', '=', 'SET.DISTRIBUTOR_ID');
            // // ->where('TYPE.DIST_TYPE','!=',null);
            // // ->get();

            // foreach ($distributortype as $item) {
            //     if ($item->DIST_TYPE == 1 || $item->DIST_TYPE == 3) {
            //         $distributor->orwhereraw("((TYPE.DIST_TYPE =1  or TYPE.DIST_TYPE = 3) and SET.DISTRIBUTOR_ID != $user->USER_DIST_ID)");
            //     } else
            //     if ($item->DIST_TYPE == 2 || $item->DIST_TYPE == 6) {
            //         $distributor->orwhereraw("((TYPE.DIST_TYPE =2  or TYPE.DIST_TYPE = 6) and SET.DISTRIBUTOR_ID != $user->USER_DIST_ID)");
            //     } else
            //     if ($item->DIST_TYPE == 4) {
            //         $distributor->orwhere('TYPE.DIST_TYPE', '=', '4');
            //         $distributor->where('SET.DISTRIBUTOR_ID', '!=', $user->USER_DIST_ID);
            //     } else
            //     if ($item->DIST_TYPE == 5) {
            //         $distributor->orwhere('TYPE.DIST_TYPE', '=', '5');
            //         $distributor->where('SET.DISTRIBUTOR_ID', '!=', $user->USER_DIST_ID);
            //     }
            // }

            // $distributor->groupby('DISTRIBUTOR_ID', 'DIST_NAME');
            // $data = $distributor->get();

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

    // consultant list to divest
    public function getconsultant(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            //semak temp table
            $constemp = DB::table('distributor_management.DIVESTMENT_CONS_TEMP AS A')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->get();

            //  dd(count($constemp) );
            $counttempt = count($constemp);

            //start
            if ($counttempt != 0) {
            } else {

                $consultantlist = DB::table('consultant_management.CONSULTANT_LICENSE AS A')
                    ->select('B.CONSULTANT_NAME', 'B.CONSULTANT_NRIC', 'B.CONSULTANT_PASSPORT_NO', 'C.TYPE_NAME', 'B.CONSULTANT_ID')
                    ->leftJoin('consultant_management.CONSULTANT AS B', 'B.CONSULTANT_ID', '=', 'A.CONSULTANT_ID')
                    ->leftJoin('admin_management.CONSULTANT_TYPE AS C', 'C.CONSULTANT_TYPE_ID', '=', 'A.CONSULTANT_TYPE_ID')
                    ->where('A.DISTRIBUTOR_ID', $user->USER_DIST_ID)
                    ->where('A.TS_ID', '=', '3')
                    ->get();

                $totalcount = count($consultantlist);

                // masuk save dalam temp
                if ($consultantlist != null) {

                    for ($x = 0; $x < $totalcount; $x++) {
                        $inserttemp = new DivestmentConsTemp;
                        $inserttemp->DIST_ID = $user->USER_DIST_ID;
                        $inserttemp->CONS_ID = $consultantlist[$x]->CONSULTANT_ID;
                        $inserttemp->save();
                    }
                }
            } //end

            // DB::enableQueryLog();

            $constemplist = DB::table('distributor_management.DIVESTMENT_CONS_TEMP AS CONSTEMP')
                ->select('*')
                ->leftJoin('consultant_management.CONSULTANT_LICENSE as CL', 'CL.CONSULTANT_ID', '=', 'CONSTEMP.CONS_ID')
                ->leftJoin('consultant_management.CONSULTANT AS B', 'B.CONSULTANT_ID', '=', 'CL.CONSULTANT_ID')
                ->leftJoin('admin_management.CONSULTANT_TYPE AS C', 'C.CONSULTANT_TYPE_ID', '=', 'CL.CONSULTANT_TYPE_ID')
                ->where('CONSTEMP.DIST_ID', $user->USER_DIST_ID)
                ->where('CONSTEMP.DIVE_SELECTED', 0)
                ->get();
            // dd(DB::getQueryLog());
            // dd($constemplist);

            foreach ($constemplist as $item) {
                if ($item->CONSULTANT_PASSPORT_NO != null) {
                } else {
                    $item->CONSULTANT_PASSPORT_NO = $item->CONSULTANT_PASSPORT_NO ?? '-';
                }
                if ($item->CONSULTANT_NRIC != null) {
                    $item->CONSULTANT_NRIC = substr($item->CONSULTANT_NRIC, 0, 6) . '-' . substr($item->CONSULTANT_NRIC, 6, 2) . '-' . substr($item->CONSULTANT_NRIC, 8, 4);
                } else {
                    $item->CONSULTANT_NRIC = $item->CONSULTANT_NRIC ?? '-';
                }
            }

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $constemplist,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }


    //  display list selected (consultant)
    public function getconsSelected(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            $constemplist = DB::table('distributor_management.DIVESTMENT_CONS_TEMP AS CONSTEMP')
                ->select('*')
                ->leftJoin('consultant_management.CONSULTANT_LICENSE as CL', 'CL.CONSULTANT_ID', '=', 'CONSTEMP.CONS_ID')
                ->leftJoin('consultant_management.CONSULTANT AS B', 'B.CONSULTANT_ID', '=', 'CL.CONSULTANT_ID')
                ->leftJoin('admin_management.CONSULTANT_TYPE AS C', 'C.CONSULTANT_TYPE_ID', '=', 'CL.CONSULTANT_TYPE_ID')
                ->where('CONSTEMP.DIST_ID', $user->USER_DIST_ID)
                ->where('CONSTEMP.DIVE_SELECTED', 1)
                ->get();

            foreach ($constemplist as $item) {
                if ($item->CONSULTANT_PASSPORT_NO != null) {
                } else {
                    $item->CONSULTANT_PASSPORT_NO = $item->CONSULTANT_PASSPORT_NO ?? '-';
                }
                if ($item->CONSULTANT_NRIC != null) {
                    $item->CONSULTANT_NRIC = substr($item->CONSULTANT_NRIC, 0, 6) . '-' . substr($item->CONSULTANT_NRIC, 6, 2) . '-' . substr($item->CONSULTANT_NRIC, 8, 4);
                } else {
                    $item->CONSULTANT_NRIC = $item->CONSULTANT_NRIC ?? '-';
                }
            }

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $constemplist,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // clear consultant selection
    public function Clear_Cons_Selection(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            //
            $consclearlist = DivestmentConsTemp::where('DIST_ID', $user->USER_DIST_ID)
                ->where('DIVE_SELECTED', 1)
                ->get();

            // dd($update);

            foreach ($consclearlist as $item) {
                $item->DIVE_SELECTED = 0;
                $item->save();
            }

            http_response_code(200);
            return response([
                'message' => 'All data successfully update.',
                'data' => $consclearlist,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // update consultant selection
    public function Cons_add(Request $request)
    {
        try {

            $update = DivestmentConsTemp::where('DIVE_CONS_TEMP_ID', $request->DIVE_CONS_TEMP_ID)
                ->where('DIST_ID', $request->DIST_ID)

                ->first();
            // dd($update);
            $update->DIVE_SELECTED = 1;
            $update->save();

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // update consultant selection
    public function Cons_remove(Request $request)
    {
        try {

            $update = DivestmentConsTemp::where('DIVE_CONS_TEMP_ID', $request->DIVE_CONS_TEMP_ID)
                ->where('DIST_ID', $request->DIST_ID)

                ->first();
            // dd($update);
            $update->DIVE_SELECTED = 0;
            $update->save();

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // Select All Cons
    public function SelectAllCons(Request $request)
    {
        try {

            $SelectAll = DivestmentConsTemp::where('DIST_ID', $request->USER_DIST_ID)
                ->get();

            foreach ($SelectAll as $item) {
                $item->DIVE_SELECTED = 1;
                $item->save();
            }

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // unselect All Cons
    public function UnselectAllCons(Request $request)
    {
        try {

            // dd($request->USER_DIST_ID);
            $UnselectAll = DivestmentConsTemp::where('DIST_ID', $request->USER_DIST_ID)
                ->get();

            // dd($UnselectAll);

            foreach ($UnselectAll as $item) {
                $item->DIVE_SELECTED = 0;
                $item->save();
            }

            http_response_code(200);
            return response([
                'message' => 'data update.',

            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    //display submission consultant
    public function Submission_cons_list(Request $request)
    {
        try {

            $consselection = DB::table('distributor_management.DIVESTMENT_CONSULTANT AS A')
                ->select('*')
                ->leftJoin('consultant_management.CONSULTANT_LICENSE as CL', 'CL.CONSULTANT_ID', '=', 'A.CONS_ID')
                ->leftJoin('consultant_management.CONSULTANT AS B', 'B.CONSULTANT_ID', '=', 'CL.CONSULTANT_ID')
                ->leftJoin('admin_management.CONSULTANT_TYPE AS C', 'C.CONSULTANT_TYPE_ID', '=', 'CL.CONSULTANT_TYPE_ID')
                ->where('A.DIVE_ID', $request->DIVE_ID)
                ->get();

            // dd($fundtemplist);

            foreach ($consselection as $item) {
                if ($item->CONSULTANT_PASSPORT_NO != null) {
                } else {
                    $item->CONSULTANT_PASSPORT_NO = $item->CONSULTANT_PASSPORT_NO ?? '-';
                }
                if ($item->CONSULTANT_NRIC != null) {
                    $item->CONSULTANT_NRIC = substr($item->CONSULTANT_NRIC, 0, 6) . '-' . substr($item->CONSULTANT_NRIC, 6, 2) . '-' . substr($item->CONSULTANT_NRIC, 8, 4);
                } else {
                    $item->CONSULTANT_NRIC = $item->CONSULTANT_NRIC ?? '-';
                }
            }

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $consselection,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // fund selection
    public function createfund_selection(Request $request)
    {
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106,
            ], 400);
        }

        try {

            $divestment = new Divestment;
            $divestment->DIST_ID = $request->DISTRIBUTOR_ID_DIVEST;
            $divestment->DIST_USER_ID = $request->USER_ID;
            $divestment->TS_ID = $request->TS_ID;
            $divestment->DIST_ID_MERGE = $request->DISTRIBUTOR_ID_TO;
            $divestment->DIVE_TYPE = $request->DIVE_TYPE;
            $divestment->save();

            $fundtemp = DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
                ->where('A.DIST_ID', $request->DISTRIBUTOR_ID_DIVEST)
                ->where('DIVE_SELECTED', 1)
                ->get();

            //  dd($fundtemp);

            $totalfundcount = count($fundtemp);

            if ($request->DIVE_TYPE == 35) {

                // for ($x = 0; $x < $totalfundcount; $x++) {
                //     $fund = new DivestmentFund;
                //     $fund->DIVE_ID = $divestment->DIVE_ID;
                //     $fund->FUND_PROFILE_ID = $fundtemp[$x]->FUND_PROFILE_ID;
                //     $fund->save();
                // }

            } else if ($request->DIVE_TYPE == 36) {

                // $consultant= new DivestmentFund;
                // $consultant->DIVE_ID = $divestment->DIVE_ID;
                // // pending save consultant (for each selection)
                // $consultant->save();

            }
            if ($request->DIVE_TYPE == 37) {

                // for ($x = 0; $x < $totalfundcount; $x++) {
                //     $fund = new DivestmentFund;
                //     $fund->DIVE_ID = $divestment->DIVE_ID;
                //     $fund->FUND_PROFILE_ID = $fundtemp[$x]->FUND_PROFILE_ID;
                //     $fund->save();
                // }

                // $consultant= new DivestmentFund;
                // $consultant->DIVE_ID = $divestment->DIVE_ID;
                // // pending save consultant (for each selection)
                // $consultant->save();

            }

            http_response_code(200);
            return response([
                'message' => 'Data successfully updated.',
                'DIVE_ID' => $divestment->DIVE_ID,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4100,
            ], 400);
        }
    }

    // submission
    public function update_submission(Request $request)

    {
        $validator = Validator::make($request->all(), []);
        if ($validator->fails()) {
            http_response_code(400);
            return response([
                'message' => 'Data validation error.',
                'errorCode' => 4106,
            ], 400);
        }
        try {

            //for returned status to new submission
            // $divest_dist_appr=DivestmentDistApprover::where(["DIVE_ID"=>$request->DIVE_ID,"TS_ID"=>7])->first();
            // $divest_dist_appr->TS_ID=$request->TS_ID;
            // $divest_dist_appr->save();

            //remove all second level document if the status comes with 2
            if ($request->SECOND_LEVEL != null && $request->SECOND_LEVEL == 2) {
                // dd('here1');
                DivestmentDocument::where(['DIVE_ID' => $request->DIVE_ID, 'FILE_NO' => $request->FILE_NO_SECONDLEVEL])->delete();
            }

            $update = Divestment::where('DIVE_ID', $request->DIVE_ID)
                ->first();
            // dd($update);
            $update->TS_ID = $request->TS_ID;
            if ($request->CESSATION_DATE != null) {
                $update->CESSATION_DATE = $request->CESSATION_DATE;
            }
            if ($request->LEGAL_DATE != null) {
                $update->LEGAL_DATE = $request->LEGAL_DATE;
            }
            if ($request->CEASE_STATUS != null) {
                $update->CEASE_STATUS = $request->CEASE_STATUS;
            }
            if ($request->SECOND_LEVEL != null) {
                $update->SECOND_LEVEL = $request->SECOND_LEVEL;
            }
            if ($request->DISTRIBUTOR_ID_TO != null) {
                $update->DIST_ID_MERGE = $request->DISTRIBUTOR_ID_TO;
            }
            $update->save();



            // dd($request->fileOne);

            if ($request->PUBLISH_STATUS == "0") {

                $countApprover = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', 1)->get();


                if (count($countApprover) == 0) {
                    $dataApproval = new DivestmentDistApprover;
                    $dataApproval->DIVE_ID = $request->DIVE_ID;
                    $dataApproval->APPR_GROUP_ID = 1;
                    $dataApproval->APPROVAL_LEVEL_ID = 13;
                    $dataApproval->TS_ID = 15;
                    $dataApproval->save();
                }
            }

            if ($request->PUBLISH_STATUS == "1") {

                $divestDistApprover = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', 1)
                    ->where('APPROVAL_LEVEL_ID', 13)->where('TS_ID', 15)->get();

                if (count($divestDistApprover) > 0) {
                    foreach ($divestDistApprover as $item) {
                        $item->delete();
                    }
                }


                //first entry
                foreach (json_decode($request->ENTRY_LIST) as $item) {
                    $countApprover = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->get();

                    $getCount = count($countApprover);

                    if ($getCount == 0) {

                        $dataEntry = new DivestmentDistApprover;
                        $dataEntry->DIVE_ID = $request->DIVE_ID;
                        $dataEntry->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                        $dataEntry->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                        $dataEntry->TS_ID = 2;
                        $dataEntry->CREATE_BY = $request->CREATE_BY;
                        $dataEntry->APPR_PUBLISH_STATUS = $request->PUBLISH_STATUS;
                        $dataEntry->save();
                    } else {
                    }
                }
                foreach (json_decode($request->APPR_LIST) as $item) {
                    $countApprover = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->get();

                    $getCount = count($countApprover);

                    if ($getCount == 0) {

                        $dataApproval = new DivestmentDistApprover;
                        $dataApproval->DIVE_ID = $request->DIVE_ID;
                        $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                        $dataApproval->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                        $dataApproval->TS_ID = $request->TS_ID;
                        $dataApproval->save();
                    } else {

                        //resubmit
                        $Approverupdate = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)
                            ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)
                            ->get();

                        foreach ($Approverupdate as $resubmit) {
                            $resubmit->TS_ID = 15;
                            $resubmit->APPR_PUBLISH_STATUS = 0;
                            $resubmit->save();
                        }
                    }

                    $notification = new ManageDistributorNotification();
                    $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $update->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                }

                //fund
                $fundtempselect = DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
                    ->where('A.DIST_ID', $update->DIST_ID)
                    ->where('DIVE_SELECTED', 1)
                    ->get();
                $totalfundselectcount = count($fundtempselect);

                // dd($totalfundselectcount);

                //consultant
                $constempselect = DB::table('distributor_management.DIVESTMENT_CONS_TEMP AS A')
                    ->where('A.DIST_ID', $update->DIST_ID)
                    ->where('DIVE_SELECTED', 1)
                    ->get();

                // dd($constempselect);
                $totalConsSelectCount = count($constempselect);

                if ($update->DIVE_TYPE == 35) {

                    for ($x = 0; $x < $totalfundselectcount; $x++) {
                        $fund = new DivestmentFund;
                        $fund->DIVE_ID = $request->DIVE_ID;
                        $fund->FUND_PROFILE_ID = $fundtempselect[$x]->FUND_PROFILE_ID;
                        $fund->save();
                    }

                    $fundtempremove = DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
                        ->where('A.DIST_ID', $update->DIST_ID)
                        ->where('DIVE_SELECTED', 1)
                        ->delete();
                } else if ($update->DIVE_TYPE == 36) {

                    // dd($totalConsSelectCount);
                    for ($x = 0; $x < $totalConsSelectCount; $x++) {
                        $Consultant = new DivestmentConsultant;
                        $Consultant->DIVE_ID = $request->DIVE_ID;
                        $Consultant->CONS_ID = $constempselect[$x]->CONS_ID;
                        $Consultant->save();
                    }

                    $constempremove = DB::table('distributor_management.DIVESTMENT_CONS_TEMP AS A')
                        ->where('A.DIST_ID', $update->DIST_ID)
                        ->where('DIVE_SELECTED', 1)
                        ->delete();
                } else if ($update->DIVE_TYPE == 37) {

                    for ($x = 0; $x < $totalfundselectcount; $x++) {
                        $fund = new DivestmentFund;
                        $fund->DIVE_ID = $request->DIVE_ID;
                        $fund->FUND_PROFILE_ID = $fundtempselect[$x]->FUND_PROFILE_ID;
                        $fund->save();
                    }

                    for ($x = 0; $x < $totalConsSelectCount; $x++) {
                        $Consultant = new DivestmentConsultant;
                        $Consultant->DIVE_ID = $request->DIVE_ID;
                        $Consultant->CONS_ID = $constempselect[$x]->CONS_ID;
                        $Consultant->save();
                    }

                    $fundtempremove = DB::table('distributor_management.DIVESTMENT_FUND_TEMP AS A')
                        ->where('A.DIST_ID', $update->DIST_ID)
                        ->where('DIVE_SELECTED', 1)
                        ->delete();

                    $constempremove = DB::table('distributor_management.DIVESTMENT_CONS_TEMP AS A')
                        ->where('A.DIST_ID', $update->DIST_ID)
                        ->where('DIVE_SELECTED', 1)
                        ->delete();
                }
            }

            //file (1) upload
            $fileOne = $request->fileOne;
            if ($fileOne != null && $request->hasFile('fileOne')) {
                foreach ($fileOne as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO_ONE;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }

            // dd($request->fileTwo);
            //file (2) upload
            $fileTwo = $request->fileTwo;
            if ($fileTwo != null && $request->hasFile('fileTwo')) {
                foreach ($fileTwo as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO_TWO;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }
            //file (3) upload
            $fileThree = $request->fileThree;
            if ($fileThree != null &&  $request->hasFile('fileThree')) {
                foreach ($fileThree as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO_THREE;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }
            //file (4) upload
            $fileFour = $request->fileFour;
            if ($fileFour != null  && $request->hasFile('fileFour')) {
                foreach ($fileFour as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO_FOUR;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }

            //file (5) upload
            $fileSecondLevel = $request->fileSecondLevel;
            if ($fileSecondLevel != null && $request->hasFile('fileSecondLevel')) {
                foreach ($fileSecondLevel as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO_SECONDLEVEL;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }

            http_response_code(200);
            return response([
                'message' => 'Data successfully updated.',
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4100,
            ], 400);
        }
    }

    //discard fund
    public function Discard(Request $request)
    {
        try {

            $fund = Divestment::where(['DIVE_ID' => $request->DIVE_ID])->first();
            if ($fund) {
                $fund->delete();
            }

            $doc = DivestmentDocument::where('DIVE_ID', $request->DIVE_ID)->first();
            if ($doc) {
                $doc->delete();
            }

            $approval = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)->get();
            if (count($approval) > 0) {
                foreach ($approval as $item) {
                    $item->delete();
                }
            }

            http_response_code(200);
            return response([
                'message' => 'Data successfully removed.',
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Data failed to be updated.',
                'errorCode' => 4100,
            ], 400);
        }
    }

    public function getDivestmentApproval(Request $request)
    {
        try {

            $distributor = DB::table('distributor_management.DIVESTMENT_DIST_APPROVAL AS A')
                ->select('*')
                ->where('A.DIVE_ID', $request->DIVE_ID)
                ->where('A.TS_ID', $request->TS_ID)
                ->first();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $distributor,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // distributor details
    public function distributordetails(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            $distributor = DB::table('distributor_management.DISTRIBUTOR AS A')
                ->select(
                    '*',
                    'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME',
                    'COUNTRYNAME.SET_CODE AS SET_CODE',
                    'STATENAME.SET_PARAM AS STATE_NAME',
                    'CITYNAME.SET_CITY_NAME',
                    'POSTAL.POSTCODE_NO',
                    'DA.DIST_STATE2',
                    'DA.DIST_CITY2',
                    'DA.DIST_POSTAL2'
                )
                ->leftJoin('distributor_management.DISTRIBUTOR_ADDRESS AS DA', 'DA.DIST_ID', '=', 'A.DISTRIBUTOR_ID')
                ->leftJoin('admin_management.SETTING_GENERAL AS COUNTRYNAME', 'COUNTRYNAME.SETTING_GENERAL_ID', '=', 'DA.DIST_COUNTRY')
                ->leftJoin('admin_management.SETTING_GENERAL AS STATENAME', 'STATENAME.SETTING_GENERAL_ID', '=', 'DA.DIST_STATE')
                ->leftJoin('admin_management.SETTING_CITY AS CITYNAME', 'CITYNAME.SETTING_CITY_ID', '=', 'DA.DIST_CITY')
                ->leftJoin('admin_management.SETTING_POSTAL AS POSTAL', 'POSTAL.SETTING_POSTCODE_ID', '=', 'DA.DIST_POSTAL')
                ->where('A.DISTRIBUTOR_ID', $user->USER_DIST_ID)
                ->first();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $distributor,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // distributor details
    public function distributordetailsfimm(Request $request)
    {
        try {

            $distributor = DB::table('distributor_management.DISTRIBUTOR AS A')
                ->select(
                    '*',
                    'COUNTRYNAME.SET_PARAM AS COUNTRY_NAME',
                    'COUNTRYNAME.SET_CODE AS SET_CODE',
                    'STATENAME.SET_PARAM AS STATE_NAME',
                    'CITYNAME.SET_CITY_NAME',
                    'POSTAL.POSTCODE_NO'
                )
                ->leftJoin('distributor_management.DISTRIBUTOR_ADDRESS AS DA', 'DA.DIST_ID', '=', 'A.DISTRIBUTOR_ID')
                ->leftJoin('admin_management.SETTING_GENERAL AS COUNTRYNAME', 'COUNTRYNAME.SETTING_GENERAL_ID', '=', 'DA.DIST_COUNTRY')
                ->leftJoin('admin_management.SETTING_GENERAL AS STATENAME', 'STATENAME.SETTING_GENERAL_ID', '=', 'DA.DIST_STATE')
                ->leftJoin('admin_management.SETTING_CITY AS CITYNAME', 'CITYNAME.SETTING_CITY_ID', '=', 'DA.DIST_CITY')
                ->leftJoin('admin_management.SETTING_POSTAL AS POSTAL', 'POSTAL.SETTING_POSTCODE_ID', '=', 'DA.DIST_POSTAL')
                ->where('A.DISTRIBUTOR_ID', $request->DIST_ID)
                ->first();

            http_response_code(200);
            return response([
                'message' => 'All data successfully retrieved.',
                'data' => $distributor,
            ]);
        } catch (RequestException $r) {

            http_response_code(400);
            return response([
                'message' => 'Failed to retrieve all data.',
                'errorCode' => 4103,
            ], 400);
        }
    }

    // distributor to merge details
    public function distributorMerge(Request $request)
    {
        try {

            $data = DB::table('distributor_management.DIVESTMENT AS A')
                ->select('*', 'A.CESSATION_DATE AS CESS_DATE', 'A.LEGAL_DATE AS LEG_DATE')
                ->leftJoin('distributor_management.DISTRIBUTOR AS B', 'B.DISTRIBUTOR_ID', '=', 'A.DIST_ID_MERGE')
                ->where('A.DIVE_ID', $request->DIVE_ID)
                ->first();


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

    // divestment list
    public function DivestmentList(Request $request)
    {
        try {

            //  $user = DB::table('distributor_management.USER AS A')
            //  ->select ('A.USER_DIST_ID')
            //  ->where('A.USER_ID',$request->USER_ID)
            //  ->first();

            $data = DB::table('distributor_management.DIVESTMENT AS A')
                ->select('*', 'A.CREATE_TIMESTAMP AS DATE', 'A.TS_ID', 'A.CESSATION_DATE AS CESS_DATE', 'A.LEGAL_DATE AS LEG_DATE', 'A.LATEST_UPDATE AS LATEST_UPDATE', 'D.TS_PARAM', 'F.USER_NAME AS ACTION_BY_FIMM', 'E.USER_NAME', 'G.TS_PARAM as FIMM_TS_PARAM')
                ->leftJoin('distributor_management.DISTRIBUTOR AS B', 'B.DISTRIBUTOR_ID', '=', 'A.DIST_ID_MERGE')
                ->leftJoin('admin_management.DISTRIBUTOR_SETTING AS C', 'C.DISTRIBUTOR_SETTING_ID', '=', 'A.DIVE_TYPE')
                ->leftJoin('admin_management.TASK_STATUS AS D', 'D.TS_ID', '=', 'A.TS_ID')
                ->leftJoin('distributor_management.USER AS E', 'E.USER_ID', '=', 'A.LATEST_UPDATE_BY')
                ->leftJoin('admin_management.USER AS F', 'F.USER_ID', '=', 'A.FIMM_LATEST_UPDATE')
                ->leftJoin('admin_management.TASK_STATUS AS G', 'G.TS_ID', '=', 'A.FIMM_TS_ID')
                // ->leftJoin('distributor_management.DIVESTMENT_APPROVAL as G','G.DIVE_ID','=','A.DIVE_ID','G.TS_ID','=',9,'G.TS_ID','=',28)
                ->where('A.DIST_ID', $request->DIST_ID)
                ->orderby('A.CREATE_TIMESTAMP', 'DESC')
                ->get();



            foreach ($data as $element) {
                $appr_remark = DB::table('distributor_management.DIVESTMENT_APPROVAL')->where('DIVE_ID', '=', $element->DIVE_ID)->whereNotNull('APPR_REMARK')->whereIn('TS_ID', [9, 28])->first();
                $appr_dist_remark = DB::table('distributor_management.DIVESTMENT_DIST_APPROVAL')->where('DIVE_ID', '=', $element->DIVE_ID)->whereNotNull('APPR_REMARK')->where('TS_ID', '=', 7)->first();
                if ($appr_remark) {
                    $element->APPR_REMARK = $appr_remark->APPR_REMARK;
                } elseif ($appr_dist_remark) {
                    $element->APPR_REMARK = $appr_dist_remark->APPR_REMARK;
                } else {
                    $element->APPR_REMARK = '';
                }




                if ($element->USER_NAME == null) {
                    $element->USER_NAME = $element->USER_NAME ?? '-';
                }
                if ($element->ACTION_BY_FIMM == null) {
                    $element->ACTION_BY_FIMM = $element->ACTION_BY_FIMM ?? '-';
                }

                if ($element->FIMM_TS_PARAM == null) {
                    $element->FIMM_TS_PARAM = $element->USER_FIMM_TS_PARAMNAME ?? '-';
                }


                $element->DATE_DISPLAY = date('d-M-Y', strtotime($element->DATE));
                $element->CESS_DATE_DISPLAY = date('d-M-Y', strtotime($element->CESS_DATE));
                $element->LEG_DATE_DISPLAY = date('d-M-Y', strtotime($element->LEG_DATE));
                $element->LATEST_UPDATE = date('d-M-Y', strtotime($element->LATEST_UPDATE));
            };
            // dd($data);

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

    // divestment approver list (Distributor)
    public function DivestmentApproverList(Request $request)
    {
        try {

            $user = DB::table('distributor_management.USER AS A')
                ->select('A.USER_DIST_ID', 'A.USER_GROUP')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();

            //  dd($user);

            $data = DB::table('distributor_management.DIVESTMENT_DIST_APPROVAL')
                ->select('*', 'A.CREATE_TIMESTAMP AS DATE', 'A.CESSATION_DATE', 'A.LEGAL_DATE', 'E.USER_NAME', 'A.FIMM_TS_ID as ACTION_BY_FIMM')
                ->leftJoin('DIVESTMENT AS A', 'A.DIVE_ID', '=', 'DIVESTMENT_DIST_APPROVAL.DIVE_ID')
                ->leftJoin('distributor_management.DISTRIBUTOR AS B', 'B.DISTRIBUTOR_ID', '=', 'A.DIST_ID_MERGE')
                ->leftJoin('admin_management.DISTRIBUTOR_SETTING AS C', 'C.DISTRIBUTOR_SETTING_ID', '=', 'A.DIVE_TYPE')
                ->leftJoin('admin_management.TASK_STATUS AS D', 'D.TS_ID', '=', 'DIVESTMENT_DIST_APPROVAL.TS_ID')
                ->where('DIVESTMENT_DIST_APPROVAL.APPR_GROUP_ID', $user->USER_GROUP)
                ->leftJoin('distributor_management.USER AS E', 'E.USER_ID', '=', 'A.LATEST_UPDATE_BY')
                ->where('A.DIST_ID', $user->USER_DIST_ID)
                ->orderby('A.CREATE_TIMESTAMP', 'DESC')
                ->get();




            foreach ($data as $element) {
                if ($element->ACTION_BY_FIMM == null || $element->ACTION_BY_FIMM == 0) {
                    $element->ACTION_BY_FIMM = '-';
                } else {
                    $element->ACTION_BY_FIMM = DB::table('admin_management.TASK_STATUS')->where('TS_ID', $element->ACTION_BY_FIMM)->value('TS_PARAM');
                }
                if ($element->USER_NAME == null) {
                    $element->USER_NAME = $element->USER_NAME ?? '-';
                }
                $element->DATE_DISPLAY = date('d-M-Y', strtotime($element->DATE));
                $element->CESS_DATE_DISPLAY = date('d-M-Y', strtotime($element->CESSATION_DATE));
                $element->LEG_DATE_DISPLAY = date('d-M-Y', strtotime($element->LEGAL_DATE));
            };

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
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

    // divestment approver list (fimm)
    public function DivestmentFimmList(Request $request)
    {
        try {

            $user = DB::table('admin_management.USER AS A')
                ->select('*')
                ->where('A.USER_ID', $request->USER_ID)
                ->first();


            $data = DB::table('distributor_management.DIVESTMENT_APPROVAL')
                ->select('*', 'A.CREATE_TIMESTAMP AS DATE', 'A.CESSATION_DATE', 'A.LEGAL_DATE', 'C.DIST_NAME AS DIST_DIVE_NAME', 'B.DIST_NAME AS DIST_MERGE_NAME')
                ->leftJoin('DIVESTMENT AS A', 'A.DIVE_ID', '=', 'DIVESTMENT_APPROVAL.DIVE_ID')
                ->leftJoin('distributor_management.DISTRIBUTOR AS B', 'B.DISTRIBUTOR_ID', '=', 'A.DIST_ID_MERGE')
                ->leftJoin('distributor_management.DISTRIBUTOR AS C', 'C.DISTRIBUTOR_ID', '=', 'A.DIST_ID')
                ->leftJoin('admin_management.DISTRIBUTOR_SETTING AS C', 'C.DISTRIBUTOR_SETTING_ID', '=', 'A.DIVE_TYPE')
                ->leftJoin('admin_management.TASK_STATUS AS D', 'D.TS_ID', '=', 'DIVESTMENT_APPROVAL.TS_ID')
                ->leftJoin('admin_management.USER AS E', 'E.USER_ID', '=', 'A.FIMM_LATEST_UPDATE')
                ->where('DIVESTMENT_APPROVAL.APPR_GROUP_ID', $user->USER_GROUP)
                ->orderby('A.CREATE_TIMESTAMP', 'DESC')
                ->get();

            foreach ($data as $element) {
                if ($element->USER_NAME == null) {
                    $element->USER_NAME = $element->USER_NAME ?? '-';
                }

                $element->LATEST_UPDATE = date('d-M-Y', strtotime($element->LATEST_UPDATE));
                $element->DATE_DISPLAY = date('d-M-Y', strtotime($element->DATE));
                $element->CESS_DATE_DISPLAY = date('d-M-Y', strtotime($element->CESSATION_DATE));
                $element->LEG_DATE_DISPLAY = date('d-M-Y', strtotime($element->LEGAL_DATE));
            };

            http_response_code(200);
            return response([
                'message' => 'Data successfully retrieved.',
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

    // approval update 2nd level
    public function updateApproval(Request $request)
    {

        try {


            //update the latest update by value
            $divestment = Divestment::where('DIVE_ID', $request->DIVE_ID)->update([
                'LATEST_UPDATE_BY' => $request->CREATE_BY,
                'TS_ID' => $request->TS_ID,
            ]);


            // $appr = DivestmentApprover::find($request->DIVE_APPR_ID);
            $appr = DivestmentDistApprover::find($request->DIVE_DIST_APPR_ID);
            $appr->APPR_REMARK = $request->APPR_REMARK;
            $appr->CREATE_BY = $request->CREATE_BY;
            $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            $appr->TS_ID = $request->TS_ID;
            $appr->save();


            if ($request->APPR_PUBLISH_STATUS == "1") {

                if ($request->TS_ID == "7") {
                    $returnSubmission = Divestment::find($request->DIVE_ID);
                    $returnSubmission->TS_ID = $request->TS_ID;

                    //updated by added
                    $returnSubmission->LATEST_UPDATE_BY = $request->CREATE_BY;

                    $returnSubmission->save();

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $returnSubmission->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }
                } else if ($request->TS_ID == "5") {


                    $rejectSubmission = Divestment::find($request->DIVE_ID);
                    $rejectSubmission->TS_ID = $request->TS_ID;
                    //updated by added
                    $rejectSubmission->LATEST_UPDATE_BY = $request->CREATE_BY;
                    $rejectSubmission->save();

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $returnSubmission->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }
                } else if ($request->TS_ID == "3") {

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $countApprover = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->get();

                        $getCount = count($countApprover);

                        if ($getCount == 0) {

                            // $dataApproval = new DivestmentDistApprover;
                            // $dataApproval->DIVE_ID = $request->DIVE_ID;
                            // $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            // $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                            // $dataApproval->TS_ID = 15;
                            // $dataApproval->save();

                            //divestment approval
                            $divestmentdataApproval = new DivestmentApprover;
                            $divestmentdataApproval->DIVE_ID = $request->DIVE_ID;
                            $divestmentdataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            $divestmentdataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                            $divestmentdataApproval->APPR_PUBLISH_STATUS = $item->APPR_STATUS;
                            $divestmentdataApproval->TS_ID = 15;
                            $divestmentdataApproval->save();
                        } else {

                            // //resubmit
                            // $Approverupdate= DivestmentDistApprover::where('DIVE_ID',$request->DIVE_ID)
                            // ->where('APPR_GROUP_ID',$item->APPR_GROUP_ID)
                            // ->get();

                            // foreach ($Approverupdate as $resubmit) {
                            //     $resubmit->TS_ID = 15;
                            //     $resubmit->save();
                            // }

                            //resubmit
                            $Approverupdate = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)
                                ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)
                                ->get();

                            foreach ($Approverupdate as $resubmit) {
                                $resubmit->TS_ID = 15;
                                $resubmit->save();
                            }
                        }

                        $notification = new ManageNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }
                }
            }

            //file Upload approver Document (6)
            $FileSecondReview = $request->FileSecondReview;
            if ($FileSecondReview != null && $request->hasFile('FileSecondReview')) {
                foreach ($FileSecondReview as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }


            http_response_code(200);
            return response([
                'message' => 'Data successfully saved.',
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be save.',
                'errorCode' => 0,
            ]);
        }
    }

    // approval update rd
    public function updateApprovalrd(Request $request)
    {
        try {

            $divestment = Divestment::where('DIVE_ID', $request->DIVE_ID)->update([
                'FIMM_LATEST_UPDATE' => $request->CREATE_BY,
                // 'TS_ID'=>$request->TS_ID,
                'FIMM_TS_ID' => 15,
            ]);


            $appr = DivestmentApprover::find($request->DIVE_APPR_ID);
            $appr->APPR_REMARK = $request->APPR_REMARK;
            $appr->CREATE_BY = $request->CREATE_BY;
            $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            $appr->TS_ID = $request->TS_ID;
            $appr->save();

            // $newappr = new DivestmentApprover;
            // $newappr->DIVE_ID=$request->DIVE_ID;
            // $newappr->APPR_REMARK=$request->APPR_REMARK;
            // $newappr->CREATE_BY=$request->CREATE_BY;
            // $newappr->APPR_PUBLISH_STATUS=$request->APPR_PUBLISH_STATUS;
            // $newappr->TS_ID=$request->TS_ID;
            // $newappr->save();

            if ($request->APPR_PUBLISH_STATUS == "1") {

                if ($request->TS_ID == "28") {

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $returndiveid = Divestment::find($request->DIVE_ID);

                        $returnSubmission = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->first();
                        $returnSubmission->TS_ID = $request->TS_ID;
                        $returnSubmission->save();

                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $returndiveid->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }
                } else if ($request->TS_ID == "5") {

                    $rejectSubmission = Divestment::find($request->DIVE_ID);
                    $rejectSubmission->TS_ID = $request->TS_ID;
                    $rejectSubmission->save();

                    $rejectDiveid = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)->get();
                    foreach ($rejectDiveid as $reject) {
                        $reject->TS_ID = $request->TS_ID;
                        $reject->save();
                    }

                    $rejectDiveDistid = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)->get();
                    foreach ($rejectDiveDistid as $reject) {
                        $reject->TS_ID = $request->TS_ID;
                        $reject->save();
                    }


                    //reject hantar notification to 2nd level,admin


                } else if ($request->TS_ID == "3") {

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $countApprover = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->get();

                        $getCount = count($countApprover);

                        if ($getCount == 0) {

                            $dataApproval = new DivestmentApprover;
                            $dataApproval->DIVE_ID = $request->DIVE_ID;
                            $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            $dataApproval->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                            $dataApproval->TS_ID = 15;

                            //  $dataApproval->APPR_REMARK=$request->APPR_REMARK;

                            $dataApproval->save();


                            //Divest dist approval added
                            // $dataApprovalDist = new DivestmentDistApprover;
                            // $dataApprovalDist->DIVE_ID = $request->DIVE_ID;
                            // $dataApprovalDist->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            // $dataApprovalDist->APPROVAL_LEVEL_ID = $item->APPROVAL_LEVEL_ID;
                            // $dataApprovalDist->TS_ID = 15;

                            // $dataApprovalDist->APPR_REMARK=$request->APPR_REMARK;

                            // $dataApprovalDist->save();


                        } else {

                            //resubmit
                            $Approverupdate = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)
                                ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)
                                ->get();

                            foreach ($Approverupdate as $resubmit) {
                                $resubmit->TS_ID = 15;
                                $resubmit->APPR_PUBLISH_STATUS = 0;

                                $resubmit->APPR_REMARK = $request->APPR_REMARK;

                                $resubmit->save();
                            }


                            //resubmit divestment dist approval
                            //  $Approverdistupdate= DivestmentDistApprover::where('DIVE_ID',$request->DIVE_ID)
                            //  ->where('APPR_GROUP_ID',$item->APPR_GROUP_ID)
                            //  ->get();

                            //  foreach ($Approverdistupdate as $resubmit) {
                            //      $resubmit->TS_ID = 15;
                            //      $resubmit->APPR_PUBLISH_STATUS = 0;

                            //      $resubmit->APPR_REMARK=$request->APPR_REMARK;

                            //      $resubmit->save();
                            //  }

                            //Create new DIST GROUP ID=2
                            // $newDistAppr = new  DivestmentDistApprover;
                            // $newDistAppr->DIVE_ID=$request->DIVE_ID;
                            // $newDistAppr->APPR_GROUP_ID=$item->APPR_GROUP_ID;

                            // $newDistAppr->APPROVAL_LEVEL_ID = $request->APPROVAL_LEVEL_ID;

                            // $newDistAppr->APPR_REMARK = $request->APPR_REMARK;
                            // $newDistAppr->CREATE_BY = $request->CREATE_BY;
                            // $newDistAppr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
                            // $newDistAppr->TS_ID = $request->TS_ID;
                            // $newDistAppr->save();

                        }

                        $notification = new ManageNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }
                }
            }

            //file Upload approver Document (6)
            $FileSecondReview = $request->FileSecondReview;
            if ($FileSecondReview != null) {
                foreach ($FileSecondReview as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }


            http_response_code(200);
            return response([
                'message' => 'Data successfully saved.',
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be save.',
                'errorCode' => 0,
            ]);
        }
    }

    // approval update hodrd
    public function updateApprovalhodrd(Request $request)
    {
        try {
            Divestment::where('DIVE_ID', $request->DIVE_ID)->update([
                // 'TS_ID'=>$request->TS_ID,
                'FIMM_LATEST_UPDATE' => $request->CREATE_BY,
                'FIMM_TS_ID' => $request->TS_ID,
            ]);

            $divestment = Divestment::where('DIVE_ID', $request->DIVE_ID)->first();


            $appr = DivestmentApprover::find($request->DIVE_APPR_ID);
            $appr->APPR_REMARK = $request->APPR_REMARK;
            $appr->CREATE_BY = $request->CREATE_BY;
            $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            $appr->TS_ID = $request->TS_ID;
            $appr->save();

            // $appr = DivestmentDistApprover::find($request->DIVE_DIST_APPR_ID);
            // $appr->APPR_REMARK = $request->APPR_REMARK;
            // $appr->CREATE_BY = $request->CREATE_BY;
            // $appr->APPR_PUBLISH_STATUS = $request->APPR_PUBLISH_STATUS;
            // $appr->TS_ID = $request->TS_ID;
            // $appr->save();


            if ($request->APPR_PUBLISH_STATUS == "1") {

                if ($request->TS_ID == "9") {

                    // DB::enableQueryLog();
                    // dd(DB::getQueryLog());

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $returnSubmission = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)->get();


                        foreach ($returnSubmission as $return) {
                            $return->TS_ID = $request->TS_ID;
                            $return->save();
                        }

                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $divestment->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }
                } else if ($request->TS_ID == "5") {

                    $rejectSubmission = Divestment::find($request->DIVE_ID);
                    $rejectSubmission->TS_ID = $request->TS_ID;
                    $rejectSubmission->save();

                    $rejectDiveid = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)->get();
                    foreach ($rejectDiveid as $reject) {
                        $reject->TS_ID = $request->TS_ID;
                        $reject->save();
                    }

                    $rejectDiveDistid = DivestmentDistApprover::where('DIVE_ID', $request->DIVE_ID)->get();
                    foreach ($rejectDiveDistid as $reject) {
                        $reject->TS_ID = $request->TS_ID;
                        $reject->save();
                    }

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $rejectSubmission->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }

                    //reject hantar notification to 2nd level,admin

                } else if ($request->TS_ID == "3") {

                    foreach (json_decode($request->APPR_LIST) as $item) {

                        $countApprover = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)->get();

                        $getCount = count($countApprover);

                        if ($getCount == 0) {

                            $dataApproval = new DivestmentApprover;
                            $dataApproval->DIVE_ID = $request->DIVE_ID;
                            $dataApproval->APPR_GROUP_ID = $item->APPR_GROUP_ID;
                            $dataApproval->APPROVAL_LEVEL_ID = $item->DISTRIBUTOR_APPROVAL_LEVEL_ID;
                            $dataApproval->TS_ID = 15;
                            $dataApproval->save();
                        } else {

                            //resubmit
                            $Approverupdate = DivestmentApprover::where('DIVE_ID', $request->DIVE_ID)
                                ->where('APPR_GROUP_ID', $item->APPR_GROUP_ID)
                                ->get();

                            foreach ($Approverupdate as $resubmit) {
                                $resubmit->TS_ID = 15;
                                $resubmit->APPR_PUBLISH_STATUS = 0;
                                $resubmit->save();
                            }
                        }

                        $notification = new ManageDistributorNotification();
                        $add = $notification->add($item->APPR_GROUP_ID, $item->APPR_PROCESSFLOW_ID, $divestment->DIST_ID, $request->NOTI_REMARK, $request->NOTI_LOCATION);
                    }

                    // action to change divestment part

                }
            }

            //file Upload approver Document (6)
            $FileSecondReview = $request->FileSecondReview;
            if ($FileSecondReview != null && $request->hasFile('FileSecondReview')) {
                foreach ($FileSecondReview as $item) {
                    $itemFile = $item;
                    $blob = $itemFile->openFile()->fread($itemFile->getSize()); //convert ke blob
                    $upFile = new DivestmentDocument;
                    $upFile->DOC_BLOB = $blob;
                    $upFile->FILE_NO = $request->FILE_NO;
                    $upFile->DOC_MIMETYPE = $itemFile->getMimeType();
                    $upFile->DOC_ORIGINAL_NAME = $itemFile->getClientOriginalName(); //$request->data;
                    $upFile->DOC_FILESIZE = $itemFile->getSize();
                    $upFile->DOC_FILETYPE = $itemFile->getClientOriginalExtension();
                    $upFile->CREATE_BY = $request->CREATE_BY;
                    $upFile->DIVE_ID = $request->DIVE_ID;
                    $upFile->save();
                }
            }


            http_response_code(200);
            return response([
                'message' => 'Data successfully saved.',
            ]);
        } catch (RequestException $r) {
            http_response_code(400);
            return response([
                'message' => 'Data failed to be save.',
                'errorCode' => 0,
            ]);
        }
    }
}
