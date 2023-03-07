<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorizeUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $isProgram = false, $isInstitute = false)
    {
        //dd(($request->segment(1).'/'.$request->segment(2)));
        if(!$isProgram && !$isInstitute)
        {
            if(Auth::user()->auth == 5)
                return redirect()->route('accreditor_verify');
        }

        if(filter_var($isProgram, FILTER_VALIDATE_BOOLEAN))
        {
            if(($request->path() == "program/create" || ($request->segment(1).'/'.$request->segment(2)) == 'program/update') && Auth::user()->auth == 4)
                return back()->with('error', 'Unauthorized');
            else if(!in_array(Auth::user()->auth, [1, 3, 4, 6]))
                return back()->with('error', 'Unauthorized');
        }

        if(filter_var($isInstitute, FILTER_VALIDATE_BOOLEAN))
        {
            if(!in_array(Auth::user()->auth, [1, 3, 6]))
                return back()->with('error', 'Unauthorized');
        }
        
        return $next($request);
    }
}
