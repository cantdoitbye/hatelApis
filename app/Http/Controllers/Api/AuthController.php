<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Exception;

class AuthController extends Controller
{
    //
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    public function signup(Request $r)
    {
        $validator = Validator::make($r->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|email|unique:customerinfo,email',
            'password' => 'required',
            'phone' => 'required|unique:customerinfo,cust_phone',
            'useragree' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        $customer = DB::table('customerinfo')->insertGetId([
            'firstname' => $r->input('f_name'),
            'lastname' => $r->input('l_name'),
            'email' => $r->input('email'),
            'cust_phone' => $r->input('phone'),
            'pass' => md5($r->input('password')),
            'signupdate' => now(), 
        ]);

        $token = $this->jwt->encode([
            'customer_id' => $customer,
            'email' => $r->input('email'),
        ], env('JWT_SECRET'), 'HS256');

        return response()->json([
            'success' => true,
            'message' => 'Signup successful',
            'token' => $token,
        ], 201); 
    }

    public function login(Request $r)
    {
        $validator = Validator::make($r->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()->all(),
            ], 400);
        }

        $email = $r->input('email');
        $password = md5($r->input('password'));

        $user = DB::table('customerinfo')->where('email', $email)
                                         ->where('pass', $password)
                                         ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $this->jwt->encode([
            'customer_id' => $user->customerid,
            'email' => $email,
        ], env('JWT_SECRET'), 'HS256');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
        ], 200);
    }

}
