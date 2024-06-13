<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\ApiFormatter;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
    
    public function login(Request $request)
    {
	    $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials =  $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return ApiForrmatter::sendResponse(400, 'User Not Found', 'Silahkan Cek Kembali Email Dan Password Anda!');
        }

        $respondWithToken = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ];
        return ApiFormatter::sendResponse(200, 'Logged-in', $respondWithToken);
   }

    
    public function me()
    {
        return ApiFormatter::sendResponse(200, 'success' , auth()->user());
    }

   
    public function logout()
    {
        auth()->logout();
        return ApiFormatter::sendResponse(200, 'Success' , 'Berhasil Logout');
    }

   
}