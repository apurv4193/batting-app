<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Config;

class VerifyJWTToken {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Failed to validating token.',
                            'code' => 404
                ]);
            } else if ($user->status != Config::get('constant.ACTIVE_STATUS_FLAG')) {
                return response()->json([
                            'message' => 'User not available.',
                            'status_code' => 400
                                ], 400);
            }
        } catch (JWTException $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Token Expired.',
                            'code' => $e->getStatusCode()
                ]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                            'status' => '0',
                            'message' => 'Invalid Token.',
                            'code' => $e->getStatusCode()
                ]);
            } else {
                return response()->json([
                            'status' => '0',
                            'message' => 'Token is required.',
                            'code' => 404
                ]);
            }
        }
        return $next($request);
    }

}
