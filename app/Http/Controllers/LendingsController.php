<?php

namespace App\Http\Controllers;

use App\Models\StuffStock;
use App\Helpers\ApiFormatter;
use App\Models\Lending;
use Illuminate\Http\Request;

class LendingsController extends Controller
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
            $getLending = Lending::with('stuff', 'user')->get();

            return ApiFormatter::sendResponse(200, 'Successfully Get All Lending Data', $getLending);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
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
                'date_time' => 'required',
                'name' => 'required',
                'total_stuff' => 'required',
            ]);

            $totalAvailable = StuffStock::where('stuff_id', $request->stuff_id)->value('total_available');
    
            if (is_null($totalAvailable)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Belum ada data inbound!');
            } elseif ((int) $request->total_stuff > (int) $totalAvailable) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Stok tidak tersedia!');
            } else {
                $lending = Lending::create([
                    'stuff_id' => $request->stuff_id,
                    'date_time' => $request->date_time,
                    'name' => $request->name,
                    'notes' => $request->notes ? $request->notes : '-',
                    'total_stuff' => $request->total_stuff,
                    'user_id' => auth()->user()->id,
                ]);
    
                $totalAvailableNow = (int) $totalAvailable - (int) $request->total_stuff;
                StuffStock::where('stuff_id', $request->stuff_id)->update(['total_available' => $totalAvailableNow]);
    
                $dataLending = Lending::where('id', $lending->id)->with('user', 'stuff', 'stuff.stuffStock')->first();
                return ApiFormatter::sendResponse(200, 'success', $dataLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $getLending = Lending::where('id', $id)->with('stuff', 'user', 'restoration')->first();

            if (!$getLending ) {
                return ApiFormatter::sendResponse(404, 'Data Lending Not Found');
            } else {
                return ApiFormatter::sendResponse(200, 'Succesfully Get A Lending Data', $getLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $getLending = Lending::find($id);

            if (!$getLending) {
                $this->validate($request, [
                    'stuff_id' => 'required',
                    'date_time' => 'required',
                    'name' => 'required',
                    'user_id' => 'required',
                    'notes' => 'required',
                    'total_stuff' => 'required',
                ]);

                $getStuffStock = StuffStock::where('stuff_id', $request->stuff_id)->first();
                $getCurrentStock = StuffStock::where('stuff_id', $getLending['stuff_id'])->first();

                if($request->stuff_id == $getCurrentStock['stuff_id']) {
                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getCurrentStock['total_available'] + $getLending['total_stuff'] - $request->total_stuff,
                    ]);
                } else {
                    $updateStock = $getCurrentStock->update([
                        'total_available' => $getCurrentStock['total_availanle'] + $getLending['total_stuff'],
                    ]);
                    $updateStock = $getStuffStock->update([
                        'total_available' => $getCurrentStock['total_availanle'] - $getLending['total_stuff'],
                    ]);
                }

                $updateLending = $getLending->update([
                    'stuff_id' => $request ->stuff_id,
                    'date_time' => $request ->date_time,
                    'name' => $request ->name,
                    'user_id' => $request ->user_id,
                    'notes' => $request ->notes,
                    'total_stuff' => $request ->total_stuff,
                ]);

                $getUpdatedLending = Lending::where('id', $id)->with('stuff', 'user', 'restoration')->first();

                return ApiFormatter::sendResponse(200, 'Successfully Update A Lending Data', $getUpdatedLending);
                
            } 
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, $err->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find the lending record
            $lending = Lending::find($id);
        
            // Check for restoration (already returned)
            if ($lending->restorations) {
                return response()->json(['error' => 'Peminjaman sudah dikembalikan, tidak bisa dibatalkan'], 400);
            }
        
            // Delete the lending record
            $lending->delete();
        
            $stuffStock = StuffStock::where('stuff_id', $lending->stuff_id)->first();
        
            if ($stuffStock) {
                $stuffStock->total_available += $lending->total_stuff;
                $stuffStock->save();
            } 
    
            return ApiFormatter::sendResponse(200, 'success', 'Data Lending berhasil dihapus ');
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, false, $err->getMessage());
        }    
    }

}