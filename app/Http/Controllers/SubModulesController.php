<?php

namespace App\Http\Controllers;

use App\Models\SubModules;
use App\Models\Modules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubModulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            //code...
        $subModules = new SubModules;
        $subModules->modules_id = $request->modules_id;
        $subModules->code = $request->subModuleCode;
        $subModules->name = $request->subModuleName;
        $subModules->save();

        return response(['message' => 'Data created successfully', 'status' => 200]);
        } catch (\Throwable $th) {
            //throw $th;
            return response(['message' => 'Failed to create module', 'status' => 400]);
        }
    }

//     public function getByModuleId($id)
//     {

//         $subModules = Modules::find($id)->subModules;

// foreach ($subModules as $subModule) {
//     //
// }
//     }

    public function get()
    {
        $subModule = DB::table('sub_modules')
            ->select('sub_modules.id','sub_modules.code','sub_modules.name','modules.name as moduleName' )
            ->join('modules', 'modules.id', '=', 'sub_modules.modules_id')

            ->get();
            return $subModule;
    }

    public function delete(Request $request)
    {
        try {
            //code...
        $module = SubModules::find($request->id);
        $module->delete();

        return response(['message' => 'Data delete successfully', 'status' => 200]);
        } catch (\Throwable $th) {
            //throw $th;
            return response(['message' => 'Failed to delete sub modules', 'status' => 400]);
        }
        
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SubModules  $subModules
     * @return \Illuminate\Http\Response
     */
    public function show(SubModules $subModules)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SubModules  $subModules
     * @return \Illuminate\Http\Response
     */
    public function edit(SubModules $subModules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SubModules  $subModules
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubModules $subModules)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SubModules  $subModules
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubModules $subModules)
    {
        //
    }
}
