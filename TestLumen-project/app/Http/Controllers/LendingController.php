<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\models\StuffStock;
use Illuminate\Support\Str;
class LendingController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
   
    public function index()
    {
        try {
            $getLending = Lending::with('stuff','user')->get();

            return ApiFormatter::sendResponse(200, 'Succesfully Get All Lending Data' , $getLending);
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    
    public function create()
    {
        //
    }

    public function trash (){
        try {
            $data = Lending::onlyTrashed()->get(); // n

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::senResponse(400, 'bad request', $err->getMessage());
        }
    }

   
   public function store(Request $request)
{
    try {
        // Validate request data
        $this->validate($request, [
            'stuff_id' => 'required',
            'date_time' => 'required',
            'name' => 'required',
            'user_id' => 'required',
            'notes' => 'required',
            'total_stuff' => 'required',
        ]);

        // Create Lending record
        $createLending = Lending::create([
            'stuff_id' => $request->stuff_id,
            'date_time' => $request->date_time,
            'name' => $request->name,
            'user_id' => $request->user_id,
            'notes' => $request->notes,
            'total_stuff' => $request->total_stuff,
        ]);

        // Get StuffStock record and update total_available
        $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
        if ($getStuffStock) {
            $updateStock = $getStuffStock->update([
                'total_available' => $getStuffStock['total_available'] - $request->total_stuff,
            ]);
        } // Handle potential case where StuffStock record is not found

        // Return success response
        return ApiFormatter::sendResponse(200, 'Successfully Created A Lending Data', $createLending);

    } catch (\Exception $e) {
        // Return error response
        return ApiFormatter::sendResponse(400, $e->getMessage());
    }
}


    
    public function show($id)
    {
        try {
            $getLending = Lending::where('id', $id)->with('stuff', 'user', 'restoration')->first();

            if (!$getLending) {
                return ApiFormatter::sendResponse(404, 'Data Lending Not Found');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Get A Lending Data', $getLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }
    
    public function edit(Lending $lending)
    {
        //
    }

    
    public function update(Request $request, $id)
    {
        try {
            
            $getLending = Lending::find($id);

            if ($getLending) {
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'date_time' => 'required',
                    'name' => 'required',
                    'user_id' => 'required',
                    'notes' => 'required',
                    'total_stuff' => 'required',
                ]);

            $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();// get stock berdasarkan request stuff id 
            $getCurrentStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first(); // stuff_id request tidak berubah

           if ($request->stuff_id == $getCurrentStock['stuff_id']) {
            $updateStock =  $getCurrentStock->update([
                'total_available' => $getCurrentStock['total_available'] + $getLending['total_stuff'] - $request->total_stuff
            ]); // total available lama yang akan dijumlahkan dengan total peminjaman barang lama lalu dikurangai dengan total peminjaman yang baru
           
        } else {
        $updateStock = $getCurrentStock->update([
         'total_available' => $getCurrentStock['total_available'] + $request['total_stuff'],
        ]); // total available lama dijumllahkan dengan total peminjaman barang yang lama 

        $updateStock = $getStuffStock->update([
         'total_available' => $getStuffStock['total_available'] - $request['total_stuff'],
        ]); // total available baru dikurangi dengan total peminjaman yang baru 
        }

        $updateLending = $getLending->update([
            'stuff_id' => $request->stuff_id,
            'date_time' => $request->date_time,
            'name' => $request->name,
            'user_id' => $request->user_id,
            'notes' => $request->notes,
            'total_stuff' => $request->total_stuff,
        ]);

        $getUpdateLending = lending::where('id',$id)->with('stuff', 'user', 'restoration')->first();

        return ApiFormatter::sendResponse(200, 'Successfully Update A Lending Data', $getUpdateLending);
    }

        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, $e->getMessage());
        }
    }

    
    public function destroy($id)
    {
        try {
            $getLending = Lending::where('id' ,$id)->delete();
            
            return ApiFormatter::sendResponse(200, 'success', 'Data Lending berhasil di hapus!');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request' , $err->getMessage());
        }
    }

        public function deletePermanent($id){
            try {
                $checkProses = Lending::onlyTrashed()->where('id',$id)->forceDelete();
    
                return ApiFormatter::sendResponse(200, 'succes', 'Berhasil menghapus permanent!');
            } catch (\Exception $err) {
                return ApiFormatter::sendResponse(400,'bad request', $err->getMessage());
            }
        }

        
    public function restore($id){
        try {
            $checkProses = Lending::onlyTrashed()->where('id' , $id)->restore();

            if ($checkProses) {
                $data = Lending::find($id);
                return ApiFormatter::sendResponse(200, 'succes', $data);

            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
}
