<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use \stdClass;
use App\ClientPO;
use App\Quotation;
use App\QuotationCS;
use App\Orders;
use App\ComputationSheet;
use App\QuotationInitialCharge;
use DB;
use App\QuotationEvaluation;
use App\ClientPOItem;

class ClientPOController extends Controller
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
            $sortProp = 'id';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return ClientPO::onlyTrashed()->select('client_po.*', 'clients.client_short_name as client_short_name', 'client_contacts.client_pic as pic_name')
                ->where(function($query) use ($request){
                    $query->where('client_po.client_po_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'client_po.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'client_po.client_pic')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return ClientPO::select('client_po.*', 'clients.client_short_name as client_short_name', 'client_contacts.client_pic as pic_name')
                ->where(function($query) use ($request){
                    $query->where('client_po.client_po_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'client_po.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'client_po.client_pic')
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
        // ClientPO::
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
            
            $po_id = ClientPO::insertGetId([
            'client_po_number' => $request['po_order'],
            'client_id' => $request['client_id'],
            'client_po_date' => $request['po_date'],
            'client_po_delivery_date' => $request['delivery_date'],
            'client_po_received_date' => $request['receive_date'],
            
            'client_po_amount' => $request['total'],
            'client_po_amount_currency' => 'PHP',
            'client_po_amount_php' => $request['total'],
            'client_po_status' => 1,
            'client_pic' => 1,
            'remarks' => $request['remarks'],
            'client_order_id' => $request['order_id'],
            'employee_id' => auth('api')->user()->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            ]);

            if(count($request['quotations']) > 0){
                for ($i=0; $i < count($request['quotations']) ; $i++) { 
                    ClientPOItem::create([
                        'client_po_id' => $po_id,
                        'quotation_id' => $request['quotations'][$i]['cs_qty'],
                        'cs_control_number' => $request['quotations'][$i]['cs_control_no'],
                        'cs_id' => $request['quotations'][$i]['cs_id'],
                        'order_id' => $request['order_id'],
                        'client_po_item_qty' => $request['quotations'][$i]['cs_qty'],
                        'client_po_item_qty_unit' => $request['quotations'][$i]['cs_unit_qty'],
                        // ano to?
                        'client_po_item_qty_remaining' => $request['quotations'][$i]['cs_qty'],
                        'client_po_item_unit_amount' => $request['quotations'][$i]['cs_unit_amount'],
                        'client_po_item_total_amount' => $request['quotations'][$i]['cs_unit_amount_php'],
                        'client_po_item_currency' => $request['quotations'][$i]['cs_unit_amount_currency'],

                        // need to convert
                        // 'client_po_item_unit_amount_php' => $request['quotations'][$i]['aw'],
                        // what is this?
                        // 'client_po_item_status' => $request['quotations'][$i]['aw'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
                
            
            DB::commit();
            return ['message' => 'Created'];
        }
        catch(\Exception $e){ 
            DB::rollback();
            throw $e;
        }

        return ['item_name' => $request['initial_charge']['item_name']];
    }
 

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // SHOW CONTROL NUMBER LIST TABLE
    public function show($id)
    {   
        // id = po_id
        try{
            DB::beginTransaction();
           
            $po = ClientPO::where('id', $id)->first();
            $po_items = ClientPOItem::where('client_po_id', $id)->get();

            DB::commit();
            return ['po'=> $po, 'po_items'=> $po_items];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
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
        try{
            DB::beginTransaction();
            $po = $request['purchase_order'];
            $po_id = ClientPO::where('id', $id)->update([
                'client_po_number' => $po['client_po_number'],
                'client_id' => $po['client_id'],
                'client_po_date' => $po['client_po_date'],
                'client_po_delivery_date' => $po['client_po_delivery_date'],
                'client_po_received_date' => $po['client_po_received_date'],
                
                'client_po_amount' => $po['client_po_amount'],
                'client_po_amount_currency' => $po['client_po_amount_currency'],
                'client_po_amount_php' => $po['client_po_amount_php'],
                'client_po_status' => $po['client_po_status'],
                'client_pic' => $po['client_pic'],
                'remarks' => $po['remarks'],
                'client_order_id' => $po['client_order_id'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
    
                ]);
    
                if(count($request['quotations']) > 0){
                    ClientPOItem::where('client_po_id', $id)->forceDelete();
                    for ($i=0; $i < count($request['quotations']) ; $i++) {
                        ClientPOItem::create([
                            'client_po_id' => $id,
                            'quotation_id' => $request['quotations'][$i]['quotation_id'],
                            'cs_control_number' => $request['quotations'][$i]['cs_control_number'],
                            'cs_id' => $request['quotations'][$i]['cs_id'],
                            'order_id' => $request['order_id'],
                            'client_po_item_qty' => $request['quotations'][$i]['client_po_item_qty'],
                            'client_po_item_qty_unit' => $request['quotations'][$i]['client_po_item_qty_unit'],
                            'client_po_item_qty_remaining' => $request['quotations'][$i]['client_po_item_qty'],
                            'client_po_item_unit_amount' => $request['quotations'][$i]['client_po_item_unit_amount'],
                            'client_po_item_total_amount' => $request['quotations'][$i]['client_po_item_total_amount'],
                            'client_po_item_currency' => $request['quotations'][$i]['client_po_item_currency'],
    
                            // need to convert
                            // 'client_po_item_unit_amount_php' => $request['quotations'][$i]['aw'],
                            'client_po_item_status' => $request['quotations'][$i]['client_po_item_status'],
                            'employee_id' => auth('api')->user()->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }
             

            DB::commit();
            return ['message' => 'Updated'];

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
    
            $po = ClientPO::withTrashed()->findOrFail($id);

           
            if($po->deleted_at){
                $po->restore();
            }
            else{
                $po->delete();
            }

            DB::commit();
            return ['message' => 'PO successfully deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
    
    public function orderWasSelected(Request $request, $id){
        // $id = client_id
        $quote = Quotation::leftJoin('quotation_cs', 'quotation_cs.quotation_id', 'quotation.id')
        ->where('client_id', $id)->where('quotation_is_valid', 1)->get();
        return $quote;
    }
}
