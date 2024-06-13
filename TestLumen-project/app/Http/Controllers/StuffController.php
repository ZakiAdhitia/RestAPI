<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use Illuminate\Http\Request;
use App\Models\User;

class StuffController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
  
    public function index()
{
    try {
        $data = Stuff::with('stuffStock')->get()->toArray();
        
        return ApiFormatter::sendResponse(200, 'success', $data);
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
}


//     public function getStuffOptions()
// {
//     try {
//         $data = Stuff::all(); // Mengambil semua data barang
//         return ApiFormatter::sendResponse(200, true, 'success', $data);
//     } catch (\Exception $err) {
//         return ApiFormatter::sendResponse(400, false, 'bad request', $err->getMessage());
//     }
// }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {
            //Validasi
            $this->validate($request , [
                'name' => 'required',
                'category' => 'required'
            ]);
            // menambah data
            $data = Stuff::create([
                'name' => $request->name,
                'category' => $request -> category,
            ]);

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    //pda lumen validasi menggunakan method validasi dari class controller yang memiliki dua argument
    // argument pertama 
    //jika antara nama kolom di database dan nama key di request sama maka bisa menggunakan perintah diatas, namun jika berbeda haruslah definisikan satu-persatu kolomnya seperti dibawah ini

 
    public function show($id)// untuk mencari data yang memiliki id mana yang akan di show 
    {
        try {
            $data = Stuff::where('id', $id)->first();
            if (is_null($data)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Data not found');
            } else {
                return ApiFormatter::sendResponse(200, 'succes', $data);
            }

        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

 
    public function edit(Stuff $stuff)
    {
        //
    }

    public function update(Request $request,$id)
    {
        try {
            $this->validate($request , [
                'name' => 'required',
                'category' => 'required'
            ]);

            $checkProses = Stuff::where('id' , $id)->update([
                'name' => $request->name,
                'category' =>$request->category
            ]);

            if ($checkProses) {
                $data = Stuff::find($id);
                return ApiFormatter::sendResponse(200, 'succes' ,$data);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request' , 'Gagal mengubah data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $getStuff = Stuff::where('id' ,$id)->delete();
            
            return ApiFormatter::sendResponse(200, 'success', 'Data stuff berhasil di hapus!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request' , $err->getMessage());
        }
    }

    public function trash (){
        try {
            $data = Stuff::onlyTrashed()->get(); // n

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::senResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id){
        try {
            $checkProses = Stuff::onlyTrashed()->where('id' , $id)->restore();

            if ($checkProses) {
                $data = Stuff::find($id);
                return ApiFormatter::sendResponse(200, 'succes', $data);

            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deletePermanent($id){
        try {
            $checkProses = Stuff::onlyTrashed()->where('id',$id)->forceDelete();

            return ApiFormatter::sendResponse(200, 'succes', 'Berhasil menghapus permanent!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400,'bad request', $err->getMessage());
        }
    }
}
