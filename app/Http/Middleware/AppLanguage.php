<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Illuminate\Support\Facades\Session;

class AppLanguage
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
        // Check header request and determine localizaton
        if($request->hasHeader('App-Language')){
            $locale = $request->header('App-Language');
        }
        elseif(env('DEFAULT_LANGUAGE') != null){
            $locale = env('DEFAULT_LANGUAGE');
        }
        else{
            $locale = 'en';
        }


        if(config('local.status')){
            if (Session::has('locale')  &&array_key_exists( Session::get('locale'),config('local.langauges') ) ) {
                \Illuminate\Support\Facades\App::setLocale(Session::get('locale'));
            }
        }


        // set laravel localization
        App::setLocale($locale);

        // continue request
        return $next($request);
    }
}
