<?php

namespace App\Http\Controllers;

use App\Models\StuffStock;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Http\Controllers\Controller;

class StuffStockController extends Controller
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
        try{
            $getStuffStock = StuffStock::with('stuff')->get();
            return ApiFormatter::sendResponse(200, true, 'Sucessfully Get all Stuff ', $getStuffStock);
        }catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, false, $e->getMessage());
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function subStock(Request $request, $id)
    {
        try{
            $getStuffStock = StuffStock::find($id);

            if (!$getStuffStock) {
                return ApiFormatter::sendResponse(404, false, 'Data stuff Stock not found');
            } else {
                $this->validate($request, [
                    'total_available' => 'reqoired',
                    'total_defec' => 'reqoired',
                ]);
                $isStockAvailable = $getStuffStock['total_available'] -$request->total_available;
                $isStockDafac = $getStuffStock[ 'total_defec'] + $request->total_defec;

                if ($isStockAvailable < 0 || $isStockDafac < 0) {
                    return ApiFormatter::sendResponse(404, true, 'A Substraction Stock Cant Less Than A Stock Stored');
                } else {
                    $subStock = $getStuffStock->update([
                        'total_available' => $isStockAvailable,
                        'total_defec' => $isStockDafac,
                    ]);
                    if($subStock) {
                        $getStuffSub  = StuffStock::where('id', $id)->with('stuff')->first();

                        return ApiFormatter::sendResponse(200, true, 'Sucessfully Add A Stock Of Stuff Stock Data', $getStuffSub);
                    }
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, 'bad request', $e->getMessage());
        }
    }

    public function addstock(Request $request, $id)
    {
        try{
            $getStuffStock = StuffStock::find($id);

            if(!$getStuffStock) {
                return ApiFormatter::sendResponse(404, false, 'Data stuff Stock not found');
            }else{
                $this->validate($request, [
                    'total_available' => 'reqoired',
                    'total_defec' => 'reqoired',
                ]);

                $addStock = $getStuffStock->update([
                    'total_available' => $getStuffStock['total_available'] + $request->total_available,
                    'total_defec' => $getStuffStock[ 'total_defec'] + $request->total_defec,
                ]);

                if ($addStock) {
                    $getStockadded = StuffStock::where('id', $id)->with('stuff')->first();

                    return ApiFormatter::sendResponse(200, 'Sucessfully Add A Stock Of Stuff Stock Data', $getStockadded);
                }
            }
        } catch (\Exception $e) {
            return ApiFormatter::sendResponse(400, 'bad request', $e->getMessage());
        }
    }
}
