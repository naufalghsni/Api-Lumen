<?php

namespace App\Http\Controllers;

use App\Models\InboundStuff;
use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use App\Models\StuffStock;
use Illuminate\Http\Request;

class InboundStuffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function __construct()
    {

        $this->middleware('auth:api');

    }

    public function index()
    {
        try {
            $getInboundStuff = InboundStuff::with('stuff', 'stuff.stuffStock')->get();

            return ApiFormatter::sendResponse(200, true, 'Successfully Get All Inbound Stuff Data', $getInboundStuff);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required', 
                'total' => 'required',
                'date' => 'required',
                'proff_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
            ]);

            $checkStuff = Stuff::where('id', $request->stuff_id)->first();

            if (!$checkStuff){
                
                return ApiFormatter::sendResponse(400, false, 'Data Stuff does not exists');
                
            } else {

                if ($request->hasFile('proff_file')) { // ngecek file ada apa engga
                    $proof = $request->file('proff_file'); // get filenya
                    $destinationPath = 'proof/'; // sub path di folder public
                    $proofName = date('YmdHis') . ". ". $proof->getClientOriginalExtension();
                    $proof->move($destinationPath, $proofName); // file yang sudah di gate diatas dipindahkan ke folder public/proof dengan nama sesuai ang di variabel proofname
                }
    
                $createStock = InboundStuff::create ([
                    'stuff_id' => $request->stuff_id,
                    'total' => $request->total,
                    'date' => $request->date,
                    'proff_file' => $proofName,
                ]);
    
                if ($createStock){
                    $getStuff = Stuff::where('id', $request->stuff_id)->first();
                    $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
    
                    if (!$getStuffStock) {
                        $updateStock = StuffStock::create([
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $request->total,
                            'total_defec' => 0,
                        ]);
                    } else {
                        $updateStock = $getStuffStock->update([
                            'stuff_id' => $request->stuff_id,
                            'total_available' => $getStuffStock['total_available'] + $request->total,
                            'total_defec' => $getStuffStock['total_defec'],
                        ]);
                    }
    
                    if ($updateStock) {
                        $getStock = StuffStock::where('stuff_id',  $request->stuff_id)->first();
                        $stuff = [
                            'stuff' => $getStuff,
                            'inboundStuff' => $createStock,
                            'stuffStock' => $getStock
                        ];
    
                        return ApiFormatter::sendResponse(200, 'Successfully Create A Inbound Stuff Data', $stuff);
                    } else {
                        return ApiFormatter::sendResponse(400, false, 'Failed To Update A Stuff Stock Data');
                    }
                } else {
                    return ApiFormatter::sendResponse(400, false, 'Failed To Create A Inbound Stuff Data');
                }
            }

        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $getInboundStuff = InboundStuff::with('stuff', 'stuff.stuffStock')->find($id);

            if(!$getInboundStuff) {
                return ApiFormatter::sendResponse(404, 'Data Inbound Stuff Not Found!');
            } else {
                return ApiFormatter::sendResponse(200, 'Successfully Get A Inbound Stuff Data', $getInboundStuff);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400,  $err->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function edit(InboundStuff $inboundStuff)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $getInboundStuff = InboundStuff::find($id);

            if (!$getInboundStuff) {
                return ApiFormatter::sendResponse(404, false, 'Data found Stuff Not Found');
            } else {
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'total' => 'required',
                    'date' => 'required',
                ]);

                if ($request->hasFile('proff_file')) {
                    $proof = $request->file('proff_file');
                    $destinationPath = 'froof/';
                    $proofName = date('YmdHis'). "." .
                    $proof->getClientOriginalExtension();
                    $proof->move($destinationPath, $proofName);
    
                    unlink(base_path('pubic/proof/'. $getInboundStuff
                    ['proff_file']));
                } else {
                    $proofName = $getInboundStuff['proff_file'];
                }

                $getStuff = Stuff::where('id', $getInboundStuff['stuff_id'])->first();

                $getStuffStock = StuffStock::where('stuff_id', $getInboundStuff ['stuff_id'])->first();

                $getCurrentStock = StuffStock::where('stuff_id', $request['stuff_id'])->first();

                if ($getStuffStock['stuff_id'] == $request['stuff_id']) {
                    $updateStock = $getStuffStock->update([
                        'total_avalaible' => $getStuffStock['total_available'] - $getInboundStuff['total'] + $request->total,
                    ]);
                } else {
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getStuffStock['total_available'] - $getStuffStock['total'],
                    ]);

                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getStuffStock['total_available'] + $request->total,
                    ]);
                }

                $updateInbound = $getInboundStuff->update([
                    'stuff_id' => $request->stuff_id,
                    'total' => $request->total,
                    'date' => $request->date,
                    'proff_file' => $proofName,
                ]);

                $getStock = StuffStock::where('stuff_id', $request['stuff_id'])->first();
                $getInbound = InboundStuff::find($id)->with('stuff', ' stuffStock');
                $getCurrentStock = Stuff::where('id', $request['stuff_id'])->first();

                $stuff = [
                    'stuff' => $getCurrentStock,
                    'InboundStuff' => $getInbound,
                    'stuffStock' => $getStock,
                ];

                return ApiFormatter::sendResponse(200, false, 'Succesfully Update A Inbound Stuff Data', $stuff);
            }
        }  catch (\Exception $err) {
            // Tangani kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InboundStuff  $inboundStuff
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $checkProses = InboundStuff::where('id', $id)->first();
    
            if ($checkProses) {

                $dataStock = StuffStock::where('stuff_id', $checkProses->stuff_id)->first();

                if ($dataStock->total_available < $checkProses->total) {

                    return ApiFormatter::sendResponse(400, 'bad request', 'Total available barang dari total data dipinjam');

                } else {

                    $stuffId = $checkProses->stuff_id;
                    $totalInbound = $checkProses->total;
                    $checkProses->delete();
                    
                    if ($dataStock) {
                        $total_available = (int)$dataStock->total_available - (int)$totalInbound;
                        $minusTotalStock = $dataStock->update(['total_available' => $total_available]);
        
                        if ($minusTotalStock) {
                            $updateStuffAndInbound = Stuff::where('id', $stuffId)->with('inboundStuffs', 'stuffStock')->first();
                            return ApiFormatter::sendResponse(200, 'success', $updateStuffAndInbound);
                        }
                    } else {
                        // Tangani jika data stok tidak ditemukan
                        return ApiFormatter::sendResponse(404, 'not found', 'Data Stock Stuff tidak ditemukan');
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
        try {
            $data = InboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
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

        // try {
        //     $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->restore();

        //     if ($checkProses) {
        //         $data = InboundStuff::find($id);
        //         return ApiFormatter::sendResponse(200, 'success', $data);
        //     } else {
        //         return ApiFormatter::sendResponse(400, 'bad request', 'Gagal Mengembalikan Data!');
        //     }
        // } catch (\Exception $err) {
        //     return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        // }
    }

    public function forceDestroy($id)
    {
        try {
            $getInbound = InboundStuff::onlyTrashed()->where('id',$id)->first();
            
            unlink(base_path('public/proof/'.$getInbound->proof_file));
            // Menghapus data dari database
            $checkProses = InboundStuff::where('id', $id)->forceDelete();
            
            // Memberikan respons sukses
            return ApiFormatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            // Memberikan respons error jika terjadi kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }

        // try {
        //     $checkProses = InboundStuff::onlyTrashed()->where('id', $id)->forceDelete();
        
        //     return ApiFormatter::sendResponse(200, 'success', 'Data Berhasil Di Hapus Permanen!');
        // } catch (\Exception $err) {
        //     return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());  
        // }
    }

    // private function deleteAssociatedFile(InboundStuff $inboundStuff)
    // {
    //     // Mendapatkan jalur lengkap ke direktori public
    //     $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proof';

    
    //     // Menggabungkan jalur file dengan jalur direktori public
    //      $filePath = public_path('proof/'.$inboundStuff->proof_file);
    
    //     // Periksa apakah file ada
    //     if (file_exists($filePath)) {
    //         // Hapus file jika ada
    //         unlink(base_path($filePath));
    //     }
    // }
}