<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Upload;

class UploadController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    public function upload(Request $request)
    {
        if (!Auth::user()) {
            return response()->json(['status' => 'error', 'message' => 'user not found!'], 400);
        }
        
        $user = auth('api')->setToken($request->bearerToken())->user();
        
        $validateData = Validator::make($request->all(), [
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validateData->fails()) {
            return response()->json(['status' => 'error', 'message' => $validateData->errors()->first()], 400);
        }

        try{
            $avatar = $request->file('image');
            $storagePath = 'users/images';
            $filename = $user->username . '_' . time() . '.' . $avatar->getClientOriginalExtension();
            $path = $avatar->storeAs($storagePath, $filename, ['disk' => 'public']);
            $link = asset(Storage::url($path));
            $upload = Upload::where('user_id', $user->id)->update([
                'name' => $filename,
                'path' => $storagePath,
                'link' => $link,
            ]);

            if($upload){
                User::where('id', $user->id)->update([
                    'avatar' => $link,
                ]);

                $user = User::where('id', $user->id)->first();
                return response()->json([
                    'status' => 'success',
                    'message' => 'operation successful!',
                    'user' => $user,
                ]);
            }            
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'operation failed with error - '.$e->getMessage(),
            ], 400);
        }
    }
}