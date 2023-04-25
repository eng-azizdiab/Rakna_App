<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (isset(Auth::guard('admin-api')->user()->key) ){
            if (Auth::guard('admin-api')->user()->key != 'admin1234')
//                throw new \Exception();
                return response()->json('Something error');
        }else{
            return \response()->json('Something error');
        }
        return $next($request);
    }
}
