<?php

namespace App\Http\Middleware;

use App\Models\Accreditation;
use Closure;
use Illuminate\Http\Request;

class ValidateRestriction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $tmp = null)
    {   

        if($tmp)
            $isRestricted = Accreditation::find($tmp);
        else
            $isRestricted = Accreditation::find($request->accredlvl);

        if($request->acceptsJson())
            if($isRestricted->restrict)
                return response()->json('Restricted', 403);

        if($isRestricted)
            if($isRestricted->restrict)
                return back()->with('error', 'Restricted');

        return $next($request);
    }
}
