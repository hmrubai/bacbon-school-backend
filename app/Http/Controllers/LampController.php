<?php

namespace App\Http\Controllers;

use App\Lamp;
use Illuminate\Http\Request;

class LampController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($data)
    {

        $lamp = (array) [
            "user_id" => $data->id,
            "age" => $data->age,
            "organization" => $data->organization,
            "passport" => $data->passport == 'No' ? false : true,
            "reason" => $data->reason,
            "background" => $data->background,
            "contributionProcess" => $data->contributionProcess,
            "remark" => $data->remark
        ];
        return Lamp::create($lamp);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Lamp  $lamp
     * @return \Illuminate\Http\Response
     */
    public function show(Lamp $lamp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Lamp  $lamp
     * @return \Illuminate\Http\Response
     */
    public function edit(Lamp $lamp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Lamp  $lamp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Lamp $lamp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Lamp  $lamp
     * @return \Illuminate\Http\Response
     */
    public function destroy(Lamp $lamp)
    {
        //
    }
}
