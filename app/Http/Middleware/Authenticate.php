<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use function React\Promise\Stream\first;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            /* if($request->header('Authorization')){
                return route('redirect', ['_token' => $request->header('Authorization')]);
            } */
            
            return route('index');
        }else{
            return redirect()->route('verifyUserAuth');
        }
    }
}
