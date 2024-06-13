<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use App\Models\InboundStuff;
use App\Models\StuffStock;                                                      
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class InboundStuffController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }
    
    public function index(Request $request)
    {
    try {
    if ($request->has('filter_id')) {
        $data = InboundStuff::where('stuff_id', $request->filter_id)->with('stuff', 'stuff.stuffStock')->get();
    } else {
        $data = InboundStuff::with('stuff', 'stuff.stuffStock')->get();
    }
    
    return ApiFormatter::sendResponse(200, 'success', $data);
} catch (\Exception $err) {
    return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
}

    }

    public function store(Request $request)
{
    try {
        $this->validate($request, [
            'stuff_id' => 'required',
            'total' => 'required',
            'date' => 'required',
            'proff_file' => 'required|mimetypes:image/jpeg,image/png,image/jpg,application/pdf', // Ubah sesuai kebutuhan
        ]);

        $proff_Name = null; // Setel ke null pada awalnya

        if ($request->hasFile('proff_file')) {
            $proff = $request->file('proff_file');
            $destinationPath = 'proff/';

            $proff_Name = date('YmdHis') . "." . $proff->getClientOriginalExtension();

            if ($proff->move($destinationPath, $proff_Name)) {
                // File berhasil diunggah
            } else {
                // Tangani kegagalan upload
                return ApiFormatter::sendResponse(400, false, 'Gagal mengunggah file.');
            }
        }

        // ... (bagian kode lainnya yang menggunakan $proff_Name jika perlu)

        // Proses pembuatan stock (asumsi Anda sudah memiliki logika ini)
        $createStock = InboundStuff::create([
            'stuff_id' => $request->stuff_id,
            'total' => $request->total,
            'date' => $request->date,
            'proff_file' => $proff_Name,
        ]);

        // if ($createStock) {
        //     $getStuff = Stuff::where('id', $request->stuff_id)->first();
        //     $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();

        //     // ... (proses update stock)

        //     if ($updateStock) {
        //         $getStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
        //         $stuff = [
        //             'stuff' => $getStuff,
        //             'InboundStuff' => $createStock,
        //             'stuffStock' => $getStock
        //         ];

        //         return ApiFormatter::sendResponse(200, 'Data Inbound Stuff berhasil dibuat', $stuff);
        //     } else {
        //         return ApiFormatter::sendResponse(400, false, 'Gagal update data Stuff Stock');
        //     }
        // } else {
        //     return ApiFormatter::sendResponse(400, false, 'Gagal membuat data Inbound Stuff');
        // }

    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, false, $err->getMessage());
    }
}


     public function destroy(InboundStuff $inboundStuff, $id)
    {
        try {
            $checkProses = InboundStuff::where('id', $id)->first(); // memasukan data dari $id ke dalam $checkproses untuk dicek kembali
    
            if ($checkProses) {
                $stuffId = $checkProses->stuff_id; // mengambil nilai dari properti stuff_id dari object $checkProses dan memasukannya ke variabel $stuffId
                $totalInbound = $checkProses->total; // mengambil nilai dari properti total dari object $checkProses dan memasukannya ke variabel $totalInbound
                $checkProses->delete(); // metode dari laravel/lumen untuk menghapus record dari database
    
                $dataStock = StuffStock::where('stuff_id', $checkProses->stuff_id)->first(); // mengambil baris pertama pada data yang ada di dalam stuff_id di dalam table StuffStock lalu menyimpannya di $dataStock
                
                if ($dataStock->total_available < $totalInbound) { // Jika total_available lebih kecil dari totalInbound, maka tampilkan pesan error dan hentikan eksekusi lebih lanjut
                    return response()->json(['message' => 'Tidak dapat menghapus InboundStuff karena total_available lebih kecil dari totalInbound'], 400);
                } else { // Jika total_available lebih besar atau sama dengan totalInbound, maka lanjutkan dengan penghapusan
                    $checkProses->delete();
                    if ($dataStock) {
                        $total_available = (int)$dataStock->total_available - (int)$totalInbound;   
                        $minusTotalStock = $dataStock->update(['total_available' => $total_available]);
        
                        if ($minusTotalStock) {
                            $updateStufAndInbound = Stuff::where('id', $stuffId)->with('inboundStuffs', 'stuffStock')->first();
                            return ApiFormatter::sendResponse(200, 'success', $updateStufAndInbound);
                        }
                    } else {
                        // Tangani jika data stok tidak ditemukan
                        return ApiFormatter::sendResponse(404, 'not found', 'Data stok stuff tidak ditemukan');
                    }
                }
                
            } else {
                // Tangani jika data InboundStuff tidak ditemukan
                return ApiFormatter::sendResponse(404, 'not found', 'Data InboundStuff tidak ditemukan');
            }
        } catch (\Exception $err) {
            // Tangani kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    

    public function trash()
    {
        try{
            $data= InboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
    
    public function restore(InboundStuff $inboundStuff, $id)
    {
        try {
            // Memulihkan data dari tabel 'inbound_stuffs'
            $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();
    
            if ($checkProses) {
                // Mendapatkan data yang dipulihkan
                $restoredData = InboundStuff::find($id);
    
                // Mengambil total dari data yang dipulihkan
                $totalRestored = $restoredData->total;
    
                // Mendapatkan stuff_id dari data yang dipulihkan
                $stuffId = $restoredData->stuff_id;
    
                // Memperbarui total_available di tabel 'stuff_stocks'
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();
                
                if ($stuffStock) {
                    // Menambahkan total yang dipulihkan ke total_available
                    $stuffStock->total_available += $totalRestored;
    
                    // Menyimpan perubahan pada stuff_stocks
                    $stuffStock->save();
                }
    
                return ApiFormatter::sendResponse(200, 'success', $restoredData);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function deletePermanent($id)
{
    try {
        $inboundStuff = InboundStuff::withTrashed()->where('id', $id)->first();
        
        if ($inboundStuff) {
            $imageName = $inboundStuff->proff_file;
            
            // Hapus file terkait
            if ($imageName && File::exists('uploads/' . $imageName)) {
                File::delete('uploads/' . $imageName);
            }
            
            // Hapus data secara permanen
            $inboundStuff->forceDelete();
            
            return ApiFormatter::sendResponse(200, true, 'Berhasil menghapus permanen data dengan id = ' . $id . ' dan berhasil menghapus semua data permanent dengan file name: ' . $imageName);
        } else {
            return ApiFormatter::sendResponse(404, false, 'Data not found');
        }
    } catch (Exception $err) {
        return ApiFormatter::sendResponse(500, false, 'Proses gagal', $err->getMessage());
    }
}

    
    
    
    private function deleteAssociatedFile(InboundStuff $inboundStuff)
    {
        // Mendapatkan jalur lengkap ke direktori public
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proff';

    
        // Menggabungkan jalur file dengan jalur direktori public
        $filePath = public_path('proff/'.$inboundStuff->proff_file);
    
        // Periksa apakah file ada
        if (file_exists($filePath)) {
            // Hapus file jika ada
            unlink(base_path($filePath));
        }
    }

    public function show($id)
    {
    
        try {
            $getInboundStuff = InboundStuff::with('stuff', 'suff.stuffStock')->find($id);
            
            if (!$getInboundStuff) {
                return ResponseFormatter::sendResponse(404, 'Data Inbound Stuff Not Found');
            } else {
                return ResponseFormatter::sendResponse(200, 'Successfully Get A Inbound Stuff Data', $getInboundStuff);
            }
        } catch (\Exception $e) {
            return ResponseFormatter::sendResponse(400, $e->getMessage());
        }

    }

    public function update(Request $request,$id)
    {
        try {
            $getInboundStuff = InboundStuff::find($id);

            if (!$getInboundStuff) {
                return ApuFormatter::sendResponse(404, 'Data Inbound Stuff Not Found');
            } else {
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'total' => 'required',
                    'date' => 'required',
                ]);
            }

                if($request->hasFile('proff_file')) {
                    $proff = $request->file('proff_file'); 
                    $destinationPath = 'proff/'; // destionationPath = untuk memasukan file ke folder tujuan 
                    $proff_Name = date('YmdHis') . "." . 
                    $proff->getClientOriginalExtension();
                    $proff->move($destinationPath, $proff_Name); 

                    // unlink(base_path('public/proff/' . $getInboundStuff['proff_file']));
                } else {
                    $proff_Name = $getInboundStuff['proff_file'];
                }

                $getStuff = Stuff::where('id', $getInboundStuff['stuff_id'])->first();

                //get data stuff stock berdasarkan stuff id di variabel awal 
                $getStuffStock = StuffStock::where('stuff_id', $getInboundStuff ['stuff_id'])->first(); // stuff id request tidak berubah 

                $getCurrentStock = StuffStock::where('stuff_id', $request['stuff_id'])->first(); // stuff_id request tidak berubah

                if ($getStuffStock['stuff_id'] == $request['stuff_id']) {
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] - $getInboundStuff['total'] + $request->total,
                    ]); //update data yang stuff_id tidak berubah dengan merubah total available dikurangi total daya lama di tambah total data baru 
                } else {
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] + $request->total,
                    ]); // update data stuff id yang berubah dengan menjumlahkan total available dengan total yang baru 
                }

                $updateInbound = $getInboundStuff->update([
                    'stuff_id' => $request->stuff_id,
                    'total' => $request->total,
                    'date' => $request->date,
                    'proff_file' => $proff_Name
                ]);
                
                $getStock = StuffStock::where('stuff_id', $request['stuff_id'])->first();
                $getInbound = InboundStuff::find($id)->with('stuff', 'StuffStock');
                $getCurrentStock = Stuff::where('id', $request['stuff_id'])->first();

                $stuff = [
                    'stuff' => $getCurrentStock,
                    'InboundStuff' => $getInbound,
                    'stuffStock' => $getStock,
                ];

                return ApiFormatter::sendResponse(200, 'Succesfully Update A Inbound Stuff Data', $stuff);

        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }
}