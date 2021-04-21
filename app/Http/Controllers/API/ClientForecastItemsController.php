<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ClientForecast;
use App\ClientForecastItems;
use Carbon\Carbon;
use App\Client;
use DB;

class ClientForecastItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return $request;

        $forecast_id = (int)$request['forecast_id'];

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'id';
            $sortOrder = 'desc';
        }

        if($request['showDeleted'] == 'true'){
            return ClientForecastItems::onlyTrashed()
                ->where('forecast_id', $forecast_id)
                ->where(function($query) use ($request){
                    $query->where('order_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return ClientForecastItems::where('forecast_id', $forecast_id)
                ->where(function($query) use ($request){
                    $query->where('order_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
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
        try{
            ClientForecastItems::create([
                'order_id' => $request['order_id'],
                'forecast_id' => $request['forecast_id'],
                'forecast_month' => $request['month'],
                'forecast_year' => $request['year'],
                'forecast_quantity' => $request['quantity'],
                'forecast_quantity_unit' => $request['quantity_unit'],
                'employee_id' => auth('api')->user()->id,
            ]);

            DB::commit();
            return ['message' => 'Forecast successfully updated!'];
        } 
        catch(\Exception $e){
            DB::rollback();
            throw $e;
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
        // return $id;

        try{
            DB::beginTransaction();

            $client_forecast_item = ClientForecastItems::withTrashed()->findOrFail($id);

            if($client_forecast_item->deleted_at){
                $client_forecast_item->restore();
            }
            else{
                $client_forecast_item->delete();
            }
    
            DB::commit();
            return ['message' => 'Forecast item deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
