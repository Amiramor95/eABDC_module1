<?php

namespace App\Http\Controllers;

use App\Models\Demo;
use Illuminate\Http\Request;
use App\Helpers\FileUpload;
use App\Helpers\CurrentUser;
class DemoController extends Controller
{
    public function createuser(Request $request)
    {
        try {
            $user = new CurrentUser();
            $result = $user->createUser($request);
            http_response_code(200);
            return response([
                'message' => 'User distributor successfully created.',
            ]);
        }catch (RequestException $r) {
            return response([
                'message' => 'User distributor successfully created.',
            ]);
        }
    }

    public function getUserByEmail(Request $request)
    {
        try {
            $user = new CurrentUser();
            $result = $user->getUserByEmail($request);

            dd($result);
            http_response_code(200);
            return response([
                'message' => 'User distributor successfully created.',
            ]);
        }catch (RequestException $r) {
            return response([
                'message' => 'User distributor successfully created.',
            ]);
        }
    }

    public function storeDemo(Request $request) {
        $demo = new Demo();
        $demo->make = $request->make;
        $demo->model = $request->model;
        $demo->save();

        return $demo;
    }

    public function storeBLOB(Request $request) {
        // dd($request);
        $fileUpload = new FileUpload();
        $result = $fileUpload->uploadDistributorDocument($request);
        // dd($result);
        return 'done';
    }

    public function getDemos(Request $request) {
        $demos = Demo::all();

        return $demos;
    }

    public  function editDemo(Request $request, $id){
        $demo = Demo::where('id',$id)->first();

        $demo->make = $request->get('val_1');
        $demo->model = $request->get('val_2');
        $demo->save();

        return $demo;
    }

    public function deleteDemo(Request $request){
        $demo = Demo::find($request->id)->delete();
    }
}
