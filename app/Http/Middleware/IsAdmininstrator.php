<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmininstrator {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        
        $user = Auth::user();

        if ($user && $user->is_admin == 1) {
            return $next($request);
        }

        return redirect('/');
    }

}
