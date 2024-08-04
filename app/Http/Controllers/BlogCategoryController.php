<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\blog_category;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = blog_category::get();
        return FacadeResponse::json($categories);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($data)
    {
        return  blog_category::create($data);
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
     * @param  \App\blog_category  $blog_category
     * @return \Illuminate\Http\Response
     */
    public function show(blog_category $blog_category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\blog_category  $blog_category
     * @return \Illuminate\Http\Response
     */
    public function edit(blog_category $blog_category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\blog_category  $blog_category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, blog_category $blog_category)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\blog_category  $blog_category
     * @return \Illuminate\Http\Response
     */
    public function destroy(blog_category $blog_category)
    {
        //
    }
}
