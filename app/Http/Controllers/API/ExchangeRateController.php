<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ExchangeRate;
use App\User;
use \stdClass;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $exchange_rates = [];
        $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
        for ($i = 1; $i <= 4; $i++) {
            $exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first();
            $exchange_rates[$exchange_rate_label[$i - 1]] = $exchange_rate;
        }

        $employees_array = ExchangeRate::pluck('employee_id')->toArray();
        $employees = User::select('id', 'first_name', 'last_name')->whereIn('id', $employees_array)->get();

        if($request['conversion_id']) $exchange_rate_history = ExchangeRate::where('conversion_id', $request['conversion_id'])->orderBy('id', 'desc')->paginate($request['take']);
        else $exchange_rate_history = ExchangeRate::orderBy('id', 'desc')->paginate($request['take']);

        return [
            'exchange_rates' => $exchange_rates, 
            'history' => $exchange_rate_history,
            'employees' => $employees,
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id = $request['id'];

        if($id == 1) $rate = $request['PHPToUSD'];
        else if($id == 2) $rate = $request['USDToPHP'];
        else if($id == 3) $rate = $request['JPYToUSD'];
        else $rate = $request['JPYToPHP'];

        ExchangeRate::create([
            'conversion_id' => $id,
            'rate' => $rate,
            'employee_id' => auth('api')->user()->id,
        ]);

        return ['message' => 'Foreign exchange saved!'];
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
        //
    }
}
