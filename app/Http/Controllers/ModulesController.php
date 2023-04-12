<?php

namespace App\Http\Controllers;

use App\Models\Modules;
use Illuminate\Http\Request;

class ModulesController extends Controller
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

    public function get()
    {
        return Modules::all();
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
        $test = new Modules;
        $test->code = $request->code;
        $test->name = $request->name;
        $test->short_name = $request->short_name;
        $test->icon = $request->icon;
        $test->index = $request->index;
        $test->save();

        return response(['message' => 'Data created successfully', 'status' => 200]);
        } catch (\Throwable $th) {
            //throw $th;
            return response(['message' => 'Failed to create module', 'status' => 400]);
        }
        
    }

    public function delete(Request $request)
    {
        try {
            //code...
        $module = Modules::find($request->id);
        $module->delete();

        return response(['message' => 'Data delete successfully', 'status' => 200]);
        } catch (\Throwable $th) {
            //throw $th;
            return response(['message' => 'Failed to delete module', 'status' => 400]);
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
     * @param  \App\Models\Modules  $modules
     * @return \Illuminate\Http\Response
     */
    public function show(Modules $modules)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Modules  $modules
     * @return \Illuminate\Http\Response
     */
    public function edit(Modules $modules)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Modules  $modules
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Modules $modules)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Modules  $modules
     * @return \Illuminate\Http\Response
     */
    public function destroy(Modules $modules)
    {
        //
    }
}
