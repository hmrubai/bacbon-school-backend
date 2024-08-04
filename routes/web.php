<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response()->json(['data' => "Welcome to BacBon School! Unauthorized Access!!"], 403);
});

Route::group(['prefix' => 'user','middleware' => ['assign.guard:users','jwt.auth']],function ()
{
	Route::get('/demo','UserController@demo');
});
