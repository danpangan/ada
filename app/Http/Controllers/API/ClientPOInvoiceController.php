<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ClientPOInvoice;
use DB;
use Carbon\Carbon;

class ClientPOInvoiceController extends Controller
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
            $sortProp = 'si_number';
            $sortOrder = 'desc';
        }

        if($request['showDeleted'] == 'true'){
            return ClientPOInvoice::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('si_number','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return ClientPOInvoice::where(function($query) use ($request){
                    $query->where('si_number','like','%'.$request['search'].'%');
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
        try{
            DB::beginTransaction();

            $si_number = '';
            $series_number = 1;
            if(ClientPOInvoice::first()) $series_number =  (int)ClientPOInvoice::orderBy('si_number', 'desc')->first()->si_number + 1;
            $series_length = 7;
            $si_number = substr('0000000'.$series_number, -$series_length);

            $ss_id = ClientPOInvoice::insertGetId([
                'client_id' => $request['invoice']['client_id'], 
                'si_number' => $si_number,
                'si_sold' => $request['invoice']['si_sold'],
                'si_attention' => $request['invoice']['si_attention'],
                'si_address' => $request['invoice']['si_address'],
                'si_status' => 1,
                'si_is_printed' => 1,
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Sales invoice successfully registered.'];
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
        $invoice = ClientPOInvoice::where('id', $id)->first();

        return [
            'invoice' => $invoice
        ];
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
            DB::beginTransaction();

            $ss_id = ClientPOInvoice::where('id', $id)->update([
                'si_sold' => $request['invoice']['si_sold'],
                'si_attention' => $request['invoice']['si_attention'],
                'si_address' => $request['invoice']['si_address'],
                'si_status' => 1,
                'si_is_printed' => 1,
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Sales invoice successfully updated.'];
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
        try{
            DB::beginTransaction();
    
            $invoice = ClientPOInvoice::withTrashed()->findOrFail($id);

            if($invoice->deleted_at){
                $invoice->restore();
            }
            else{
                $invoice->delete();
            }

            DB::commit();
            return ['message' => 'Invoice successfully deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
