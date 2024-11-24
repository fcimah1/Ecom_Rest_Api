<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Local
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {


        if(config('local.status')){
            if (Session::has('locale')  &&array_key_exists( Session::get('locale'),config('local.langauges') ) ) {
                App::setLocale(Session::get('locale'));
            }
        }
        return $next($request);
    }
}
