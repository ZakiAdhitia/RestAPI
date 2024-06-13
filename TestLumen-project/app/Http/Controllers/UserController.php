<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Models\User;
use Illuminate\Support\Facades\Hash;



class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }

    public function login(Request $request)
   {
    try {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        // dd($request->email);
        $user = User::where('email', $request->email)->first(); // mencari dan mendapatkan data user berdasarkan email yang digunakan untuk login
        // dd($user);

        if (!$user) {
            // jika email terdaftar maka akan dikembalikan response error
            return ApiFormatter::sendResponse(404, 'Login Failed! User doesnt exist');
        } else {
            //jika email terdaftar, selanjutnya pencocokan password yang diinput dengan password di database dengan menggunakan Hash::check().
            $isValid =  Hash::check($request->password, $user->password);
            //Hash::check membutuhkan 2 argument untuk perbandingan mencocokan yang sudah di hash dan yang belum

            if (!$isValid) {
                //jika password tidak cocok maka akan dikembalikan response error
                return ApiFormatter::sendResponse(404, 'Login failed! Paswword doesnt Match');
            } else {
                // jika password sesuai selanjutnya akan membuat token
                //bin2hex digunakan untuk dapat mengkonversi string karakter ASCII menjadi nilai hekasadesimal
                // random_bytes menghasilkan type pseudo-acak yang aman secara kriptografis dengan panjang 40 karakter
                $generateToken = bin2hex(random_bytes(40));
                //token inilah yang nanti diperlukan pada proses authentucation user yang login 

                $user->update([ // sudah ada where
                    'token' => $generateToken
                    // update kolom token dengan value hasil dari generateToken di row user yang ingin login 
                ]);
                return ApiFormatter::sendResponse(200, 'Login Succesfully', $user);
            }
        }
    } catch (\Exception $e) {
        return ApiFormatter::sendResponse(400, false, $e->getMessage());
    }
   }
    
    public function index()
    {
        try {
            $data = User::all()->toArray();


           return ApiFormatter::sendResponse(200,'succes',$data);
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    
    public function store(Request $request)
    {
        try {
            $this->validate($request , [
            'username' => 'required|unique:user',
            'email' => 'required|unique:user',
            'password' => 'required',
            'role' => 'required'
        ]);

        $createUser = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' =>  $request->role,
        ]);
        
        return ApiFormatter::sendResponse(200 , true, 'Succesfully Create A User Succesfully', $createUser);

        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400 , false, $e->getMessage());
        }
    }

   
    public function show($id)
    {
        try{
            $data = User::where ('id', $id)->first();
            if (is_null($data)){
                return ApiFormatter::sendResponse(400, 'bad request','data not found!');
            } else {
                return ApiFormatter::sendResponse(200, 'success', $data);
            } 

        }catch (\Exception $err){
            return ApiFormatter::sendResponse(400,'bad request', $err->getMessage());
        }
    }

    
    public function edit($id)
    {
        //
    }

    
    public function update(Request $request, $id)
    {
        try {

            $getUser = User::find($id);

            if (!$getUser) {
                return ResponseFormatter::sendResponse(404, false, 'Data User Not Found');
            } else {
                $this->validate($request, [
                    'username' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'role' => 'required'
                ]);

                $updateUser = $getUser->update([
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                ]);

                if($updateUser) {
                    return ApiFormatter::sendResponse(200, 'Succesfully Update A User Data', $getUser);
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $getUser = User::find($id);

            if(!$getUser) {
                return ApiFormatter::sendResponse(404, 'Data User Not Found');
            } else {
                $deleteUser = $getUser->delete();

                if($deleteUser) {
                    return ApiFormatter::sendResponse(200, 'Succesfully Delete A User Data');
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function recycleBin()
    {
        try {

            $userDeleted = User::onlyTrashed()->get();

            if(!$userDeleted) {
                return ApiFormatter::sendResponse(404, 'Deleted Data User Doesnt Exists');
            } else {
                return ApiFormatter::sendResponse(200, 'Succesfully Get Delete All User Data');
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function restore($id) 
    {
        try {

            $getUser = User::onlyTrashed()->where('id', $id)->restore();

            if (!$getUser) {
                return ApiFormatter::sendResponse(404, 'Data User Not Found');
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function forceDestroy($id)
    {
        try{

            $getUser = User::onlyTrashed()->where('id', $id)->forceDelete();

            if(!$getUser) {
                return ApiFormatter::sendResponse(404, 'Data User Not Found');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Permanent Delete A User Data');
            }
        } catch(\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }


    public function logout (Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return ApiFormatter::sendResponse(400, 'Login Failed User Doesn Exist' );                
            } else {
                if (!$user->token) {
                    return ApiFormatter::sendResponse(400, 'Logout Failed! User Doesn Login Sciene');
                } else {
                    $logout = $user->update(['token' => null]);

                    if ($logout) {
                        return ApiFormatter::sendResponse(200, 'Logout Succesfully');
                    }
                }
            }

        } catch (\Exception $e) {
         return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    public function trash (){
        try {
            $data = User::onlyTrashed()->get(); // n

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::senResponse(400, 'bad request', $err->getMessage());
        }
    }
}
