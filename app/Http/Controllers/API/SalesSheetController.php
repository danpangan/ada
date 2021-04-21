<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use \stdClass;
use Carbon\Carbon;
use App\SalesSheet;
use App\SalesSheetEvaluation;

class SalesSheetController extends Controller
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
            $sortProp = 'ss_reference_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return SalesSheet::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('ss_reference_number','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return SalesSheet::where(function($query) use ($request){
                    $query->where('ss_reference_number','like','%'.$request['search'].'%');
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

            $series = '';
            $series_number = 1;
            if(SalesSheet::first()) $series_number =  (int)SalesSheet::select(DB::raw('SUBSTR(`ss_reference_number`, 9, 11) as ss_reference_number_substr'))->orderBy('ss_reference_number_substr', 'desc')->first()->ss_reference_number_substr + 1;
            $series_length = 3;
            $series = substr('000'.$series_number, -$series_length);

            $date_now = Carbon::now();
            $year = $date_now->year;
            $month = $date_now->format('m');

            $ss_reference_number = $year.'-'.$month.'-'.$series;

            $ss_id = SalesSheet::insertGetId([
                'ss_reference_number' => $ss_reference_number, 
                'cs_id' => $request['ss']['cs_id'], 
                'ss_qty' => $request['ss']['ss_qty'], 
                'ss_qty_remaining' => $request['ss']['ss_qty_remaining'],
                'ss_total_amount' => $request['ss']['ss_total_amount'],
                'ss_cost_price' => $request['ss']['ss_cost_price'],
                'ss_cost_rate' => $request['ss']['ss_cost_rate'],
                'ss_gross_sales' => $request['ss']['ss_gross_sales'],
                'ss_sales_price' => $request['ss']['ss_sales_price'],
                'ss_gross_profit' => $request['ss']['ss_gross_profit'],
                'ss_quotation_qty' => $request['ss']['ss_quotation_qty'],
                'ss_payment_method' => $request['ss']['ss_payment_method'],
                'ss_status' => 1,
                'ss_islinked' => 0,
                'ss_is_closed' => 0,
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            for($i = 0; $i < count($request['ss_evaluation_checkers']); $i++) {
                SalesSheetEvaluation::create([
                    'ss_id' => $ss_id, 
                    'evaluator_id' => $request['ss_evaluation_checkers'][$i]['evaluator_id'],
                    'evaluation_type' => 1,
                    'evaluator_remarks' => $request['ss_evaluation_checkers'][$i]['evaluator_remarks'],
                    'evaluation_status' => 1,
                    'employee_id' => auth('api')->user()->id
                ]);
            }

            for($i = 0; $i < count($request['ss_evaluation_approvers']); $i++) {
                SalesSheetEvaluation::create([
                    'ss_id' => $ss_id, 
                    'evaluator_id' => $request['ss_evaluation_approvers'][$i]['evaluator_id'],
                    'evaluation_type' => 2,
                    'evaluator_remarks' => $request['ss_evaluation_approvers'][$i]['evaluator_remarks'],
                    'evaluation_status' => 3,
                    'employee_id' => auth('api')->user()->id
                ]);
            }

            DB::commit();
            return ['message' => 'Sales sheet successfully registered.'];
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
        $ss = SalesSheet::withTrashed()->where('id', $id)->first();

        $ss_evaluation_checkers = SalesSheetEvaluation::select('sales_sheet_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'sales_sheet_evaluation.evaluator_id')
            ->where('ss_id', $id)
            ->where('evaluation_type', 1)
            ->get();

        $ss_evaluation_approvers = SalesSheetEvaluation::select('sales_sheet_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'sales_sheet_evaluation.evaluator_id')
            ->where('ss_id', $id)
            ->where('evaluation_type', 2)
            ->get();

        $ss_evaluation_remarks = SalesSheetEvaluation::select('sales_sheet_evaluation.id', 'sales_sheet_evaluation.evaluator_id', 'sales_sheet_evaluation.evaluation_type', 'sales_sheet_evaluation.evaluator_remarks', 'sales_sheet_evaluation.updated_at', 'sales_sheet_evaluation.evaluation_status', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'sales_sheet_evaluation.evaluator_id')
            ->where('sales_sheet_evaluation.ss_id', $id)
            ->where('sales_sheet_evaluation.evaluator_remarks', '!=', null)
            ->orderBy('sales_sheet_evaluation.evaluation_type')
            ->get();

        $user_id = auth('api')->user()->id;
        
        return [
            'ss' => $ss,
            'ss_evaluation_checkers' => $ss_evaluation_checkers,
            'ss_evaluation_approvers' => $ss_evaluation_approvers,
            'ss_evaluation_remarks' => $ss_evaluation_remarks,
            'user_id' => $user_id
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

            $ss_id = SalesSheet::where('id', $id)->update([
                'ss_qty' => $request['ss']['ss_qty'], 
                'ss_qty_remaining' => $request['ss']['ss_qty_remaining'],
                'ss_total_amount' => $request['ss']['ss_total_amount'],
                'ss_cost_price' => $request['ss']['ss_cost_price'],
                'ss_cost_rate' => $request['ss']['ss_cost_rate'],
                'ss_gross_sales' => $request['ss']['ss_gross_sales'],
                'ss_sales_price' => $request['ss']['ss_sales_price'],
                'ss_gross_profit' => $request['ss']['ss_gross_profit'],
                'ss_quotation_qty' => $request['ss']['ss_quotation_qty'],
                'ss_payment_method' => $request['ss']['ss_payment_method'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            $ss_checkers_id = SalesSheetEvaluation::where('ss_id', $id)
                ->where('evaluation_type', 1)
                ->pluck('id')
                ->toArray();

            $ss_evaluation_checkers = $request['ss_evaluation_checkers'];
            $updated_ss_checkers_id = [];
            foreach($ss_evaluation_checkers as $ss_evaluation_checker){
                array_push($updated_ss_checkers_id, $ss_evaluation_checker['id']);
            }

            $deleted_ids = array_diff($ss_checkers_id, $updated_ss_checkers_id);
            SalesSheetEvaluation::whereIn('id', $deleted_ids)
                ->where('ss_id', $id)
                ->where('evaluation_type', 1)
                ->delete();

            for($i = 0; $i < count($request['ss_evaluation_checkers']); $i++) {
                $checker = SalesSheetEvaluation::where('id', $request['ss_evaluation_checkers'][$i]['id'])
                    ->where('ss_id', $id)
                    ->where('evaluation_type', 1)
                    ->first();

                if($checker){
                    SalesSheetEvaluation::where('id', $request['ss_evaluation_checkers'][$i]['id'])->update([
                        'evaluator_id' => $request['ss_evaluation_checkers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['ss_evaluation_checkers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['ss_evaluation_checkers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 1,
                        'employee_id' => auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    SalesSheetEvaluation::create([
                        'ss_id' => $id, 
                        'evaluator_id' => $request['ss_evaluation_checkers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['ss_evaluation_checkers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['ss_evaluation_checkers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 1,
                        'employee_id' => auth('api')->user()->id
                    ]);
                }
            }

            $ss_approvers_id = SalesSheetEvaluation::where('ss_id', $id)
                ->where('evaluation_type', 2)
                ->pluck('id')
                ->toArray();

            $ss_evaluation_approvers = $request['ss_evaluation_approvers'];
            $updated_ss_approvers_id = [];
            foreach($ss_evaluation_approvers as $ss_evaluation_approver){
                array_push($updated_ss_approvers_id, $ss_evaluation_approver['id']);
            }

            $deleted_ids = array_diff($ss_approvers_id, $updated_ss_approvers_id);
            SalesSheetEvaluation::whereIn('id', $deleted_ids)
                ->where('ss_id', $id)
                ->where('evaluation_type', 2)
                ->delete();

            for($i = 0; $i < count($request['ss_evaluation_approvers']); $i++) {
                $approver = SalesSheetEvaluation::where('id', $request['ss_evaluation_approvers'][$i]['id'])
                    ->where('ss_id', $id)
                    ->where('evaluation_type', 2)
                    ->first();

                if($approver){
                    SalesSheetEvaluation::where('id', $request['ss_evaluation_approvers'][$i]['id'])->update([
                        'evaluator_id' => $request['ss_evaluation_approvers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['ss_evaluation_approvers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['ss_evaluation_approvers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 3,
                        'employee_id' => auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    SalesSheetEvaluation::create([
                        'ss_id' => $id, 
                        'evaluator_id' => $request['ss_evaluation_approvers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['ss_evaluation_approvers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['ss_evaluation_approvers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 3,
                        'employee_id' => auth('api')->user()->id
                    ]);
                }
            }

            DB::commit();
            return ['message' => 'Sales sheet successfully registered.'];
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
    
            $ss = SalesSheet::withTrashed()->findOrFail($id);

            if($ss->deleted_at){
                $ss->restore();
            }
            else{
                $ss->delete();
            }

            DB::commit();
            return ['message' => 'SS successfully deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function evaluateSS(Request $request)
    {
        try{
            DB::beginTransaction();

            if($request['evaluation_status'] == 1) $evaluation_status = 2;
            else $evaluation_status = 1;

            if($request['evaluation_type'] == 2){
                if($request['evaluation_status'] == 3) $evaluation_status = 4;
                else $evaluation_status = 3;
            }
    
            $cs_evaluation = SalesSheetEvaluation::where('id', $request['id'])->update([
                'evaluation_status' => $evaluation_status,
                'evaluator_remarks' => $request['message']
            ]);

            DB::commit();
            return ['message' => 'SS evaluation successfully completed.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function rejectSSEvaluation(Request $request)
    {
        try{
            DB::beginTransaction();

            if($request['evaluation_status'] == 5 && $request['evaluation_type'] == 1) $evaluation_status = 1;
            else $evaluation_status = 5;

            if($request['evaluation_type'] == 2){
                if($request['evaluation_status'] == 5 && $request['evaluation_type'] == 3) $evaluation_status = 3;
                else $evaluation_status = 3;
            }
    
            $cs_evaluation = SalesSheetEvaluation::where('id', $request['id'])->update([
                'evaluation_status' => $evaluation_status,
                'evaluator_remarks' => $request['message']
            ]);

            DB::commit();
            return ['message' => 'SS evaluation successfully completed.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
