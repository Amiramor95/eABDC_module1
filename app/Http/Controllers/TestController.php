<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use Storage;
use File;
use Response as Gambar;

class response
{
    public $response;
    public $data;
    public $statusCode;
}

class TestController extends Controller
{
    public function get()
    {
        return Test::all();
    }

    public function getById($id)
    {
        return Test::find($id);
    }

    public function create(Request $request)
    {
        $test = new Test;
        $test->name = $request->name;
        $test->email = $request->email;
        $test->save();

        return response()->json('Data created successfully');
    }

    public function update(Request $request)
    {
        $test = Test::find($request->id);
        $test->name = $request->name;
        $test->email = $request->email;
        $test->save();

        return response()->json('Data updated successfully');
    }

    public function delete(Request $request)
    {
        $test = Test::find($request->id);
        $test->delete();

        return response()->json('Data deleted successfully');
    }

    public function savePhoto(Request $request)
    {
        $p = '1';
        $imageId = '1';
        $image = $request->file('photo');
        $name = $imageId.'.'.$image->getClientOriginalExtension();

        $destinationPath = storage_path('/app/public/images');
        $image->move($destinationPath, $name);
         
        return response()->json(['data'=>"image is uploaded",'url'=>""]);

    }

    public function getPhoto($filename)
    {
        $path = storage_path('app/public/images/'. $filename);
        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Gambar::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

    public function filter(Request $request){
        
    }
}
