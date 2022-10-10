<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Upload;

class AuthController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login','register']]);
    // }
    
    public function loginPage()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $validateData = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validateData->fails()) {
            return response()->json(['status' => 'error', 'message' => $validateData->errors()->first()], 400);
        }
        
        $credentials = $request->only('username', 'password');

        try{
            $token = Auth::attempt($credentials);
            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $user = Auth::user();
            return response()->json([
                'status' => 'success',
                'message' => 'operation successful!',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'operation failed with error - '.$e->getMessage(),
            ], 400);
        }
    }

    public function register(Request $request){        
        $validateData = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'username' => 'required|string|max:20|unique:users',
            // 'phone_number' => 'required|numeric|max:15|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validateData->fails()) {
            return response()->json(['status' => 'error', 'message' => $validateData->errors()->first()], 400);
        }
        // dd($request);
        
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ]);
            
            if($user){
                Upload::create([
                    'user_id' => $user->id,
                ]);

                $token = Auth::login($user);
                return response()->json([
                    'status' => 'success',
                    'message' => 'operation successful!',
                    'user' => $user,
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]
                ]);
            }      
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'operation failed with error - '.$e->getMessage(),
            ], 400);
        }  
    }

    public function logout(Request $request)
    {
        if (!Auth::user()) {
            return response()->json(['status' => 'error', 'message' => 'user not found!'], 400);
        }
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh(Request $request)
    {
        if (!Auth::user()) {
            return response()->json(['status' => 'error', 'message' => 'user not found!'], 400);
        }
        
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}