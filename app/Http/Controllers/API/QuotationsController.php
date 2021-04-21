<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use \stdClass;
use App\Quotation;
use App\QuotationCS;
use App\Orders;
use App\ComputationSheet;
use App\QuotationInitialCharge;
use DB;
use App\QuotationEvaluation;
use App\ExchangeRate;
use App\ClientContacts;

class QuotationsController extends Controller
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
            $sortProp = 'quotation_number';
            $sortOrder = 'asc';
        }

        $valid_quote = Quotation::where('created_at', '<', Carbon::now()->addMonths(-1))->where('quotation_is_valid', 1)->pluck('id');
        Quotation::whereIn('id', $valid_quote)->update(['quotation_is_valid' => 0, 'quotation_is_valid' => 0]);

        if($request['showDeleted'] == 'true'){
            return Quotation::onlyTrashed()->select('quotation.*', 'clients.client_short_name as client_name', 'client_contacts.client_pic as pic_name')
                ->where(function($query) use ($request){
                    $query->where('quotation.quotation_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'quotation.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'quotation.client_pic_id')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Quotation::select('quotation.*', 'clients.client_short_name as client_name', 'client_contacts.client_pic as pic_name')
                ->where(function($query) use ($request){
                    $query->where('quotation.quotation_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('clients', 'clients.id', 'quotation.client_id')
                ->leftJoin('client_contacts', 'client_contacts.id', 'quotation.client_pic_id')
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
            
            $series = '';
            $series_number = 1;
            if(Quotation::withTrashed()->first()) $series_number =  (int)Quotation::select(DB::raw('SUBSTR(`quotation_number`, 6, 11) as quotation_number_substr'))->orderBy('quotation_number_substr', 'desc')->withTrashed()->first()->quotation_number_substr + 1;
            $series_length = 6;
            $series = substr('000000'.$series_number, -$series_length);
            $quotation_number = 'AICI-'.$series;
            
           
            if(count($request['selectedCs']) > 0 || count($request['added_quotation']) > 0){
                $quotation_id = Quotation::insertGetId([
                    'quotation_number' => $quotation_number,
                    'client_id' => $request['quotation']['client_id'],
                    'order_id' => $request['quotation']['order_id'],
                    'client_pic_id' => $request['quotation']['client_pic_id'],
                    'quotation_qty_unit' => $request['cs_qty_unit'],
                    'order_qty_per_quotation_unit' => $request['cs_pieces_per_qty_unit'],
                    'client_pic_id' => $request['quotation']['client_pic_id'],
                    'quotation_specs' => 'TEST',
                    // 'quotation_validity_date' =>  Carbon::now()->addMonths(1),
                    'quotation_status' => 1,
                    'quotation_is_valid' => 1,
                    'remarks' => $request['remarks'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),

                ]);
                for($i = 0; $i < count($request['selectedCs']); $i++){
                    QuotationCS::create([
                        'cs_id'=> $request['selectedCs'][$i]['id'],
                        'quotation_id' => $quotation_id,
                        'cs_control_no'=> $request['selectedCs'][$i]['cs_control_number'],  
                        'cs_qty'=> $request['selectedCs'][$i]['cs_qty'],
                        'cs_unit_amount'=> $request['selectedCs'][$i]['cs_qty_unit'],  
                        'cs_unit_amount_currency'=> $request['selectedCs'][$i]['cs_currency'],  
                        'cs_unit_amount_php'=> $request['selectedCs'][$i]['cs_total_amount'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        
                    ]);
                }
            }
            
            if(count($request['initial_charge']) > 0){
                for($i = 0; $i < count($request['initial_charge']); $i++){
                    QuotationInitialCharge::create([
                    'quotation_id' => $quotation_id,
                    'item_name' => $request['initial_charge'][$i]['item_name'],
                    'item_total_amt' => $request['initial_charge'][$i]['initial_charge_amount'],
                    'item_unit_amt' => $request['initial_charge'][$i]['initial_charge_unit_price'],
                    'item_qty' => $request['initial_charge'][$i]['initial_charge_qty'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    ]);
                }
            }
            if(count($request['added_quotation']) > 0){
                for($i = 0; $i < count($request['added_quotation']); $i++){
                QuotationCS::create([
                    'quotation_id' => $quotation_id,
                    'cs_qty'=> $request['added_quotation'][$i]['quotation_qty'],
                    'cs_unit_amount'=> $request['added_quotation'][$i]['quotation_unit_price'],  
                    'cs_unit_amount_currency'=> $request['added_quotation'][$i]['quotation_currency'],  
                    'cs_unit_amount_php'=> $request['added_quotation'][$i]['quotation_amount'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    
                ]);
                }
            }

            for($i = 0; $i < count($request['checkers']); $i++) {
                QuotationEvaluation::create([
                    'quotation_id' => $quotation_id,
                    'evaluator_id' => $request['checkers'][$i]['evaluator_id'],
                    'evaluator_remarks' => $request['checkers'][$i]['evaluator_remarks'],
                    'evaluation_status' => 1,
                    'evaluation_type' => 1,
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            for($i = 0; $i < count($request['approvers']); $i++) {
                QuotationEvaluation::create([
                    'quotation_id' => $quotation_id,
                    'evaluator_id' => $request['approvers'][$i]['evaluator_id'],
                    'evaluator_remarks' => $request['approvers'][$i]['evaluator_remarks'],
                    'evaluation_status' => 3,
                    'evaluation_type' => 2,
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
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

    public function show(Request $request, $id)
    {   
        $quote = new stdClass();
        $quote->quote = Quotation::where('id', $id)->first();
        $quote->quote_cs = QuotationCS::where('quotation_id', $id)->get();
        $quote->cs = ComputationSheet::where('cs_client_id', $quote->quote['client_id'])->where('cs_is_valid', 1)->get();

        $cs = [];
        for($i = 0; $i < count($quote->quote_cs); $i++){
           $cs = ComputationSheet::where('id', $quote->quote_cs[$i]['cs_id'])->get();
        }
        
        if(count($cs) != 0){
            for($i = 0; $i < count($cs); $i++){
                $quote->order = Orders::where('id', $cs[$i]['client_order_id'])->first();
            }
        }
        
        foreach($quote->quote_cs as $row){
            $row->is_selected = true;
        }
        
        $quote->quote_initial_charge = QuotationInitialCharge::where('quotation_id', $id)->get();

        $quote->checkers = QuotationEvaluation::select('quotation_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'quotation_evaluation.evaluator_id')
            ->where('quotation_id', $id)
            ->where('evaluation_type', 1)
            ->get();

        $quote->approvers = QuotationEvaluation::select('quotation_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'quotation_evaluation.evaluator_id')
            ->where('quotation_id', $id)
            ->where('evaluation_type', 2)
            ->get();

        $quote->remarks = QuotationEvaluation::select('quotation_evaluation.id', 'quotation_evaluation.evaluator_id', 'quotation_evaluation.evaluation_type', 'quotation_evaluation.evaluator_remarks', 'quotation_evaluation.updated_at', 'quotation_evaluation.evaluation_status', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'quotation_evaluation.evaluator_id')
            ->where('quotation_evaluation.quotation_id', $id)
            ->where('quotation_evaluation.evaluator_remarks', '!=', null)
            ->orderBy('quotation_evaluation.evaluation_type')
            ->get();

        $quote->user_id = auth('api')->user()->id;
        return response()->json($quote);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
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
        $quote_cs = QuotationCS::where('quotation_id', $id)->get();
        
        try{
            DB::beginTransaction();

            Quotation::where('id', $id)->update([ 
                'remarks' => $request['quote']['remarks'],
                'quotation_is_valid' => 1,
                'updated_at' => Carbon::now(),
                'quotation_approved_date' =>  NULL,]);
            
            if(count($request['csTable']) > 0){
                for($i = 0; $i < count($request['csTable']); $i++){
                    if($request['csTable'][$i]['quote_cs_id'] != NULL){
                        $quote_cs = QuotationCS::withTrashed()->findOrFail($request['csTable'][$i]['quote_cs_id']);
                        if($request['csTable'][$i]['is_selected'] == false){
                            $quote_cs->delete();
                        }
                        else{
                            $quote_cs->restore();
                        }
                    }elseif($request['csTable'][$i]['quote_cs_id'] == NULL && $request['csTable'][$i]['is_selected'] == true){
                        QuotationCS::create([
                            'cs_id' => $request['csTable'][$i]['id'],
                            'quotation_id' => $id,
                            'cs_control_no' => $request['csTable'][$i]['cs_control_number'],
                            'cs_qty'=> $request['csTable'][$i]['cs_qty'],
                            'cs_unit_amount'=> $request['csTable'][$i]['cs_qty_unit'],  
                            'cs_unit_amount_currency'=> $request['csTable'][$i]['cs_currency'],  
                            'cs_unit_amount_php'=> $request['csTable'][$i]['cs_total_amount'],
                            'employee_id' => auth('api')->user()->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            
                        ]); 
                    }
                }
            }

            if(count($request['added_quotation']) > 0){
                for($i = 0; $i < count($request['added_quotation']); $i++){
                    $quote_cs = QuotationCS::where('quotation_id', $id)->where('cs_qty', '!=', $request['added_quotation'][$i]['quotation_qty'])
                    ->where('cs_unit_amount','!=', $request['added_quotation'][$i]['quotation_unit_price']);
                    $quote_cs->create([
                        'quotation_id' => $id,
                        'cs_qty'=> $request['added_quotation'][$i]['quotation_qty'],
                        'cs_unit_amount'=> $request['added_quotation'][$i]['quotation_unit_price'],
                        // For review
                        'cs_unit_amount_currency'=> $request['csTable'][$i]['cs_currency'],  
                        'cs_unit_amount_php'=> $request['added_quotation'][$i]['quotation_amount'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            
            $initial_charge = QuotationInitialCharge::where('quotation_id', $id);

            if(count($request['initial_charge']) > 0){
                $initial_charge->delete();
                for($i = 0; $i < count($request['initial_charge']); $i++){

                    $initial = $initial_charge->where('item_qty', $request['initial_charge'][$i]['item_qty'])
                    ->where('item_unit_amt',$request['initial_charge'][$i]['item_unit_amt'])
                    ->withTrashed()
                    ->first();

                    if($initial){
                        $initial_charge->where('item_qty', $request['initial_charge'][$i]['item_qty'])
                        ->where('item_unit_amt',$request['initial_charge'][$i]['item_unit_amt'])
                        ->restore();
                    }
                    else{
                        $initial_charge->create([
                        'quotation_id' => $id,
                        'item_name' => $request['initial_charge'][$i]['item_name'], 
                        'item_qty' => $request['initial_charge'][$i]['item_qty'],
                        'item_unit_amt' => $request['initial_charge'][$i]['item_unit_amt'],
                        'item_total_amt' => $request['initial_charge'][$i]['item_total_amt'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        ]);
                    }
                }
            }
            else $initial_charge->delete();
                    

            $eval = QuotationEvaluation::where('quotation_id', $id)->get();
            $quote_eval = QuotationEvaluation::where('quotation_id', $id);
            // as of the moment lol
            $quote_eval->delete();
            for($i = 0; $i < count($request['checkers']); $i++) {
               $checker = $quote_eval->where('evaluation_type', 1)
               ->where('id', $request['checkers'][$i]['id'])
               ->withTrashed()
               ->first();
                if($checker){
                    $quote_eval->where('id', $request['checkers'][$i]['id'])
                    ->where('evaluation_type', 1)
                    ->restore();
                }
                else{
                    $quote_eval->create([
                        'quotation_id' => $id,
                        'evaluator_id' => $request['checkers'][$i]['evaluator_id'],
                        'evaluator_remarks' => $request['checkers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 1,
                        'evaluation_type' => 1,
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(), 
                        ]);
                }
            }

            for($i = 0; $i < count($request['approvers']); $i++) {
                $approvers = $quote_eval->where('evaluation_type', 1)
                ->where('id', $request['approvers'][$i]['id'])
                ->withTrashed()
                ->first();
                 if($approvers){
                     $quote_eval->where('id', $request['approvers'][$i]['id'])
                     ->where('evaluation_type', 2)
                     ->restore();
                 }
                 else{
                     $quote_eval->create([
                         'quotation_id' => $id,
                         'evaluator_id' => $request['approvers'][$i]['evaluator_id'],
                         'evaluator_remarks' => $request['approvers'][$i]['evaluator_remarks'],
                         'evaluation_status' => 3,
                         'evaluation_type' => 2,
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
    
            $supplier = Quotation::withTrashed()->findOrFail($id);

            if($supplier->deleted_at){
                $supplier->restore();
            }
            else{
                $supplier->delete();
            }

            DB::commit();
            return ['message' => 'Quotation successfully deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
    public function getOrders($id){
        
        $client_cs = ComputationSheet::where('cs_client_id', $id)->get();
        if(count($client_cs) == 0) $orders = [];
        else{ $orders = Orders::where('id', $client_cs[0]['client_order_id'])->get();
            foreach($orders as $row){
                $row->is_selected = false;
            }
        }
       return $orders;
    }

    public function selectControlNumberList(Request $request, $id){
        
        $quote_id = $request['quote_id'];

        $cs = new stdClass();

        $cs = ComputationSheet::whereIn('computation_sheet.id', $valid_cs_id)
        ->leftJoin('quotation_cs', function ($join) use ($quote_id){
            $join->on('computation_sheet.id', '=', 'quotation_cs.cs_id')
                 ->where('quotation_cs.quotation_id', '=', $quote_id);
        })
        ->select('computation_sheet.id','computation_sheet.cs_control_number', 'computation_sheet.cs_client_id',
        'computation_sheet.client_order_id','computation_sheet.cs_qty', 'computation_sheet.cs_qty_unit','computation_sheet.cs_currency',
        'computation_sheet.cs_total_amount',
        'quotation_cs.cs_id', 'quotation_cs.deleted_at', 'quotation_cs.id as quote_cs_id')
        ->get();
     
        foreach($cs as $row){ 
            if($row->quote_cs_id == NULL || $row->deleted_at != NULL){
                $row->is_selected = false;
            }
            else{ $row->is_selected = true;
            }
        }
         
        return $cs;
       
    }
    public function copyQuotation(Request $request){
        {
            try{
                DB::beginTransaction(); 
                
                $series = '';
                $series_number = 1;
                if(Quotation::first()) $series_number =  (int)Quotation::select(DB::raw('SUBSTR(`quotation_number`, 6, 11) as quotation_number_substr'))->orderBy('quotation_number_substr', 'desc')->first()->quotation_number_substr + 1;
                $series_length = 6;
                $series = substr('000000'.$series_number, -$series_length);
                $quotation_number = 'AICI-'.$series;
                
                
                if(count($request['csTable']) > 0 || count($request['added_quotation']) > 0){
                    $quotation_id = Quotation::insertGetId([
                        'quotation_number' => $quotation_number,
                        'client_id' => $request['quotation']['client_id'],
                        'client_name' => $request['quote']['client_name'],
                        'client_address' => $request['quote']['client_address'],
                        'quotation_qty_unit' => $request['cs_qty_unit'],
                        'order_qty_per_quotation_unit' => $request['cs_pieces_per_qty_unit'],
                        'client_pic_id' => $request['quotation']['client_pic_id'],
                        'quotation_specs' => 'TEST',
                        'quotation_status' => '1',
                        'remarks' => $request['remarks'],
                        'quotation_is_valid' => '1',
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),

                    ]);
                    for($i = 0; $i < count($request['csTable']); $i++){
                        QuotationCS::create([
                            'cs_id'=> $request['csTable'][$i]['id'],
                            'quotation_id' => $quotation_id,
                            'cs_control_no'=> $request['csTable'][$i]['cs_control_number'],  
                            'cs_qty'=> $request['csTable'][$i]['cs_qty'],
                            'cs_unit_amount'=> $request['csTable'][$i]['cs_qty_unit'],  
                            'cs_unit_amount_currency'=> $request['csTable'][$i]['cs_currency'],  
                            'cs_unit_amount_php'=> $request['csTable'][$i]['cs_total_amount'],
                            'employee_id' => auth('api')->user()->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            
                        ]);
                    }
                }
                
                if(count($request['initial_charge']) > 0){
                    for($i = 0; $i < count($request['initial_charge']); $i++){
                        QuotationInitialCharge::create([
                        'quotation_id' => $quotation_id,
                        'item_name' => $request['initial_charge'][$i]['item_name'],
                        'item_total_amt' => $request['initial_charge'][$i]['item_total_amt'],
                        'item_unit_amt' => $request['initial_charge'][$i]['item_unit_amt'],
                        'item_qty' => $request['initial_charge'][$i]['item_qty'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        ]);
                    }
                }
                if(count($request['added_quotation']) > 0){
                    for($i = 0; $i < count($request['added_quotation']); $i++){
                    QuotationCS::create([ 
                        'quotation_id' => $quotation_id,
                        'cs_qty'=> $request['added_quotation'][$i]['quotation_qty'],
                        'cs_unit_amount'=> $request['added_quotation'][$i]['quotation_unit_price'],  
                        'cs_unit_amount_currency'=> $request['added_quotation'][$i]['quotation_currency'],  
                        'cs_unit_amount_php'=> $request['added_quotation'][$i]['quotation_amount'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        
                    ]);
                    }
                }

                for($i = 0; $i < count($request['checkers']); $i++) {
                    QuotationEvaluation::create([
                        'quotation_id' => $quotation_id,
                        'evaluator_id' => $request['checkers'][$i]['evaluator_id'],
                        'evaluator_remarks' => $request['checkers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 1,
                        'evaluation_type' => 1,
                        'employee_id' => auth('api')->user()->id
                    ]);
                }

                for($i = 0; $i < count($request['approvers']); $i++) {
                    QuotationEvaluation::create([
                        'quotation_id' => $quotation_id,
                        'evaluator_id' => $request['approvers'][$i]['evaluator_id'],
                        'evaluator_remarks' => $request['approvers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 3,
                        'evaluation_type' => 2,
                        'employee_id' => auth('api')->user()->id
                    ]);
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
    }

    public function evaluateQuote(Request $request){
        try{
            DB::beginTransaction();

            if($request['evaluation_status'] == 1) $evaluation_status = 2;
            else $evaluation_status = 1;

            if($request['evaluation_type'] == 2){
                if($request['evaluation_status'] == 3) $evaluation_status = 4;
                else $evaluation_status = 3;
            }
            
            $evaluation = QuotationEvaluation::where('id', $request['id'])->where('quotation_id', $request['quote_id'])->update([
                'evaluation_status' => $evaluation_status,
                'evaluator_remarks' => $request['message']
            ]);
            
            DB::commit();
            return ['message' => 'Quote evaluation successfully completed.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function updateEvaluation(Request $request, $id){
        
        try{
            DB::beginTransaction();

            $eval = QuotationEvaluation::where('quotation_id', $id)->get();

            for ($i=0; $i < count($eval) ; $i++) { 
                
                if($eval[$i]['evaluation_type'] == 1)
                QuotationEvaluation::where('evaluation_type', $eval[$i]['evaluation_type'])->where('quotation_id', $id)
                ->where('id', $eval[$i]['id'])
                ->update([
                    'evaluation_status' => 1,
                ]);
                elseif ($eval[$i]['evaluation_type'] == 2) QuotationEvaluation::where('evaluation_type', $eval[$i]['evaluation_type'])->where('quotation_id', $id)
                ->where('id', $eval[$i]['id'])
                ->update([
                    'evaluation_status' => 3,
                ]);

            }
           
            DB::commit();
            return ['message' => 'Quote evaluation successfully updated.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function createCopy($id){

        try{
            DB::beginTransaction(); 
            
            $quote = Quotation::where('id', $id)->first();
            $quote_cs = QuotationCS::where('quotation_id', $quote->id)->get();
            $eval = QuotationEvaluation::where('quotation_id', $quote->id)->get();
            $charge = QuotationInitialCharge::where('quotation_id', $quote->id)->get();
            // return $eval;
            $series = '';
            $series_number = 1;
            if(Quotation::first()) $series_number =  (int)Quotation::select(DB::raw('SUBSTR(`quotation_number`, 6, 11) as quotation_number_substr'))->orderBy('quotation_number_substr', 'desc')->first()->quotation_number_substr + 1;
            $series_length = 6;
            $series = substr('000000'.$series_number, -$series_length);
            $quotation_number = 'AICI-'.$series;
            
           
            $quotation_id = Quotation::insertGetId([
                'quotation_number' => $quotation_number,
                'client_id' => $quote['client_id'],
                'client_pic_id' => $quote['client_pic_id'],
                'quotation_qty_unit' => $quote['quotation_qty_unit'],
                'order_qty_per_quotation_unit' => $quote['order_qty_per_quotation_unit'],
                'quotation_specs' => $quote['quotation_specs'],
                'quotation_status' => 1,
                'quotation_is_valid' => 1,
                'remarks' => $quote['remarks'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),

            ]);
                            
            for($i = 0; $i < count($quote_cs); $i++){
                QuotationCS::create([
                    'cs_id'=> $quote_cs[$i]['cs_id'],
                    'quotation_id' => $quotation_id,
                    'cs_control_no'=> $quote_cs[$i]['cs_control_no'],  
                    'cs_qty'=> $quote_cs[$i]['cs_qty'],
                    'cs_unit_amount'=> $quote_cs[$i]['cs_unit_amount'],  
                    'cs_unit_amount_currency'=> $quote_cs[$i]['cs_unit_amount_currency'],  
                    'cs_unit_amount_php'=> $quote_cs[$i]['cs_unit_amount_php'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
         
            
            if(count($charge) > 0){
                for($i = 0; $i < count($charge); $i++){
                    QuotationInitialCharge::create([
                    'quotation_id' => $quotation_id,
                    'item_name' => $charge[$i]['item_name'],
                    'item_total_amt' => $charge[$i]['item_total_amt'],
                    'item_unit_amt' => $charge[$i]['item_unit_amt'],
                    'item_qty' => $charge[$i]['item_qty'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    ]);
                }
            }
           
            for($i = 0; $i < count($eval); $i++) {
                if($eval[$i]['evaluation_type'] == 1){
                    QuotationEvaluation::create([
                        'quotation_id' => $quotation_id,
                        'evaluator_id' => $eval[$i]['evaluator_id'],
                        'evaluator_remarks' => $eval[$i]['evaluator_remarks'],
                        'evaluation_status' => 1,
                        'evaluation_type' => 1,
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else
                    QuotationEvaluation::create([
                        'quotation_id' => $quotation_id,
                        'evaluator_id' => $eval[$i]['evaluator_id'],
                        'evaluator_remarks' => $eval[$i]['evaluator_remarks'],
                        'evaluation_status' => 3,
                        'evaluation_type' => 2,
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
            }


            DB::commit();
            return ['message' => 'Created'];
        }
        catch(\Exception $e){ 
            DB::rollback();
            throw $e;
        }



        return ['quote'=> $quote,
        'cs'=> $quote_cs,
        'eval'=> $eval,
        'charge'=> $charge];
    }

    public function getClientPIC(Request $request, $id){
        return ClientContacts::where('client_id', $id)->get();
    }
    
    public function orderSelected(Request $request, $id){
        
        $cs = new stdClass();

        $cs_cn_key_array = ComputationSheet::select(DB::raw('SUBSTR(`cs_control_number`, 1, 12) as cs_cn_key'))
            ->where('client_order_id', $id)
            ->where('cs_client_id', $request['params']['client_id'])
            ->groupBy('cs_cn_key')
            ->pluck('cs_cn_key')
            ->toArray();

        $cs_orders = ComputationSheet::where('client_order_id', $id)->get();
        $valid_cs_id = [];
        for ($i = 0; $i < count($cs_cn_key_array); $i++) { 
            $cs_latest_version = ComputationSheet::where('cs_control_number', 'LIKE', '%'.$cs_cn_key_array[$i].'%')
                ->orderBy('cs_version_number', 'desc')
                ->first();
            
            if($cs_latest_version->cs_is_valid == 1) array_push($valid_cs_id, $cs_latest_version->id);
        }

        $cs->cs = ComputationSheet::whereIn('id', $valid_cs_id)->get();
        


        return response()->json($cs);
    }
}

