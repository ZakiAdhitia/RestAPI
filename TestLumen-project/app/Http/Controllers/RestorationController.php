<?php

namespace App\Http\Controllers;

use App\Models\Restoration;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Models\Lending;
use App\models\StuffStock;

class RestorationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
    
    
    public function index()
    {
        //
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        
            try {
                $this->validate($request, [
                    'user_id' => 'required',
                    'lending_id' => 'required',
                    'date_time' => 'required',
                    'total_good_stuff' => 'required',
                    'total_defec_stuff' => 'required',
                ]);

            $getLending = Lending::where('id', $request->lending_id)->first();// get data peminjaman yang sesuai dengan pengembalian

            $totalStuff = $request->total_good_stuff + $request->total_defec_stuff;// variabel penampung jumlah barang yang akan dikembalikan

            if ($getLending['total_stuff'] != $totalStuff) {
                return ApiFormatter::sendResponse(400, 'The Amount Of Items Returned does not match the amount borrowed');
            } else {
                $getStuffStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first();
            
            
            $createRestoration = Restoration::create([
                'user_id' => $request->user_id,
                'lending_id' => $request->lending_id,
                'date_time' => $request->date_time,
                'total_good_stuff' => $request->total_good_stuff,
                'total_defec_stuff' => $request->total_defec_stuff,
            ]);
            
            $updateStock = $getStuffStock->update([
                'total_available' => $getStuffStock['total_available'] + $request->total_good_stuff,
                'total_defec' => $getStuffStock['total_defec'] + $request->total_defec_stuff,
        ]);
        
        if ($createRestoration && $updateStock) {
            return ApiFormatter::sendResponse(200, 'Successfully Create A Restoration Data' , $createRestoration);
        }
    }
    
    } catch (\Exception $e) {
        return ApiFormatter::sendResponse(404, $e->getMessage());
    }
    }
    
    
    
    public function show(Restoration $restoration)
    {
        //
    }

   
    public function edit(Restoration $restoration)
    {
        //
    }

    
    public function update(Request $request, Restoration $restoration)
    {
        //
    }

    
    public function destroy(Restoration $restoration)
    {
        //
    }
}
