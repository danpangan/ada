<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use App\ClientPODeliveryMonitoring;

class ClientPODeliveryMonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'po_id';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return ClientPODeliveryMonitoring::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('po_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return ClientPODeliveryMonitoring::where(function($query) use ($request){
                    $query->where('po_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
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
        try{
            DB::beginTransaction();
    
            $supplier = ClientPODeliveryMonitoring::withTrashed()->findOrFail($id);

            if($supplier->deleted_at){
                $supplier->restore();
            }
            else{
                $supplier->delete();
            }
 
            DB::commit();
            return ['message' => 'Order successfully deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
