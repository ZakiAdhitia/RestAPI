<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use Illuminate\Http\Request;
use App\models\Stuff;
use App\models\StuffStock;
use Illuminate\Support\Facades\Validator;

class StuffStockController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
    
    public function index(){
        $stuffStock = StuffStock::all();
        $stuff = Stuff::with('stuffstock')->get();

        return response()->json([
         'success' => true,
         'message' => 'Lihat semua barang',
            'barang' => $stuff,
            'data' => $stuffStock
        ],200);
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'stuff_id' => 'required',
        'total_available' => 'required',
        'total_defec' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Semua kolom wajib disi!',
            'data' => $validator->errors()
        ], 400);
    } else {
        $stock = StuffStock::updateOrCreate(
            ['stuff_id' => $request->input('stuff_id')],
            ['total_available' => $request->input('total_available'),
             'total_defec' => $request->input('total_defec')]
        );

        if ($stock) {
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan',
                'data' => $stock
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Barang gagal ditambahkan',
            ], 400);
        }
    }
}


    public function show($id){
        try{
            $stock = StuffStock::with('stuff')->find($id);

            return response()->json([
                'success' => true, 
                'message' => 'Lihat semua stock barang dengan id ' . $id,
                'data' => $stock
            ], 200);
        } catch(\Throwable $th){
            return response() -> json([
                'success' => false,
                'message' => 'dara dengan id' . $id .'tidak ditemukan'
            ], 400);
        }
    }

    public function update(Request $request, $id) {
        try{
            $stock = StuffStock::with('stuff')->find($id);

            $stuff_id = ($request->stuff_id) ? $request->stuff_id : $stock->stuff_id;
            $total_available = ($request->total_available) ? $request->total_available : $stock->total_available;
            $total_defect = ($request->total_defect) ? $request->total_defect : $stock->total_defect;

            if ($stock) {
                $stock->update([
                    'stuff_id' => $stuff_id,
                    'total_available' => $total_available,
                    'total_defect' => $total_defect
                ]);

                return response()->json([
                  'success' => true,
                  'message' => 'Barang berhasil diubah',
                    'data' => $stock
                ],200);
            } else{
                return response()->json([
                    'success' => false,
                    'message' => 'Proses gagal',
                  ],400);
            }
        } catch(\Throwable $th){
            return response()->json([
              'success' => false,
              'message' => 'Proses gagal! data dengan id '.$id.' tidak ditemukan',
            ],400);
        }
    }

    public function destroy($id){
        try{
            $stuffStock = stuffStock::findOrFail($id);
    
            $stuffStock->delete();
    
            return response()->json([
             'success' => true,
             'message' => 'Barang Hapus Data dengan id' . $id,
                'data' => $stuffStock
            ],200);
        } catch(\Throwable $th){
            return response()->json([
            'success' => false,
            'message' => 'Proses gagal! data dengan id '.$id.' tidak ditemukan',
            ],400);
        }
    }

    public function subStock(Request $request, $id)
    {
        try {
             $getStuffStock = StuffStock::find($id);

             if (!$getStuffStock) {
                return ApiFormatter::sendResponse(400, false, 'Data Stuff Stock Not Found');
             } else {
                $this->validate($request, [
                    'total_available' => 'required',
                    'total_defec' => 'required',
                ]);

                $isStockAvailable = $getStuffStock->update['total_available'] - $request->total_available;
                $isStockDefac = $getStuffStock->update['total_defec'] - $request->total_defec;

                if ($isStockAvailable < 0 || $isStockDefac < 0) {
                    return ApiFormatter::sendResponse(400, true, 'Substraction Stock Cant Less Than A Stock Stored');
                } else {
                    $subStock = $getStuffStock->update([
                        'total_available' => $isStockAvailable,
                        'total_defec' => $isStockDefac,
                    ]);

                    if ($subStock) {
                        $getStockSub = StuffStock::where('id', $id)->with('stuff')->first();

                        return ApiFormatter::sendResponse(200, true, 'Succesfully Sub A Stock Of StuFf Stock Data', $getStockSub);
                    }
                }
             }
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }
}