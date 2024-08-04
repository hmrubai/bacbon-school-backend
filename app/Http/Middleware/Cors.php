<?php

namespace App\Http\Middleware;

use Illuminate\Http\Response;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin' , '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');

        return $response;
        // header('Access-Control-Allow-Origin:  ');
        // header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Authorization, Origin');
        // header('Access-Control-Allow-Methods:  POST, PUT, GET');
        // return $next($request)
        // ->header('Access-Control-Allow-Origin', '*')
        // ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application')
        // ->header('Access-Control-Allow-Methods',  'POST, GET, OPTIONS, PUT, DELETE');
    }
}
