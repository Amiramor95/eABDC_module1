<?php

namespace App\Http\Controllers;

use App\Http\Resources\Distributor_Module\ExtensionRequest as Distributor_ModuleExtensionRequest;
use App\Http\Resources\Distributor_Module\SubsequentExtensionRequest as Distributor_ModuleSubsequentExtensionRequest;
use App\Models\Distributor;
use App\Models\DistributorApproval;
use Illuminate\Http\Request;
use App\Models\ExtensionRequest;
use App\Models\ExtensionRequestApproval;
use App\Models\ExtensionRequestApprovalDocument;
use App\Models\ExtensionRequestDocument;
use App\Models\SubsequentExtensionRequest;
use App\Models\SubsequentExtensionRequestDocument;
use App\Models\TaskStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DistributorExtensionRequest extends Controller
{

    public function index(Request $request)
    {

        $params = collect(json_decode(base64_decode($request->params)));

        // filter purpose
        $TS_PARAM = [];
        if(isset($params['STATUS']) && ($params['STATUS'] !== 'null' || !is_null($params['STATUS']))) {
            $TS_PARAM = TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID');
        }

        $query = ExtensionRequest::with(['taskStatus', 'distributor', 'author']);

        if(filter_var($params['IS_FIMM'], FILTER_VALIDATE_BOOL)) { // If FIMM

            switch($params['USER_GROUP_ID']) {
                case 1:
                    $relation = 'ceoApproval';
                    break;
                case 2:
                    $relation = 'rdHodApproval';
                    break;
                case 3:
                    $relation = 'gmApproval';
                    break;
                // case 4:
                case 5:
                    $relation = 'rdApproval';
                    break;
                default:
                return response()->json(['message' => 'error'], 400);
            }
        }else{ // DIST LIST

            if($params['USER_GROUP_ID'] == 1) { // DIST MANAGER

                $relation = 'managerApproval';

            }else { // DIST ADMIN

                $query->with('managerApproval')->when(isset($params['STATUS']) && ($params['STATUS'] !== 'null' || !is_null($params['STATUS'])), function($query) use($TS_PARAM){
                    return $query->whereIn('EXTENSION_STATUS_ID', $TS_PARAM);
                })->where($params->only('DISTRIBUTOR_ID')->toArray());

            }

        }

        // Check if FIMM user or Manager of Dist
        if(filter_var($params['IS_FIMM'], FILTER_VALIDATE_BOOL) || (filter_var($params['IS_FIMM'], FILTER_VALIDATE_BOOL) == false && $params['USER_GROUP_ID'] != 3)) {
            $query->with("$relation.taskStatus")->whereHas($relation, function($query) use($params){
                return $query->whereIn('TS_ID', TaskStatus::whereIn('TS_PARAM', ['PENDING', 'DRAFT', 'RETURNED', 'APPROVED', 'REJECTED'])
                ->when($params['STATUS'] !== 'null' || !is_null($params['STATUS']), function($query) use($params){
                    return $query->where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%");
                })
                ->pluck('TS_ID'))
                ->where([
                    'IS_FIMM' => filter_var($params['IS_FIMM'], FILTER_VALIDATE_BOOL),
                    'IS_SUBSEQUENT' => false]);
            });
        }

        if(array_key_exists('DIST_NAME', $params->toArray()) && ($params['DIST_NAME'] != '' && $params['DIST_NAME'] != 'null')){
            $query->whereHas('distributor', function($query) use($params) {
                return $query->where('DIST_NAME', 'LIKE', "%{$params['DIST_NAME']}%");
            });
        }

        if(array_key_exists('DIST_NO', $params->toArray()) && ($params['DIST_NO'] != '' && $params['DIST_NO'] != 'null')){
            $query->whereHas('distributor', function($query) use($params) {
                return $query->where('DIST_CODE', 'LIKE', "%{$params['DIST_NO']}%");
            });
        }

        if(array_key_exists('EXTENSION_TYPE', $params->toArray()) && $params['EXTENSION_TYPE'] != ''){
            $query->where(function($query) use($params) {
                return $query->where('EXTENSION_REQUEST.EXTENSION_TYPE', 'LIKE', "%{$params['EXTENSION_TYPE']}%")
                        ->orWhere('EXTENSION_REQUEST.OTHER_EXTENSION_TYPE', 'LIKE', "%{$params['EXTENSION_TYPE']}%");
            });
        }

        $data = $query->latest('SUBMISSION_DATE')
        ->get();

        if($params->has('GROUP') && $params['GROUP'] == 'true') {
            $data = $data->groupBy('distributor.DIST_NAME');

            $items = [];
            foreach($data as $key => $item) {
                $items[] = [
                    'DIST_NAME' => $key,
                    'DIST_CODE' => $item[0]['distributor']['DIST_CODE'],
                    'COUNT' => $item->count()
                ];
            }

            $data = $items;

        }else{

            $response = [];

            foreach($data as $item) {
                //  $status based on user level

                $itemStatus = null;
                $loadAmendment = null;

                if(filter_var($params['IS_FIMM'], FILTER_VALIDATE_BOOL)) {

                    // if($params['USER_GROUP_ID'] == 4) {
                    if($params['USER_GROUP_ID'] == 5) {
                        $itemStatus = $item->rdApproval;
                        $loadAmendment = 'rdHodApproval';
                    }else {
                        $itemStatus = $item->rdHodApproval;
                        $loadAmendment = 'gmApproval.taskStatus';
                    }

                }else {

                    if($params['USER_GROUP_ID'] == 3) {
                        $itemStatus = $item;
                    }else {
                        $itemStatus = $item->managerApproval;
                    }

                    $loadAmendment = 'rdApproval';

                }

                if($itemStatus->taskStatus->TS_PARAM == 'RETURNED') {
                    $item->load($loadAmendment);
                }

                $response[] = $item;

            }

            $data = $response;

        }


        return response()->json([
            'data' => $data,
            'params' => $params
        ], 200);

    }

    public function getById(Request $request, $id)
    {
        try{

            $data = ExtensionRequest::with('documents')->when($request->IS_FIMM == true || $request->IS_FIMM == 'true', function($query) use($request){
                return $query->with(['approvalLogs' => function($query) {
                    return $query->with(['approval.user', 'approval.group'])->whereHas('approval', function($query) {
                        return $query->where('EXTENSION_REQUEST_APPROVAL.IS_FIMM', true);
                    })->latest();
                }])->when($request->USER_GROUP_ID == 1, function($query) {
                    return $query->with(['ceoApproval' => function($query) {
                        return $query->with(['taskStatus', 'documents']);
                    }, 'gmApproval.documents', 'rdHodApproval.documents', 'rdApproval.documents', 'managerApproval.documents']);
                })
                ->when($request->USER_GROUP_ID == 3, function($query) {
                    return $query->with(['gmApproval' => function($query) {
                        return $query->with(['taskStatus', 'documents']);
                    },'rdHodApproval.documents', 'rdApproval.documents', 'managerApproval.documents']);
                })->when($request->USER_GROUP_ID == 2, function($query) use($request){
                    return $query->with(['rdHodApproval' => function($query) {
                        return $query->with(['taskStatus', 'documents']);
                    }, 'rdApproval.documents', 'managerApproval.documents'])->when($request->has('CORRECTION'), function($query) {
                        return $query->with('gmApproval.documents');
                    });
                // })->when($request->USER_GROUP_ID == 4, function($query) use($request){
                })->when($request->USER_GROUP_ID == 5, function($query) use($request){
                    return $query->with(['rdApproval' => function($query){
                        return $query->with(['taskStatus', 'documents']);
                    }, 'managerApproval.documents'])->when($request->has('CORRECTION'), function($query) {
                        return $query->with('rdHodApproval.documents');
                    });
                });
                return $query->with('rdApproval');
            })->when($request->IS_FIMM == false|| $request->IS_FIMM == 'false', function($query) use($request){
                return $query->when($request->ACTION == 'correction', function($query) {
                    return $query->with('managerApproval.documents');
                })->when($request->USER_GROUP_ID == 1, function($query) {
                    return $query->with(['managerApproval' => function($query) {
                        return $query->with(['taskStatus', 'documents']);
                    }]);
                });
            })->findOrFail($id);

            if(strtoupper($data->taskStatus->TS_PARAM) == 'APPROVED') {
                $data->load('ceoApproval.documents');
            }

            if(boolval($request->IS_FIMM)) {
                // if (intval($request->USER_GROUP_ID) === 4) { // Staff RD Block
                if (intval($request->USER_GROUP_ID) === 5) { // Staff RD Block
                    if($data->rdHodApproval()->exists()) {
                        if($data->rdHodApproval->taskStatus->TS_PARAM === 'RETURNED') {
                            $data->load('rdHodApproval.documents');
                        }
                    }
                }elseif(intval($request->USER_GROUP_ID) === 2) { // HOD RD Block
                    if($data->gmApproval()->exists()) {
                        if($data->gmApproval->taskStatus->TS_PARAM === 'RETURNED') {
                            $data->load('gmApproval.documents');
                        }
                    }
                }
            }

            return response()->json([
                'message' => 'Data successfully fetch',
                'data' => new Distributor_ModuleExtensionRequest($data)
            ], 200);

        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Invalid id given',
                'trace' => $e->getTrace()
            ], 400);

        }
    }

    public function getSubsequentById(Request $request, $id)
    {
        try{

            $data = SubsequentExtensionRequest::with(['documents', 'extensionRequest.documents'])
                    ->when($request->IS_FIMM == true || $request->IS_FIMM == 'true', function($query) use($request){
                        return $query->with(['approvalLogs' => function($query) {
                            return $query->with(['approval.user', 'approval.group'])->whereHas('approval', function($query) {
                                return $query->where('EXTENSION_REQUEST_APPROVAL.IS_FIMM', true);
                            })->latest();
                        }])->when($request->USER_GROUP_ID == 1, function($query) {
                            return $query->with(['ceoApproval' => function($query) {
                                return $query->with(['taskStatus', 'documents']);
                            }, 'gmApproval.documents', 'rdHodApproval.documents', 'rdApproval.documents', 'managerApproval.documents']);
                        })
                        ->when($request->USER_GROUP_ID == 3, function($query) {
                            return $query->with(['gmApproval' => function($query) {
                                return $query->with(['taskStatus', 'documents']);
                            },'rdHodApproval.documents', 'rdApproval.documents', 'managerApproval.documents']);
                        })->when($request->USER_GROUP_ID == 2, function($query) use($request){
                            return $query->with(['rdHodApproval' => function($query) {
                                return $query->with(['taskStatus', 'documents']);
                            }, 'rdApproval.documents', 'managerApproval.documents'])->when($request->has('CORRECTION'), function($query) {
                                return $query->with('gmApproval.documents');
                            });
                        // })->when($request->USER_GROUP_ID == 4, function($query) use($request){
                        })->when($request->USER_GROUP_ID == 5, function($query) use($request){
                            return $query->with(['rdApproval' => function($query){
                                return $query->with(['taskStatus', 'documents']);
                            }, 'managerApproval.documents'])->when($request->has('CORRECTION'), function($query) {
                                return $query->with('rdHodApproval.documents');
                            });
                        });
                    return $query->with('rdApproval');
                })->when($request->IS_FIMM == false|| $request->IS_FIMM == 'false', function($query) use($request){
                    return $query->when($request->ACTION == 'correction', function($query) {
                        return $query->with('managerApproval.documents');
                    })->when($request->USER_GROUP_ID == 1, function($query) {
                        return $query->with(['managerApproval' => function($query) {
                            return $query->with(['taskStatus', 'documents']);
                        }]);
                    });
                })
                ->findOrFail($id);


                if(boolval($request->IS_FIMM)) {
                    // if(intval($request->USER_GROUP_ID) === 4) { // Staff RD Block
                    if(intval($request->USER_GROUP_ID) === 5) { // Staff RD Block
                        if($data->rdHodApproval()->exists()) {
                            if($data->rdHodApproval->taskStatus->TS_PARAM == 'RETURNED') {
                                $data->load('rdHodApproval.documents');
                            }
                        }
                    }elseif(intval($request->USER_GROUP_ID) === 2) { // HOD RD Block
                        if($data->gmApproval()->exists()) {
                            if($data->gmApproval->taskStatus->TS_PARAM === 'RETURNED') {
                                $data->load('gmApproval.documents');
                            }
                        }
                    }
                }

                if(strtoupper($data->taskStatus->TS_PARAM) == 'APPROVED') {
                    $data->load('ceoApproval.documents');
                }

            return response()->json([
                'message' => 'Data successfully fetch',
                'data' => new Distributor_ModuleSubsequentExtensionRequest($data),
                'param' => $request->all()
            ], 200);

        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'message' => 'Invalid id given',
                'trace' => $e->getTrace()
            ], 404);

        }
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try{

            $request->EXTENSION_END_DATE = Carbon::parse($request->EXTENSION_END_DATE)->toDateString();
            $request->merge([
                'EXTENSION_STATUS_ID' => DB::connection('module0')->table('TASK_STATUS')->where('TS_PARAM', $request->EXTENSION_STATUS_ID)->first()->TS_ID,
                'SUBMISSION_DATE' => $request->EXTENSION_STATUS_ID != 1 ? Carbon::now()->toDateTimeString() : null
            ]);


            $extensionRequest = ExtensionRequest::create($request->only([
                'EXTENSION_TYPE',
                'OTHER_EXTENSION_TYPE',
                'DISTRIBUTOR_ID',
                'JUSTIFICATION',
                'EXTENSION_END_DATE',
                'EXTENSION_STATUS_ID',
                'SUBMISSION_DATE',
                'CREATED_BY'
            ]));

            if($request->hasAny('FILE')) {

                foreach($request->allFiles() as $key => $val) {

                    foreach($val as $item){

                        $contents = $item->openFile()->fread($item->getSize());

                        $input = [
                            'EXTENSION_REQUEST_ID' => $extensionRequest->EXTENSION_REQUEST_ID,
                            'IS_ACTION_PLAN' => $key == 'ACTION_FILE' ?? false,
                            'DOCUMENT_NAME' => $item->getClientOriginalName(),
                            'DOCUMENT_BLOB' => $contents,
                            'DOCUMENT_SIZE' => $item->getSize(),
                            'DOCUMENT_TYPE' => $item->getClientOriginalExtension(),
                            'SUBMISSION_DATE' => $request->EXTENSION_STATUS_ID != 1 ? Carbon::now()->toDateTimeString() : null
                        ];

                        ExtensionRequestDocument::create($input);

                    }

                }
            }

            if($request->EXTENSION_STATUS_ID != 1) {

                $firstApproval = DB::connection('module0')->table('DIST_APPROVAL_LEVEL')->where(['DIST_APPR_PROCESSFLOW_ID' => intval(58), 'DIST_APPR_INDEX' => intval(1)])->first();

                ExtensionRequestApproval::create([
                    'APPROVAL_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                    'APPROVAL_LEVEL_ID' => $firstApproval->DIST_APPROVAL_LEVEL_ID,
                    'EXTENSION_REQUEST_ID' => $extensionRequest->getKey(),
                    'TS_ID' => $request->EXTENSION_STATUS_ID,
                ]);

                DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                    'NOTIFICATION_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                    'DISTRIBUTOR_ID' => $extensionRequest->DISTRIBUTOR_ID,
                    'PROCESS_FLOW_ID' => $firstApproval->DIST_APPR_PROCESSFLOW_ID,
                    'NOTIFICATION_STATUS' => intval(0),
                    'REMARK' => '(EXT TIME) New Entry Pending for Approval.',
                    'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                    'LOCATION' => 'extension-request'
                ]);

            }

            DB::commit();

            return response()->json([
                'data' => $request->all(),
                'auth' => auth()->user(),
                'output' => $extensionRequest
            ], 200);

        }catch (\Illuminate\Database\QueryException $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);

        }catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);

        }

    }

    public function update(Request $request, $id)
    {

        DB::beginTransaction();

        try{

            $extensionRequest = ExtensionRequest::findOrFail($id);

            $request->EXTENSION_END_DATE = Carbon::parse($request->EXTENSION_END_DATE)->toDateString();
            $request->merge(['EXTENSION_STATUS_ID' => TaskStatus::where('TS_PARAM', $request->STATUS)->first()->getKey()]);

            tap($extensionRequest)->update($request->only(['EXTENSION_STATUS_ID', 'EXTENSION_REQUEST_ID', 'EXTENSION_TYPE', 'OTHER_EXTENSION_TYPE', 'JUSTIFICATION', 'EXTENSION_END_DATE']));

            if($request->hasAny('FILE')) {

                foreach($request->allFiles() as $key => $val) {

                    foreach($val as $item){

                        $contents = $item->openFile()->fread($item->getSize());

                        $input = [
                            'EXTENSION_REQUEST_ID' => $id,
                            'IS_ACTION_PLAN' => $key == 'ACTION_FILE' ?? false,
                            'DOCUMENT_NAME' => $item->getClientOriginalName(),
                            'DOCUMENT_BLOB' => $contents,
                            'DOCUMENT_SIZE' => $item->getSize(),
                            'DOCUMENT_TYPE' => $item->getClientOriginalExtension()
                        ];

                        ExtensionRequestDocument::create($input);

                    }

                }

            }

            if(!$extensionRequest->managerApproval()->exists()){

                if($request->EXTENSION_STATUS_ID == 15) {

                    tap($extensionRequest)->update(['SUBMISSION_DATE' => Carbon::now()->toDateTimeString()]);

                    $firstApproval = DB::connection('module0')->table('DIST_APPROVAL_LEVEL')->where(['DIST_APPR_PROCESSFLOW_ID' => intval(58), 'DIST_APPR_INDEX' => intval(1)])->first();

                    ExtensionRequestApproval::create([
                        'APPROVAL_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                        'APPROVAL_LEVEL_ID' => $firstApproval->DIST_APPROVAL_LEVEL_ID,
                        'EXTENSION_REQUEST_ID' => $id,
                        'TS_ID' => $request->EXTENSION_STATUS_ID,
                    ]);

                }

            }else {

                $updateInput = [
                    'TS_ID' => $request->EXTENSION_STATUS_ID,
                    'APPROVAL_PUBLISH_STATUS' => false
                ];

                if($request->EXTENSION_STATUS_ID == 15) {

                    $updateInput['APPROVAL_REMARK'] = null;

                    ExtensionRequestApprovalDocument::where([
                        'EXTENSION_REQUEST_APPROVAL_ID' => $extensionRequest->managerApproval->EXTENSION_REQUEST_APPROVAL_ID
                    ])->delete();

                }

                $extensionRequest->managerApproval()->update($updateInput);

            }

            if($request->EXTENSION_STATUS_ID == 15) {
                DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                    'NOTIFICATION_GROUP_ID' => 1,
                    'DISTRIBUTOR_ID' => $extensionRequest->DISTRIBUTOR_ID,
                    'PROCESS_FLOW_ID' => 58,
                    'NOTIFICATION_STATUS' => intval(0),
                    'REMARK' => '(EXT TIME) New Entry Pending for Approval.',
                    'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                    'LOCATION' => 'extension-request'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully update'
            ], 200);

        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Not found'
            ], 404);

        }

    }

    public function subsequentExtensionRequest(Request $request)
    {

        DB::beginTransaction();

        try{

            $request->merge(['TS_ID' => DB::connection('module0')->table('TASK_STATUS')->where('TS_PARAM', $request->STATUS)->first()->TS_ID]);
            $request->EXTENSION_END_DATE = Carbon::parse($request->EXTENSION_END_DATE)->toDateString();

            $subsequentRequest = SubsequentExtensionRequest::create($request->only([
                'EXTENSION_REQUEST_ID',
                'EXTENSION_TYPE',
                'OTHER_EXTENSION_TYPE',
                'DISTRIBUTOR_ID',
                'JUSTIFICATION',
                'EXTENSION_END_DATE',
                'TS_ID'
            ]));

            if($request->hasAny('FILE')) {

                foreach($request->allFiles() as $key => $val) {

                    foreach($val as $item){

                        $contents = $item->openFile()->fread($item->getSize());

                        $input = [
                            'SUBSEQUENT_EXTENSION_REQUEST_ID' => $subsequentRequest->getKey(),
                            'IS_ACTION_PLAN' => $key == 'ACTION_FILE' ?? false,
                            'DOCUMENT_NAME' => $item->getClientOriginalName(),
                            'DOCUMENT_BLOB' => $contents,
                            'DOCUMENT_SIZE' => $item->getSize(),
                            'DOCUMENT_TYPE' => $item->getClientOriginalExtension()
                        ];

                        SubsequentExtensionRequestDocument::create($input);

                    }

                }
            }

            if(strtoupper($request->STATUS) != 'DRAFT') {

                $firstApproval = DB::connection('module0')->table('DIST_APPROVAL_LEVEL')->where(['DIST_APPR_PROCESSFLOW_ID' => intval(58), 'DIST_APPR_INDEX' => intval(1)])->first();

                ExtensionRequestApproval::create([
                    'APPROVAL_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                    'APPROVAL_LEVEL_ID' => $firstApproval->DIST_APPROVAL_LEVEL_ID,
                    'EXTENSION_REQUEST_ID' => $subsequentRequest->getKey(),
                    'TS_ID' => $request->EXTENSION_STATUS_ID,
                ]);

                DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                    'NOTIFICATION_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                    'DISTRIBUTOR_ID' => $subsequentRequest->DISTRIBUTOR_ID,
                    'PROCESS_FLOW_ID' => $firstApproval->DIST_APPR_PROCESSFLOW_ID,
                    'NOTIFICATION_STATUS' => intval(0),
                    'REMARK' => '(SUB EXT TIME) New Entry Pending for Approval.',
                    'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                    'LOCATION' => 'subsequent-extension-request'
                ]);

            }

            DB::commit();

            return response()->json([
                'data' => $request->all(),
                'auth' => auth()->user(),
                'output' => $subsequentRequest
            ], 200);

        }catch (\Illuminate\Database\QueryException $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);

        }catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);

        }
    }

    // Approval
    public function extensionRequestApproval(Request $request)
    {

        $location = 'fimm-extension-request';

        if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
            $approvalLevelId = 21;
            $approvalIndex = 1;
        }else {

            switch($request->APPROVAL_GROUP_ID){
                // case 4:
                case 5:
                    $approvalLevelId = 85;
                    $approvalIndex = 1;
                break;
                case 2:
                    $approvalLevelId = 86;
                    $approvalIndex = 2;
                break;
                case 3:
                    $approvalLevelId = 87;
                    $approvalIndex = 3;
                break;
                case 1:
                    $approvalLevelId = 88;
                    $approvalIndex = 4;
                break;
                default:
                return response()->json(['message' => 'error'], 400);
            }

        }

        $extensionRequestApproval = ExtensionRequestApproval::query()->where([
            'EXTENSION_REQUEST_ID' => $request->EXTENSION_REQUEST_ID,
            'APPROVAL_GROUP_ID' => $request->APPROVAL_GROUP_ID,
            'APPROVAL_LEVEL_ID' => $approvalLevelId
        ]);

        if(!$extensionRequestApproval->exists()) {
            return response()->json([
                'message' => 'not found'
            ], 400);
        }

        $extensionRequestApproval = $extensionRequestApproval->first();

        $request->merge([
            'TS_ID' =>  TaskStatus::where(strtoupper($request->STATUS) == 'RETURNED TO CREATOR' ? 'TS_REMARK' : 'TS_PARAM', $request->STATUS)->first()->getKey(),
            'APPROVAL_PUBLISH_STATUS' => strtoupper($request->STATUS) == 'APPROVED' || strtoupper($request->STATUS) == 'REJECTED' || strtoupper($request->STATUS) == 'RETURNED TO CREATOR' ? true : false,
            'APPROVAL_DATE' => strtoupper($request->STATUS) == 'APPROVED' ? Carbon::today()->toDateString() : null
        ]);

        DB::beginTransaction();

        try{

            if(strtoupper($request->STATUS) === 'DRAFT') {

                $extensionRequestApproval->withoutEvents(function() use($request, $extensionRequestApproval) {

                    $extensionRequestApproval->update($request->only([
                        'TS_ID',
                        'APPROVAL_PUBLISH_STATUS',
                        'APPROVAL_REMARK',
                        'APPROVAL_DATE',
                        'CREATED_BY'
                    ]));

                });

            } else {

                $extensionRequestApproval->update($request->only([
                    'TS_ID',
                    'APPROVAL_PUBLISH_STATUS',
                    'APPROVAL_REMARK',
                    'APPROVAL_DATE',
                    'CREATED_BY'
                ]));

            }


            if($request->hasAny('FILE')) {

                foreach($request->allFiles() as $key => $val) {

                    foreach($val as $item){

                        $contents = $item->openFile()->fread($item->getSize());

                        $input = [
                            'EXTENSION_REQUEST_APPROVAL_ID' => $extensionRequestApproval->getKey(),
                            'DOCUMENT_NAME' => $item->getClientOriginalName(),
                            'DOCUMENT_BLOB' => $contents,
                            'DOCUMENT_SIZE' => $item->getSize(),
                            'DOCUMENT_TYPE' => $item->getClientOriginalExtension()
                        ];

                        ExtensionRequestApprovalDocument::create($input);

                    }

                }
            }

            if(filter_var($extensionRequestApproval->IS_SUBSEQUENT, FILTER_VALIDATE_BOOL)) {
                $distCode = $extensionRequestApproval->subsequentExtensionRequest->distributor->DIST_CODE;
            }else{
                $distCode = $extensionRequestApproval->extensionRequest->distributor->DIST_CODE;
            }

            if($request->STATUS == 'Approved') {

                $query = $extensionRequestApproval->extensionRequest;
                // $location .= "/approval";

                if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
                    $nextApproval = $query->rdApproval();
                }else{

                    switch($request->APPROVAL_GROUP_ID){
                        // case 4:
                        case 5:
                            $nextApproval = $query->rdHodApproval();
                        break;
                        case 2:
                            $nextApproval = $query->gmApproval();
                        break;
                        case 3:
                            $nextApproval = $query->ceoApproval();
                        break;
                        default:
                            $nextApproval = $query->ceoApproval();
                    }

                }

                if(!$nextApproval->exists() || $approvalIndex == 4) {

                    if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
                        if(strtoupper($request->APPROVAL_GROUP_ID) == intval(1)) {
                            $secondApproval = DB::connection('module0')->table('APPROVAL_LEVEL')->where([
                                'APPR_PROCESSFLOW_ID' => intval(58),
                                'APPR_INDEX' => intval(1)
                            ])->first();
                        }
                    }else{
                        if($approvalIndex == 4){
                            $extensionRequestApproval->extensionRequest()->update([
                                'EXTENSION_STATUS_ID' => $request->TS_ID,
                                'EXTENSION_APPROVAL_DATE' => Carbon::now()->toDateString()
                            ]);
                        }else {
                            $secondApproval = DB::connection('module0')->table('APPROVAL_LEVEL')->where([
                                'APPR_PROCESSFLOW_ID' => intval(58),
                                'APPR_INDEX' => intval($approvalIndex+=1)
                            ])->first();
                        }
                    }

                    if($approvalLevelId < 88) {

                        if($request->APPROVAL_GROUP_ID == 2 && $query->EXTENSION_TYPE !== 'COMMENCEMENT OF OPERATION') {

                            DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                                'NOTIFICATION_GROUP_ID' => 3,
                                'DISTRIBUTOR_ID' => $query->DISTRIBUTOR_ID,
                                'PROCESS_FLOW_ID' => 58,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(EXT TIME) Your request has been approved by Registration Head of Department.",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => $location
                            ]);

                            DB::connection('module0')->table('NOTIFICATION')->insert([
                                // 'NOTIFICATION_GROUP_ID' => 4,
                                'NOTIFICATION_GROUP_ID' => 5,
                                'PROCESS_FLOW_ID' => $secondApproval->APPR_PROCESSFLOW_ID,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(EXT TIME) Entry for {$distCode} has been Approved",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => $location
                            ]);

                            $extensionRequestApproval->extensionRequest()->update(['EXTENSION_STATUS_ID' => $request->TS_ID, 'EXTENSION_APPROVAL_DATE' => now()]);

                        } else {

                            ExtensionRequestApproval::create([
                                'APPROVAL_GROUP_ID' => $secondApproval->APPR_GROUP_ID,
                                'APPROVAL_LEVEL_ID' => $secondApproval->APPROVAL_LEVEL_ID,
                                'EXTENSION_REQUEST_ID' => $request->EXTENSION_REQUEST_ID,
                                'TS_ID' => TaskStatus::where('TS_PARAM', 'PENDING')->first()->getKey(),
                                'APPROVAL_PUBLISH_STATUS' => false,
                                'IS_FIMM' => true
                            ]);

                            DB::connection('module0')->table('NOTIFICATION')->insert([
                                'NOTIFICATION_GROUP_ID' => $secondApproval->APPR_GROUP_ID,
                                'PROCESS_FLOW_ID' => $secondApproval->APPR_PROCESSFLOW_ID,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(EXT TIME) New entry pending approval for {$distCode}",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => $location
                            ]);

                        }

                    }else{

                        DB::connection('module0')->table('NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => 85,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(EXT TIME) Request for {$distCode} has been approved.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => $location
                        ]);

                        DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => 3,
                            'DISTRIBUTOR_ID' => $query->DISTRIBUTOR_ID,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(EXT TIME) Your request has been approved.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => str_replace('fimm-', '', $location).'-exchange'
                        ]);
                    }

                }else{

                    $nextApproval->update([
                        'APPROVAL_REMARK' => null,
                        'TS_ID' => TaskStatus::where('TS_PARAM', 'PENDING')->first()->getKey(),
                        'APPROVAL_PUBLISH_STATUS' => false
                    ]);

                    ExtensionRequestApprovalDocument::where([
                        'EXTENSION_REQUEST_APPROVAL_ID' => $nextApproval->first()->EXTENSION_REQUEST_APPROVAL_ID
                    ])->delete();

                    if($request->IS_FIMM == false || $request->IS_FIMM == 'false') {
                        $query->update(['RETURN_DATELINE' => null]);
                    }

                    DB::connection('module0')->table('NOTIFICATION')->insert([
                        'NOTIFICATION_GROUP_ID' => $nextApproval->first()->APPROVAL_GROUP_ID,
                        'PROCESS_FLOW_ID' => 58,
                        'NOTIFICATION_STATUS' => intval(0),
                        'REMARK' => "(EXT TIME) New entry pending approval for {$distCode}",
                        'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                        'LOCATION' => $location
                    ]);

                }

            }else if($request->STATUS == 'Returned to creator') {

                if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
                    $location = str_replace('fimm-', '', $location);
                    if(strtoupper($request->APPROVAL_GROUP_ID) == intval(1)) {
                        $extensionRequestApproval->extensionRequest()->update(['EXTENSION_STATUS_ID' => $request->TS_ID]);
                        $extensionRequestApproval->update(['APPROVAL_PUBLISH_STATUS' => true]);
                        $location .= "-exchange";

                        DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => 3,
                            'DISTRIBUTOR_ID' => $extensionRequestApproval->extensionRequest->DISTRIBUTOR_ID,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(EXT TIME) Your request has been returned.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => str_replace('fimm-', '', $location)
                        ]);
                    }

                }else {
                    // $previousApprover = ExtensionRequestApproval::where('EXTENSION_REQUEST_ID', $extensionRequestApproval->EXTENSION_REQUEST_ID)->latest()->skip(1)->first();
                    $approver = ExtensionRequestApproval::where('EXTENSION_REQUEST_ID', $extensionRequestApproval->EXTENSION_REQUEST_ID)->latest()->get();
                    $approverIndex = $approver->search(function($item) use($extensionRequestApproval){
                        return $item->EXTENSION_REQUEST_APPROVAL_ID === $extensionRequestApproval->EXTENSION_REQUEST_APPROVAL_ID;
                    });
                    $previousApprover = $approver->skip($approverIndex + 1)->first();
                    // if($previousApprover->IS_FIMM === false) {
                    //     $location .= "-history";
                    // }
                    $updatePreviousApprover = ['TS_ID' => $request->TS_ID, 'APPROVAL_PUBLISH_STATUS' => false];

                    $previousApprover->withoutEvents(function() use($previousApprover, $updatePreviousApprover){
                        $previousApprover->update($updatePreviousApprover);
                    });

                    // RD returned to dist admin
                    // if($request->APPROVAL_GROUP_ID == 4) {
                    if($request->APPROVAL_GROUP_ID == 5) {
                        // $previousApprover = $approver->skip($approverIndex)->first();

                        $updatePreviousApprover = ['TS_ID' => $request->TS_ID, 'APPROVAL_PUBLISH_STATUS' => false];

                        $previousApprover->withoutEvents(function() use($previousApprover, $updatePreviousApprover){
                            $previousApprover->update($updatePreviousApprover);
                        });

                        $extensionRequestApproval->extensionRequest()->update(['EXTENSION_STATUS_ID' => $request->TS_ID]);
                        $extensionRequestApproval->update(['APPROVAL_PUBLISH_STATUS' => true]);
                    }

                    if($request->has('RETURN_DATELINE') && $approvalLevelId == intval(85)) {
                        $request->merge(['RETURN_DATELINE' => Carbon::parse($request->RETURN_DATELINE)->toDateString()]);
                        $extensionRequestApproval->extensionRequest()->update($request->only('RETURN_DATELINE'));
                        $distLocation = str_replace('fimm-', '', $location).'-exchange';

                        // Notify Dist Admin & Manager
                        DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                            [
                                'NOTIFICATION_GROUP_ID' => 1,
                                'DISTRIBUTOR_ID' => $extensionRequestApproval->extensionRequest->DISTRIBUTOR_ID,
                                'PROCESS_FLOW_ID' => 58,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(EXT TIME) Your request has been returned.",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => $distLocation
                            ],
                            [
                                'NOTIFICATION_GROUP_ID' => 3,
                                'DISTRIBUTOR_ID' => $extensionRequestApproval->extensionRequest->DISTRIBUTOR_ID,
                                'PROCESS_FLOW_ID' => 58,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(EXT TIME) Your request has been returned.",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => $distLocation
                            ]
                        ]);

                    }

                    DB::connection('module0')->table('NOTIFICATION')->insert([
                        'NOTIFICATION_GROUP_ID' => $previousApprover->APPROVAL_GROUP_ID,
                        'PROCESS_FLOW_ID' => 58,
                        'NOTIFICATION_STATUS' => intval(0),
                        'REMARK' => "(EXT TIME) Application for {$distCode} has been returned.",
                        'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                        'LOCATION' => $location
                    ]);
                }
            }else if($request->STATUS == 'Rejected') {

                $extensionRequestApproval->extensionRequest()->update(['EXTENSION_STATUS_ID' => $request->TS_ID]);

                // If FIMM
                if(filter_var($extensionRequestApproval->IS_FIMM, FILTER_VALIDATE_BOOL)) {

                    $approvalsQuery = ExtensionRequestApproval::where([
                        'EXTENSION_REQUEST_ID' => $request->EXTENSION_REQUEST_ID,
                        'IS_SUBSEQUENT' => false
                    ]);

                    $approvalsQuery->update($request->only('TS_ID'));

                    $approvals = $approvalsQuery->where(['IS_FIMM' => true])->get();

                    foreach($approvals as $approval) {

                        DB::connection('module0')->table('NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => $approval->APPROVAL_LEVEL_ID,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(EXT TIME) Entry for {$distCode} has been Rejected.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => $location
                        ]);
                    }

                    DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                        'NOTIFICATION_GROUP_ID' => 1,
                        'DISTRIBUTOR_ID' => $extensionRequestApproval->extensionRequest->DISTRIBUTOR_ID,
                        'PROCESS_FLOW_ID' => 58,
                        'NOTIFICATION_STATUS' => intval(0),
                        'REMARK' => "(EXT TIME) Your entry has been rejected.",
                        'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                        'LOCATION' => str_replace('fimm-', '', $location)
                    ]);

                }

                DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                    'NOTIFICATION_GROUP_ID' => 3,
                    'DISTRIBUTOR_ID' => $extensionRequestApproval->extensionRequest->DISTRIBUTOR_ID,
                    'PROCESS_FLOW_ID' => 58,
                    'NOTIFICATION_STATUS' => intval(0),
                    'REMARK' => "(EXT TIME) Your entry has been rejected.",
                    'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                    'LOCATION' => str_replace('fimm-', '', $location)
                ]);

            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully created data'
            ], 200);

        }catch(\Illuminate\Database\QueryException $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], 400);

        }

    }

    public function subsequentIndex(Request $request)
    {

        $params = collect(json_decode(base64_decode($request->params)));

        $TS_PARAM = [];
        if(isset($params['STATUS']) && ($params['STATUS'] !== 'null' || !is_null($params['STATUS']))) {
            $TS_PARAM = TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID');
        }

        $data = SubsequentExtensionRequest::with(['taskStatus', 'distributor', 'author'])
        ->when($params->has('EXTENSION_TYPE') && ($params['EXTENSION_TYPE'] !== 'null' || !is_null($params['EXTENSION_TYPE'])), function($query) use($params){
            return $query->where('EXTENSION_TYPE', 'LIKE', "%{$params['EXTENSION_TYPE']}%");
        })->when(isset($params['DIST_NO']) && ($params['DIST_NO'] !== 'null' || !is_null($params['DIST_NO'])), function($query) use($params){
            return $query->whereHas('distributor', function($query) use($params) {
                return $query->where('DIST_CODE', 'LIKE', "%{$params['DIST_NO']}%");
            });
        })->when(array_key_exists('DIST_NAME', $params->toArray()) && ($params['DIST_NAME'] != '' && $params['DIST_NAME'] != 'null'), function($query) use($params){
            return $query->whereHas('distributor', function($query) use($params) {
                return $query->where('DIST_NAME', 'LIKE', "%{$params['DIST_NAME']}%");
            });
        })
        ->when($params['IS_FIMM'], function($query) use($params) {

                return $query->when($params['USER_GROUP_ID'] == 1, function($query) use($params){
                    return $query->with('ceoApproval.taskStatus')->whereHas('ceoApproval', function($query) use($params){
                        return $query->whereIn('TS_ID', TaskStatus::whereIn('TS_PARAM', ['PENDING', 'DRAFT', 'RETURNED', 'APPROVED'])
                        ->when($params['STATUS'] !== 'null' || !is_null($params['STATUS']), function($query) use($params){
                            return $query->where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%");
                        })
                        ->pluck('TS_ID'))
                        ->where([
                            // 'APPROVAL_PUBLISH_STATUS' => false,
                            'IS_FIMM' => true,
                            'IS_SUBSEQUENT' => true
                        ]);
                    });
                })->when($params['USER_GROUP_ID'] == 3, function($query) use($params){
                    return $query->with('gmApproval.taskStatus')->whereHas('gmApproval', function($query) use($params){
                        return $query->whereIn('TS_ID', TaskStatus::whereIn('TS_PARAM', ['PENDING', 'DRAFT', 'RETURNED', 'APPROVED'])
                        ->when($params['STATUS'] !== 'null' || !is_null($params['STATUS']), function($query) use($params){
                            return $query->where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%");
                        })
                        ->pluck('TS_ID'))
                        ->where([
                            // 'APPROVAL_PUBLISH_STATUS' => false,
                            'IS_FIMM' => true,
                            'IS_SUBSEQUENT' => true
                        ]);
                    });
                })->when($params['USER_GROUP_ID'] == 2, function($query) use($params){
                    return $query->with('rdHodApproval.taskStatus')->whereHas('rdHodApproval', function($query) use($params){
                        return $query->whereIn('TS_ID', TaskStatus::whereIn('TS_PARAM', ['PENDING', 'DRAFT', 'RETURNED', 'APPROVED'])
                        ->when($params['STATUS'] !== 'null' || !is_null($params['STATUS']), function($query) use($params){
                            return $query->where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%");
                        })
                        ->pluck('TS_ID'))
                        ->where([
                            // 'APPROVAL_PUBLISH_STATUS' => false,
                            'IS_FIMM' => true,
                            'IS_SUBSEQUENT' => true
                        ]);
                    });
                // })->when($params['USER_GROUP_ID'] == 4, function($query) use($params){
                })->when($params['USER_GROUP_ID'] == 5, function($query) use($params){
                    return $query->with('rdApproval.taskStatus')->whereHas('rdApproval', function($query) use($params){
                        return $query->whereIn('TS_ID', TaskStatus::whereIn('TS_PARAM', ['PENDING', 'DRAFT', 'RETURNED', 'APPROVED'])
                        ->when($params['STATUS'] !== 'null' || !is_null($params['STATUS']), function($query) use($params){
                            return $query->where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%");
                        })
                        ->pluck('TS_ID'))
                        ->where([
                            // 'APPROVAL_PUBLISH_STATUS' => false,
                            'IS_FIMM' => true,
                            'IS_SUBSEQUENT' => true
                        ]);
                    });
                });
        }, function($query) use($params, $TS_PARAM) {
            // return distributor list
            return $query->when(strtoupper($params['USER_GROUP_ID']) == 1, function($query) use($params){
                return $query->with('managerApproval.taskStatus')
                ->whereHas('managerApproval', function($query) use($params){
                    return $query->whereIn('TS_ID', TaskStatus::whereIn('TS_PARAM', ['PENDING', 'DRAFT', 'RETURNED', 'APPROVED'])
                    // return $query->whereIn('TS_ID', TaskStatus::whereIn('TS_PARAM', ['PENDING', 'DRAFT'])
                    ->when($params['STATUS'] !== 'null' || !is_null($params['STATUS']), function($query) use($params){
                        return $query->where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%");
                    })
                    ->pluck('TS_ID'))->where([
                        // 'APPROVAL_PUBLISH_STATUS' => false,
                        'IS_FIMM' => false,
                        'IS_SUBSEQUENT' => true
                    ]);
                })->orWhereHas('rdApproval', function($query) {
                    return $query->where('TS_ID', TaskStatus::where('TS_REMARK', 'RETURNED TO CREATOR')->first()->TS_ID)->where(['APPROVAL_PUBLISH_STATUS' => true, 'IS_FIMM' => true]);
                });
            }, function($query) use($params, $TS_PARAM) {
                return $query->when(isset($params['STATUS']) && ($params['STATUS'] !== 'null' || !is_null($params['STATUS'])), function($query) use($TS_PARAM){
                    return $query->whereIn('TS_ID', $TS_PARAM);
                });
            })->where($params->only('DISTRIBUTOR_ID')->toArray());
        })
        ->latest('SUBMISSION_DATE')->get();

        if($params->has('GROUP') && $params['GROUP'] == 'true') {
            $data = $data->groupBy('distributor.DIST_NAME');

            $items = [];
            foreach($data as $key => $item) {
                $items[] = [
                    'DIST_NAME' => $key,
                    'DIST_CODE' => $item[0]['distributor']['DIST_CODE'],
                    'COUNT' => $item->count()
                ];
            }

            $data = $items;
        }else{

            $response = [];

            foreach($data as $item) {
                //  $status based on user level

                $itemStatus = null;
                $loadAmendment = null;

                if(filter_var($params['IS_FIMM'], FILTER_VALIDATE_BOOL)) {

                    // if($params['USER_GROUP_ID'] == 4) {
                    if($params['USER_GROUP_ID'] == 5) {
                        $itemStatus = $item->rdApproval;
                        $loadAmendment = 'rdHodApproval';
                    }else {
                        $itemStatus = $item->rdHodApproval;
                        $loadAmendment = 'gmApproval';
                    }

                }else {

                    if($params['USER_GROUP_ID'] == 3) {
                        $itemStatus = $item;
                    }else {
                        $itemStatus = $item->managerApproval;
                    }

                    $loadAmendment = 'rdApproval';

                }

                if($itemStatus->taskStatus->TS_PARAM == 'RETURNED') {
                    $item->load($loadAmendment);
                }

                $response[] = $item;

            }

            $data = $response;

        }

        return response()->json([
            'data' => $data,
            'params' => $params,
        ], 200);

    }

    public function storeSubsequent(Request $request)
    {
        DB::beginTransaction();

        try{

            $request->EXTENSION_END_DATE = Carbon::parse($request->EXTENSION_END_DATE)->toDateString();
            $request->merge([
                'TS_ID' => DB::connection('module0')->table('TASK_STATUS')->where('TS_PARAM', $request->TS_ID)->first()->TS_ID,
                'SUBMISSION_DATE' => $request->EXTENSION_STATUS_ID != 1 ? Carbon::now()->toDateTimeString() : null
            ]);

            if(!$request->has('OTHER_EXTENSION_TYPE')) {
                $request->merge(['OTHER_EXTENSION_TYPE' => null]);
            }

            $extensionRequest = SubsequentExtensionRequest::create($request->only([
                'EXTENSION_TYPE',
                'EXTENSION_REQUEST_ID',
                'OTHER_EXTENSION_TYPE',
                'DISTRIBUTOR_ID',
                'JUSTIFICATION',
                'EXTENSION_END_DATE',
                'TS_ID',
                'SUBMISSION_DATE',
                'CREATED_BY'
            ]));

            if($request->hasAny('FILE')) {

                foreach($request->allFiles() as $key => $val) {

                    foreach($val as $item){

                        $contents = $item->openFile()->fread($item->getSize());

                        $input = [
                            'SUBSEQUENT_EXTENSION_REQUEST_ID' => $extensionRequest->SUBSEQUENT_EXTENSION_REQUEST_ID,
                            'IS_ACTION_PLAN' => $key == 'ACTION_FILE' ?? false,
                            'DOCUMENT_NAME' => $item->getClientOriginalName(),
                            'DOCUMENT_BLOB' => $contents,
                            'DOCUMENT_SIZE' => $item->getSize(),
                            'DOCUMENT_TYPE' => $item->getClientOriginalExtension(),
                            'SUBMISSION_DATE' => $request->EXTENSION_STATUS_ID != 1 ? Carbon::now()->toDateTimeString() : null
                        ];

                        SubsequentExtensionRequestDocument::create($input);

                    }

                }
            }

            if($request->TS_ID != 1) {

                $firstApproval = DB::connection('module0')->table('DIST_APPROVAL_LEVEL')->where(['DIST_APPR_PROCESSFLOW_ID' => intval(58), 'DIST_APPR_INDEX' => intval(1)])->first();

                ExtensionRequestApproval::create([
                    'APPROVAL_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                    'APPROVAL_LEVEL_ID' => $firstApproval->DIST_APPROVAL_LEVEL_ID,
                    'EXTENSION_REQUEST_ID' => $extensionRequest->getKey(),
                    'TS_ID' => $request->TS_ID,
                    'IS_SUBSEQUENT' => true
                ]);

                DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                    'NOTIFICATION_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                    'DISTRIBUTOR_ID' => $extensionRequest->DISTRIBUTOR_ID,
                    'PROCESS_FLOW_ID' => $firstApproval->DIST_APPR_PROCESSFLOW_ID,
                    'NOTIFICATION_STATUS' => intval(0),
                    'REMARK' => '(SUB EXT TIME) New Entry Pending for Approval.',
                    'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                    'LOCATION' => 'subsequent-extension-request'
                ]);

            }

            DB::commit();

            return response()->json([
                'data' => $request->all(),
                'auth' => auth()->user(),
                'output' => $extensionRequest
            ], 200);

        }catch (\Illuminate\Database\QueryException $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);

        }catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 400);

        }

    }

    public function updateSubsequentExtensionRequest(Request $request, $id)
    {

        DB::beginTransaction();

        try{

            $extensionRequest = SubsequentExtensionRequest::findOrFail($id);

            $request->EXTENSION_END_DATE = Carbon::parse($request->EXTENSION_END_DATE)->toDateString();
            $request->merge(['TS_ID' => TaskStatus::where('TS_PARAM', $request->TS_ID)->first()->getKey(), 'EXTENSION_END_DATE' => Carbon::parse($request->EXTENSION_END_DATE)->toDateTimeString()]);

            tap($extensionRequest)->update($request->only(['TS_ID', 'EXTENSION_TYPE', 'OTHER_EXTENSION_TYPE', 'JUSTIFICATION', 'EXTENSION_END_DATE']));

            if($request->TS_ID != intval(1)){
                $extensionRequest->update(['SUBMISSION_DATE' => Carbon::now()->toDateTimeString()]);
            }

            if($request->hasAny('FILE')) {

                foreach($request->allFiles() as $key => $val) {

                    foreach($val as $item){

                        $contents = $item->openFile()->fread($item->getSize());

                        $input = [
                            'SUBSEQUENT_EXTENSION_REQUEST_ID' => $id,
                            'IS_ACTION_PLAN' => $key == 'ACTION_FILE' ?? false,
                            'DOCUMENT_NAME' => $item->getClientOriginalName(),
                            'DOCUMENT_BLOB' => $contents,
                            'DOCUMENT_SIZE' => $item->getSize(),
                            'DOCUMENT_TYPE' => $item->getClientOriginalExtension()
                        ];

                        SubsequentExtensionRequestDocument::create($input);

                    }

                }

            }

            if(!$extensionRequest->managerApproval()->exists()){

                if($request->TS_ID == 15) {

                    tap($extensionRequest)->update(['SUBMISSION_DATE' => Carbon::now()->toDateTimeString()]);

                    $firstApproval = DB::connection('module0')->table('DIST_APPROVAL_LEVEL')->where(['DIST_APPR_PROCESSFLOW_ID' => intval(58), 'DIST_APPR_INDEX' => intval(1)])->first();

                    ExtensionRequestApproval::create([
                        'APPROVAL_GROUP_ID' => $firstApproval->DIST_APPR_GROUP_ID,
                        'APPROVAL_LEVEL_ID' => $firstApproval->DIST_APPROVAL_LEVEL_ID,
                        'EXTENSION_REQUEST_ID' => $id,
                        'TS_ID' => $request->TS_ID,
                        'IS_SUBSEQUENT' => true
                    ]);

                }

            }else {
                $extensionRequest->managerApproval()->update(['TS_ID' => $request->TS_ID, 'APPROVAL_PUBLISH_STATUS' => false]);
            }

            // Resubmit Notification to Manager
            if($request->TS_ID == 15) {
                DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                    'NOTIFICATION_GROUP_ID' => 1,
                    'DISTRIBUTOR_ID' => $extensionRequest->DISTRIBUTOR_ID,
                    'PROCESS_FLOW_ID' => 58,
                    'NOTIFICATION_STATUS' => intval(0),
                    'REMARK' => '(SUB EXT TIME) New Entry Pending for Approval.',
                    'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                    'LOCATION' => 'subsequent-extension-request'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully update'
            ], 200);

        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Not found'
            ], 404);

        }

    }

    public function subsequentExtensionRequestApproval(Request $request)
    {

        if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
            $approvalLevelId = 21;
            $approvalIndex = 1;
        }else {

            switch($request->APPROVAL_GROUP_ID){
                // case 4:
                case 5:
                    $approvalLevelId = 85;
                    $approvalIndex = 1;
                break;
                case 2:
                    $approvalLevelId = 86;
                    $approvalIndex = 2;
                break;
                case 3:
                    $approvalLevelId = 87;
                    $approvalIndex = 3;
                break;
                case 1:
                    $approvalLevelId = 88;
                    $approvalIndex = 4;
                break;
                default:
                return response()->json(['message' => 'error'], 400);
            }

        }

        $extensionRequestApproval = ExtensionRequestApproval::query()->where([
            'EXTENSION_REQUEST_ID' => $request->EXTENSION_REQUEST_ID,
            'APPROVAL_GROUP_ID' => $request->APPROVAL_GROUP_ID,
            'APPROVAL_LEVEL_ID' => $approvalLevelId,
            'IS_SUBSEQUENT' => true
        ]);

        if(!$extensionRequestApproval->exists()) {
            return response()->json([
                'message' => 'not found'
            ], 400);
        }

        $extensionRequestApproval = $extensionRequestApproval->first();

        $request->merge([
            'TS_ID' =>  TaskStatus::where(strtoupper($request->STATUS) == 'RETURNED TO CREATOR' ? 'TS_REMARK' : 'TS_PARAM', $request->STATUS)->first()->getKey(),
            'APPROVAL_PUBLISH_STATUS' => strtoupper($request->STATUS) == 'APPROVED' || strtoupper($request->STATUS) == 'REJECTED' || strtoupper($request->STATUS) == 'RETURNED TO CREATOR' ? true : false,
            'APPROVAL_DATE' => strtoupper($request->STATUS) == 'APPROVED' ? Carbon::today()->toDateString() : null
        ]);

        DB::beginTransaction();

        try{

            if(strtoupper($request->STATUS) === 'DRAFT') {

                $extensionRequestApproval->withoutEvents(function() use($request, $extensionRequestApproval) {

                    $extensionRequestApproval->update($request->only([
                        'TS_ID',
                        'APPROVAL_PUBLISH_STATUS',
                        'APPROVAL_REMARK',
                        'APPROVAL_DATE',
                        'CREATED_BY'
                    ]));

                });

            } else {

                $extensionRequestApproval->update($request->only([
                    'TS_ID',
                    'APPROVAL_PUBLISH_STATUS',
                    'APPROVAL_REMARK',
                    'APPROVAL_DATE',
                    'CREATED_BY'
                ]));

            }

            if($request->hasAny('FILE')) {

                foreach($request->allFiles() as $key => $val) {

                    foreach($val as $item){

                        $contents = $item->openFile()->fread($item->getSize());

                        $input = [
                            'EXTENSION_REQUEST_APPROVAL_ID' => $extensionRequestApproval->getKey(),
                            'DOCUMENT_NAME' => $item->getClientOriginalName(),
                            'DOCUMENT_BLOB' => $contents,
                            'DOCUMENT_SIZE' => $item->getSize(),
                            'DOCUMENT_TYPE' => $item->getClientOriginalExtension()
                        ];

                        ExtensionRequestApprovalDocument::create($input);

                    }

                }
            }

            $distCode = $extensionRequestApproval->subsequentExtensionRequest->distributor->DIST_CODE;

            if($request->STATUS == 'Approved') {

                $query = $extensionRequestApproval->subsequentExtensionRequest;

                if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
                    $nextApproval = $query->rdApproval();
                }else{

                    switch($request->APPROVAL_GROUP_ID){
                        // case 4:
                        case 5:
                            $nextApproval = $query->rdHodApproval();
                        break;
                        case 2:
                            $nextApproval = $query->gmApproval();
                        break;
                        case 3:
                            $nextApproval = $query->ceoApproval();
                        break;
                        default:
                        $nextApproval = $query->ceoApproval();
                    }

                }


                if(!$nextApproval->exists() || $approvalIndex == 4) {

                    if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
                        if(strtoupper($request->APPROVAL_GROUP_ID) == intval(1)) {
                            $secondApproval = DB::connection('module0')->table('APPROVAL_LEVEL')->where(['APPR_PROCESSFLOW_ID' => intval(58), 'APPR_INDEX' => intval(1)])->first();
                        }
                    }else{
                        if($approvalIndex == 4){
                            $extensionRequestApproval->subsequentExtensionRequest()->update(['TS_ID' => $request->TS_ID, 'EXTENSION_APPROVAL_DATE' => Carbon::now()->toDateString()]);
                        }else {
                            $secondApproval = DB::connection('module0')->table('APPROVAL_LEVEL')->where(['APPR_PROCESSFLOW_ID' => intval(58), 'APPR_INDEX' => intval($approvalIndex+=1)])->first();
                        }
                    }

                    if($approvalLevelId < 88) {

                        if($request->APPROVAL_GROUP_ID == 2 && $query->EXTENSION_TYPE !== 'COMMENCEMENT OF OPERATION') {

                            DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                                'NOTIFICATION_GROUP_ID' => 3,
                                'DISTRIBUTOR_ID' => $query->DISTRIBUTOR_ID,
                                'PROCESS_FLOW_ID' => 58,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(SUB EXT TIME) Your request has been approved by Registration Head of Department.",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => 'subsequent-extension-request'
                            ]);

                            DB::connection('module0')->table('NOTIFICATION')->insert([
                                // 'NOTIFICATION_GROUP_ID' => 4,
                                'NOTIFICATION_GROUP_ID' => 5,
                                'PROCESS_FLOW_ID' => $secondApproval->APPR_PROCESSFLOW_ID,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(SUB EXT TIME) Entry for {$distCode} has been Approved",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => 'fimm-subsequent-extension-request'
                            ]);

                            $extensionRequestApproval->subsequentExtensionRequest()->update(['TS_ID' => $request->TS_ID, 'EXTENSION_APPROVAL_DATE' => now()]);

                        } else {

                            ExtensionRequestApproval::create([
                                'APPROVAL_GROUP_ID' => $secondApproval->APPR_GROUP_ID,
                                'APPROVAL_LEVEL_ID' => $secondApproval->APPROVAL_LEVEL_ID,
                                'EXTENSION_REQUEST_ID' => $request->EXTENSION_REQUEST_ID,
                                'TS_ID' => TaskStatus::where('TS_PARAM', 'PENDING')->first()->getKey(),
                                'IS_SUBSEQUENT' => true,
                                'APPROVAL_PUBLISH_STATUS' => false,
                                'IS_FIMM' => true
                            ]);

                            DB::connection('module0')->table('NOTIFICATION')->insert([
                                'NOTIFICATION_GROUP_ID' => $secondApproval->APPR_GROUP_ID,
                                'PROCESS_FLOW_ID' => $secondApproval->APPR_PROCESSFLOW_ID,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(SUB EXT TIME) New entry pending approval for {$distCode}",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => 'subsequent-extension-request'
                            ]);

                        }

                    }else {
                        DB::connection('module0')->table('NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => 85,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(SUB EXT TIME) Request for {$distCode} has been approved.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => 'subsequent-extension-request'
                        ]);

                        DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => 3,
                            'DISTRIBUTOR_ID' => $query->DISTRIBUTOR_ID,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(SUB EXT TIME) Your request has been approved.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => 'subsequent-extension-request'
                        ]);
                    }

                }else {

                    $nextApproval->update(['TS_ID' => TaskStatus::where('TS_PARAM', 'PENDING')->first()->getKey(), 'APPROVAL_PUBLISH_STATUS' => false]);

                    if($request->IS_FIMM == false || $request->IS_FIMM == 'false') {
                        $query->update(['RETURN_DATELINE' => null]);
                    }

                    DB::connection('module0')->table('NOTIFICATION')->insert([
                        'NOTIFICATION_GROUP_ID' => $nextApproval->first()->APPROVAL_GROUP_ID,
                        'PROCESS_FLOW_ID' => 58,
                        'NOTIFICATION_STATUS' => intval(0),
                        'REMARK' => "(SUB EXT TIME) New entry pending approval for {$distCode}",
                        'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                        'LOCATION' => 'subsequent-extension-request'
                    ]);

                }

            }else if($request->STATUS == 'Returned to creator') {
                if($request->IS_FIMM == false || $request->IS_FIMM == 'false'){
                    if(strtoupper($request->APPROVAL_GROUP_ID) == intval(1)) {
                        $extensionRequestApproval->subsequentExtensionRequest()->update(['TS_ID' => $request->TS_ID]);
                        $extensionRequestApproval->update(['APPROVAL_PUBLISH_STATUS' => true]);

                        DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => 3,
                            'DISTRIBUTOR_ID' => $extensionRequestApproval->subsequentExtensionRequest->DISTRIBUTOR_ID,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(SUB EXT TIME) Your request has been returned.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => 'subsequent-extension-request'
                        ]);
                    }
                }else {
                    // $previousApprover = ExtensionRequestApproval::where('EXTENSION_REQUEST_ID', $extensionRequestApproval->EXTENSION_REQUEST_ID)->latest()->skip(1)->first();
                    $approver = ExtensionRequestApproval::where('EXTENSION_REQUEST_ID', $extensionRequestApproval->EXTENSION_REQUEST_ID)->latest()->get();
                    $approverIndex = $approver->search(function($item) use($extensionRequestApproval){
                        return $item->EXTENSION_REQUEST_APPROVAL_ID === $extensionRequestApproval->EXTENSION_REQUEST_APPROVAL_ID;
                    });
                    $previousApprover = $approver->skip($approverIndex + 1)->first();
                    $updatePreviousApprover = ['TS_ID' => $request->TS_ID, 'APPROVAL_PUBLISH_STATUS' => false];

                    $previousApprover->withoutEvents(function() use($previousApprover, $updatePreviousApprover){
                        $previousApprover->update($updatePreviousApprover);
                    });

                    // RD returned to dist admin
                    // if($request->APPROVAL_GROUP_ID == 4) {
                    if($request->APPROVAL_GROUP_ID == 5) {
                        $previousApprover = $approver->skip($approverIndex)->first();
                        $updatePreviousApprover = ['TS_ID' => $request->TS_ID, 'APPROVAL_PUBLISH_STATUS' => false];

                        $previousApprover->withoutEvents(function() use($previousApprover, $updatePreviousApprover){
                            $previousApprover->update($updatePreviousApprover);
                        });

                        $extensionRequestApproval->subsequentExtensionRequest()->update(['TS_ID' => $request->TS_ID]);
                        $extensionRequestApproval->update(['APPROVAL_PUBLISH_STATUS' => true]);
                    }

                    if($request->has('RETURN_DATELINE') && $approvalLevelId == intval(85)) {
                        $request->merge(['RETURN_DATELINE' => Carbon::parse($request->RETURN_DATELINE)->toDateString()]);
                        $extensionRequestApproval->subsequentExtensionRequest()->update($request->only('RETURN_DATELINE'));

                        DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                            [
                                'NOTIFICATION_GROUP_ID' => 1,
                                'DISTRIBUTOR_ID' => $extensionRequestApproval->subsequentExtensionRequest->DISTRIBUTOR_ID,
                                'PROCESS_FLOW_ID' => 58,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(SUB EXT TIME) Your request has been returned.",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => 'subsequent-extension-request'
                            ],
                            [
                                'NOTIFICATION_GROUP_ID' => 3,
                                'DISTRIBUTOR_ID' => $extensionRequestApproval->subsequentExtensionRequest->DISTRIBUTOR_ID,
                                'PROCESS_FLOW_ID' => 58,
                                'NOTIFICATION_STATUS' => intval(0),
                                'REMARK' => "(SUB EXT TIME) Your request has been returned.",
                                'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                                'LOCATION' => 'subsequent-extension-request'
                            ]

                        ]);
                    }

                    DB::connection('module0')->table('NOTIFICATION')->insert([
                        'NOTIFICATION_GROUP_ID' => $previousApprover->APPROVAL_GROUP_ID,
                        'PROCESS_FLOW_ID' => 58,
                        'NOTIFICATION_STATUS' => intval(0),
                        'REMARK' => "(SUB EXT TIME) Application for {$distCode} has been returned.",
                        'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                        'LOCATION' => 'subsequent-extension-request'
                    ]);
                }
            }else if($request->STATUS == 'Rejected') {
                $extensionRequestApproval->subsequentExtensionRequest()->update(['TS_ID' => $request->TS_ID]);

                // If FIMM
                if(filter_var($extensionRequestApproval->IS_FIMM, FILTER_VALIDATE_BOOL)) {

                    $approvalsQuery = ExtensionRequestApproval::where([
                        'EXTENSION_REQUEST_ID' => $request->EXTENSION_REQUEST_ID,
                        'IS_SUBSEQUENT' => true
                    ]);

                    $approvalsQuery->update($request->only('TS_ID'));

                    $approvals = $approvalsQuery->where(['IS_FIMM' => true])->get();

                    foreach($approvals as $approval) {

                        DB::connection('module0')->table('NOTIFICATION')->insert([
                            'NOTIFICATION_GROUP_ID' => $approval->APPROVAL_LEVEL_ID,
                            'PROCESS_FLOW_ID' => 58,
                            'NOTIFICATION_STATUS' => intval(0),
                            'REMARK' => "(EXT TIME) Entry for {$distCode} has been Rejected.",
                            'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                            'LOCATION' => 'fimm-subsequent-extension-request'
                        ]);
                    }

                    DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                        'NOTIFICATION_GROUP_ID' => 1,
                        'DISTRIBUTOR_ID' => $extensionRequestApproval->subsequentExtensionRequest->DISTRIBUTOR_ID,
                        'PROCESS_FLOW_ID' => 58,
                        'NOTIFICATION_STATUS' => intval(0),
                        'REMARK' => "(EXT TIME) Your entry has been rejected.",
                        'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                        'LOCATION' => 'subsequent-extension-request'
                    ]);

                }

                DB::connection('module0')->table('DISTRIBUTOR_NOTIFICATION')->insert([
                    'NOTIFICATION_GROUP_ID' => 3,
                    'DISTRIBUTOR_ID' => $extensionRequestApproval->subsequentExtensionRequest->DISTRIBUTOR_ID,
                    'PROCESS_FLOW_ID' => 58,
                    'NOTIFICATION_STATUS' => intval(0),
                    'REMARK' => "(SUB EXT TIME) Your approval has been rejected.",
                    'NOTIFICATION_DATE' => Carbon::now()->toDateTimeString(),
                    'LOCATION' => 'subsequent-extension-request'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully created data'
            ], 200);

        }catch(\Illuminate\Database\QueryException $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], 400);

        }

    }

    public function deleteApprovalDocs($id)
    {
        try{
            $item = ExtensionRequestApprovalDocument::findOrFail($id);

            if($item->delete()) {
                return response()->json([
                    'message' => 'Successfully delete'
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to delete'
            ], 400);

        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'File might been deleted'
            ], 400);
        }
    }

    public function deleteDocs(Request $request, $id)
    {
        if($request->IS_SUBSEQUENT) {
            $item = SubsequentExtensionRequestDocument::query()->where(['SUBSEQUENT_EXTENSION_REQUEST_DOCUMENT_ID' => $id, 'IS_ACTION_PLAN' => $request->IS_ACTION_PLAN]);
        }else {
            $item = ExtensionRequestDocument::query()->where(['EXTENSION_REQUEST_DOCUMENT_ID' => $id, 'IS_ACTION_PLAN' => $request->IS_ACTION_PLAN]);
        }

        if($item->delete()) {
            return response()->json([
                'message' => 'Successfully delete'
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to delete'
        ], 400);
    }

    public function history($id)
    {
        $extensionRequest = ExtensionRequest::find($id);

        $data = ExtensionRequest::with('taskStatus')->where('EXTENSION_TYPE', $extensionRequest->EXTENSION_TYPE)->get();

        return response()->json([
            'message' => 'Successfully fetch data',
            'data' => $extensionRequest,
            'history' => $data
        ], 200);
    }

    public function extensionRequestApprovalList(Request $request)
    {

        $params = collect($request->all());

        $data = ExtensionRequest::with(['taskStatus', 'distributor', 'author'])
        ->when($params['IS_FIMM'] == 'true', function($query) use($params) {

                return $query->when($params['USER_GROUP_ID'] == 1, function($query) use($params){
                    return $query->with('ceoApproval.taskStatus')->whereHas('ceoApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                })->when($params['USER_GROUP_ID'] == 3, function($query) use($params){
                    return $query->with('gmApproval.taskStatus')->whereHas('gmApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                })->when($params['USER_GROUP_ID'] == 2, function($query) use($params){
                    return $query->with('rdHodApproval.taskStatus')->whereHas('rdHodApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                // })->when($params['USER_GROUP_ID'] == 4, function($query) use($params){
                })->when($params['USER_GROUP_ID'] == 5, function($query) use($params){
                    return $query->with('rdApproval.taskStatus')->whereHas('rdApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                });
        }, function($query) use($params) {
            // return distributor list
            return $query->when($params['USER_GROUP_ID'] == 1, function($query) use($params){
                return $query->with('managerApproval.taskStatus')
                ->whereHas('managerApproval', function($query) use($params){
                    return $query
                    ->when($params['STATUS'] !== 'null', function($query) use($params){
                        return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                    })
                    ->where(['IS_FIMM' => false, 'IS_SUBSEQUENT' => false]);
                })->orWhereHas('rdApproval', function($query) {
                    return $query->where('TS_ID', TaskStatus::where('TS_REMARK', 'RETURNED TO CREATOR')->first()->TS_ID)->where(['APPROVAL_PUBLISH_STATUS' => true, 'IS_FIMM' => true]);
                });
            })
            ->where($params->only('DISTRIBUTOR_ID')->toArray());
        })
        ->latest('SUBMISSION_DATE')->get();

        return response()->json([
            'data' => $data,
            'params' => $params
        ], 200);

    }

    public function subsequentExtensionRequestApprovalList(Request $request)
    {

        $params = collect($request->all());

        $data = SubsequentExtensionRequest::with(['taskStatus', 'distributor', 'author'])
        ->when($params['IS_FIMM'] == 'true', function($query) use($params) {

                return $query->when($params['USER_GROUP_ID'] == 1, function($query) use($params){
                    return $query->with('ceoApproval.taskStatus')->whereHas('ceoApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                })->when($params['USER_GROUP_ID'] == 3, function($query) use($params){
                    return $query->with('gmApproval.taskStatus')->whereHas('gmApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                })->when($params['USER_GROUP_ID'] == 2, function($query) use($params){
                    return $query->with('rdHodApproval.taskStatus')->whereHas('rdHodApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                // })->when($params['USER_GROUP_ID'] == 4, function($query) use($params){
                })->when($params['USER_GROUP_ID'] == 5, function($query) use($params){
                    return $query->with('rdApproval.taskStatus')->whereHas('rdApproval', function($query) use($params){
                        return $query
                        ->when($params['STATUS'] !== 'null', function($query) use($params){
                            return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                        })
                        ->where(['APPROVAL_PUBLISH_STATUS' => false, 'IS_FIMM' => true, 'IS_SUBSEQUENT' => false]);
                    });
                });
        }, function($query) use($params) {
            // return distributor list
            return $query->when($params['USER_GROUP_ID'] == 1, function($query) use($params){
                return $query->with('managerApproval.taskStatus')
                ->whereHas('managerApproval', function($query) use($params){
                    return $query
                    ->when($params['STATUS'] !== 'null', function($query) use($params){
                        return $query->whereIn('TS_ID', TaskStatus::where('TS_PARAM', 'LIKE', "%{$params['STATUS']}%")->pluck('TS_ID'));
                    })
                    ->where(['IS_FIMM' => false, 'IS_SUBSEQUENT' => false]);
                })->orWhereHas('rdApproval', function($query) {
                    return $query->where('TS_ID', TaskStatus::where('TS_REMARK', 'RETURNED TO CREATOR')->first()->TS_ID)->where(['APPROVAL_PUBLISH_STATUS' => true, 'IS_FIMM' => true]);
                });
            })
            ->where($params->only('DISTRIBUTOR_ID')->toArray());
        })
        ->latest('SUBMISSION_DATE')->get();

        return response()->json([
            'data' => $data,
            'params' => $params
        ], 200);

    }
}
