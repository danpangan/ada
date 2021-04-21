<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ClientForecast;
use App\ClientForecastItems;
use Carbon\Carbon;
use App\Client;
use DB;

class ClientForecastController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $client_id = $request['client_id'];

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'forecast_id';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return ClientForecast::onlyTrashed()
                ->select('id', 'client_number', 'forecast_number')
                ->where('client_id', $client_id)
                ->where(function($query) use ($request){
                    $query->where('forecast_number','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return ClientForecast::select('id', 'client_number', 'forecast_number')
                ->where('client_id', $client_id)
                ->where(function($query) use ($request){
                    $query->where('forecast_number','like','%'.$request['search'].'%');
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
        $forecast_list = $request['forecastList'];
        $client_id = $request['clientId'];

        $client = Client::where('id', $client_id)->first();

        $client_series = substr($client->client_number, -4);

        $forecast_series = '';
        $forecast_series_number = (int)substr(ClientForecast::orderBy('forecast_number','desc')->first()->forecast_number, 5, 8) + 1;
        $forecast_series_length = 4;
        $forecast_series = substr('0000'.$forecast_series_number, -$forecast_series_length);

        $forecast_number = $client_series.'-'.$forecast_series;

        try{
            $forecast_id = ClientForecast::insertGetId([
                'client_id' => $client_id,
                'forecast_number' => $forecast_number,
                'client_number' => $client->client_number,
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
            ]);

            for($i = 0; $i < count($forecast_list); $i++){
                ClientForecastItems::create([
                    'order_id' => $forecast_list[$i]['order_id'],
                    'forecast_id' => $forecast_id,
                    'forecast_month' => $forecast_list[$i]['month'],
                    'forecast_year' => $forecast_list[$i]['year'],
                    'forecast_quantity' => $forecast_list[$i]['quantity'],
                    'forecast_quantity_unit' => $forecast_list[$i]['quantity_unit'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            DB::commit();
            return ['message' => 'Forecast successfully saved!'];
        } 
        catch(\Exception $e){
            DB::rollback();
            throw $e;
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
        $forecast = ClientForecast::select('client_forecast.id as forecast_id', 'client_forecast.forecast_number', 'clients.id as client_id', 'clients.client_number', 'clients.client_name')
            ->where('client_forecast.id', $id)
            ->leftJoin('clients', 'clients.id', 'client_id')
            ->first();
        return $forecast;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {

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

            $client_forecast = ClientForecast::withTrashed()->findOrFail($id);

            if($client_forecast->deleted_at){
                $client_forecast->restore();
            }
            else{
                $client_forecast->delete();
            }
    
            DB::commit();
            return ['message' => 'Forecast deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
