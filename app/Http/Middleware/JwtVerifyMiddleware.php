<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Exception;
use Firebase\JWT\Key;


class JwtVerifyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        
            try {
                // $token = $request->header('Authorization');
                $token =  $request->bearerToken();
                if (!$token) {
                    throw new Exception('Token not provided');
                }
                $key = env('JWT_SECRET');

                // $decoded = JWT::decode($token, env('JWT_SECRET'), 'HS256');
                $decoded = JWT::decode($token, new Key($key, 'HS256'));

                $request->merge(['customer_id' => $decoded->customer_id]);
    
                return $next($request);
            } catch (Exception $e) {
                return response()->json(['success'=> false, 'error' => $e->getMessage()], 401);
            }
        
    }
}
