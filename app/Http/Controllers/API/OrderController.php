<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use \stdClass;
use App\TypeCode;
use App\MaterialClassifications;
use App\Orders;
use App\OrderMaterialsPrimary;
use App\OrderMaterialsSecondary;
use App\OrderMaterialsSubcon;
use App\Materials;
use App\SCMaterials;
use App\Process;
use App\OrderProcess;
use App\OrderProcessSubcon;
use App\OrderPrints;

class OrderController extends Controller
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
            $sortProp = 'order_item_description';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return Orders::onlyTrashed()->
                where(function($query) use ($request){
                    $query->where('order_item_code','like','%'.$request['search'].'%')
                        ->orWhere('order_item_description','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Orders::where(function($query) use ($request){
                    $query->where('order_item_code','like','%'.$request['search'].'%')
                        ->orWhere('order_item_description','like','%'.$request['search'].'%');
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

            $type_code = TypeCode::where('id', $request['order']['order_item_type_code'])->first()->type_code;

            $series = '';
            $series_number = 1;
            if(Orders::first()) $series_number =  (int)Orders::select(DB::raw('SUBSTR(`order_item_code`, 7, 12) as order_item_code_substr'))->orderBy('order_item_code_substr', 'desc')->first()->order_item_code_substr + 1;
            $series_length = 7;
            $series = substr('0000000'.$series_number, -$series_length);

            $order_category = $request['order']['order_category'];
            $category = 1;
            if($order_category < 12) $category = 2;

            $order_item_code = 'AD'.$category.'-'.$type_code.''.$series;

            $order_id = Orders::insertGetId([
                'order_item_code' => $order_item_code,
                'order_item_description' => $request['order']['order_item_description'],
                'order_part_number' => $request['order']['order_part_number'],
                'order_category' => $order_category,
                'order_currency' => $request['order']['order_currency'],
                'order_item_type_code' => $request['order']['order_item_type_code'],
                'order_item_classification' => $request['order']['order_item_classification'],
                'order_length' => $request['order']['order_length'],
                'order_length_unit' => $request['order']['order_length_unit'],
                'order_width' => $request['order']['order_width'],
                'order_width_unit' => $request['order']['order_width_unit'],
                'order_gaplr' => $request['order']['order_gaplr'],
                'order_gaplr_unit' => $request['order']['order_gaplr_unit'],
                'order_gaptb' => $request['order']['order_gaptb'],
                'order_gaptb_unit' => $request['order']['order_gaptb_unit'],
                'order_remarks' => $request['order']['order_remarks'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            for($i = 0; $i < count($request['primary_materials']); $i++){
                OrderMaterialsPrimary::create([
                    'order_id' => $order_id, 
                    'material_id' => $request['primary_materials'][$i]['material_id'],
                    'order_material_length' => $request['primary_materials'][$i]['order_material_length'],
                    'order_material_length_unit' => $request['primary_materials'][$i]['order_material_length_unit'],
                    'order_material_width' => $request['primary_materials'][$i]['order_material_width'],
                    'order_material_width_unit' => $request['primary_materials'][$i]['order_material_width_unit'],
                    'order_material_margintb' => $request['primary_materials'][$i]['order_material_margintb'],
                    'order_material_margintb_unit' => $request['primary_materials'][$i]['order_material_margintb_unit'],
                    'order_material_marginlr' => $request['primary_materials'][$i]['order_material_marginlr'],
                    'order_material_marginlr_unit' => $request['primary_materials'][$i]['order_material_marginlr_unit'],
                    'order_pages' => $request['primary_materials'][$i]['order_pages'],
                    'order_rolls' => $request['primary_materials'][$i]['order_rolls'],
                    'order_material_cut' => $request['primary_materials'][$i]['order_material_cut'],
                    'order_material_slit' => $request['primary_materials'][$i]['order_material_slit'],
                    'order_material_cylinder' => $request['primary_materials'][$i]['order_material_cylinder'],
                    'order_material_layout_overlap' => $request['primary_materials'][$i]['order_material_layout_overlap'],
                    'order_material_layout_overlap_unit' => $request['primary_materials'][$i]['order_material_layout_overlap_unit'],
                    'order_material_outs' => $request['primary_materials'][$i]['order_material_outs'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            for($i = 0; $i < count($request['secondary_materials']); $i++){
                OrderMaterialsSecondary::create([
                    'order_id' => $order_id, 
                    'material_id' => $request['secondary_materials'][$i]['material_id'],
                    'order_material_length' => $request['secondary_materials'][$i]['order_material_length'],
                    'order_material_length_unit' => $request['secondary_materials'][$i]['order_material_length_unit'],
                    'order_material_width' => $request['secondary_materials'][$i]['order_material_width'],
                    'order_material_width_unit' => $request['secondary_materials'][$i]['order_material_width_unit'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            OrderMaterialsSubcon::create([
                'order_id' => $order_id, 
                'material_id' => $request['subcontractor_material']['material_id'],
                'order_material_length' => $request['subcontractor_material']['order_material_length'],
                'order_material_length_unit' => $request['subcontractor_material']['order_material_length_unit'],
                'order_material_width' => $request['subcontractor_material']['order_material_width'],
                'order_material_width_unit' => $request['subcontractor_material']['order_material_width_unit'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            for($i = 0; $i < count($request['prints']); $i++){
                OrderPrints::create([
                    'order_id' => $order_id, 
                    'material_id' => $request['prints'][$i]['material_id'], 
                    'order_print_item' => $request['prints'][$i]['order_print_item'],
                    'order_print_machine_id' => $request['prints'][$i]['order_print_machine_id'],
                    'order_print_front' => $request['prints'][$i]['order_print_front'],
                    'order_print_front_regular' => $request['prints'][$i]['order_print_front_regular'],
                    'order_print_front_special' => $request['prints'][$i]['order_print_front_special'],
                    'order_print_back' => $request['prints'][$i]['order_print_back'],
                    'order_print_back_regular' => $request['prints'][$i]['order_print_back_regular'],
                    'order_print_back_special' => $request['prints'][$i]['order_print_back_special'],
                    'order_print_regular' => $request['prints'][$i]['order_print_regular'],
                    'order_print_special' => $request['prints'][$i]['order_print_special'],
                    'order_print_cutsize' => $request['prints'][$i]['order_print_cutsize'],
                    'order_print_outs' => $request['prints'][$i]['order_print_outs'],
                    'order_print_pages' => $request['prints'][$i]['order_print_pages'],
                    'order_print_wit' => $request['prints'][$i]['order_print_wit'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            for($i = 0; $i < count($request['process_list']); $i++){
                OrderProcess::create([
                    'order_id'              	=> $order_id,
                    'order_process_description' => $request['process_list'][$i]['order_process_description'],
                    'order_process_id'	        => $request['process_list'][$i]['process_id'],
                    'order_process_cuts'	    => $request['process_list'][$i]['process_cuts'],
                    'order_process_folds'	    => $request['process_list'][$i]['process_folds'],
                    'order_process_outs'	    => $request['process_list'][$i]['process_outs'],
                    'order_process_paper_size'	=> $request['process_list'][$i]['process_size'],
                    'order_process_signature'	=> $request['process_list'][$i]['process_sig'],
                    'order_process_gsm'	        => $request['process_list'][$i]['process_gsm'],
                    'employee_id'	            => auth('api')->user()->id,
                    'created_at'	            => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                ]);
            }

            for($i = 0; $i < count($request['process_subcon']); $i++){
                OrderProcessSubcon::create([
                    'order_id'              	        => $order_id,
                    'order_process_subcon'	            => $request['process_subcon'][$i]['process_name'],
                    'order_process_subcon_description'  => $request['process_subcon'][$i]['description'],
                    'employee_id'	                    => auth('api')->user()->id,
                    'created_at'	                    => Carbon::now(),
                    'updated_at'                        => Carbon::now(),
                ]);
            }

            DB::commit();
            return ['message' => 'Order successfully registered.'];
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
        $order = new stdClass();
        $order->order = Orders::where('id', $id)->first();

        $order->primary_materials = OrderMaterialsPrimary::select('order_materials_primary.*', 'materials.material_description as material_name')
            ->where('order_materials_primary.order_id', $id)
            ->leftJoin('materials', 'materials.id', 'order_materials_primary.material_id')
            ->get();

        $order->secondary_materials = OrderMaterialsSecondary::select('order_materials_secondary.*', 'materials.material_description as material_name')
            ->where('order_materials_secondary.order_id', $id)
            ->leftJoin('materials', 'materials.id', 'order_materials_secondary.material_id')
            ->get();

        $order->subcontractor_material = OrderMaterialsSubcon::select('order_materials_subcon.*', 'sc_materials.sc_material_name as material_name')
            ->where('order_materials_subcon.order_id', $id)
            ->leftJoin('sc_materials', 'sc_materials.id', 'order_materials_subcon.material_id')
            ->first();

        $order->prints = OrderPrints::select('order_prints.*', 'materials.material_description as material_name')
            ->where('order_prints.order_id', $id)
            ->leftJoin('materials', 'materials.id', 'order_prints.material_id')
            ->get();

        $order->process_list = OrderProcess::select('order_processes.*', 'process.name as process_name')
            ->where('order_processes.order_id', $id)
            ->leftJoin('process', 'process.id', 'order_processes.order_process_id')
            ->get();

        $order->process_subcon = OrderProcessSubcon::where('order_id', $id)->get();

        return response()->json($order);
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

            Orders::where('id', $id)->update([
                'order_item_description' => $request['order']['order_item_description'],
                'order_part_number' => $request['order']['order_part_number'],
                'order_category' => $request['order']['order_category'],
                'order_currency' => $request['order']['order_currency'],
                'order_item_type_code' => $request['order']['order_item_type_code'],
                'order_item_classification' => $request['order']['order_item_classification'],
                'order_length' => $request['order']['order_length'],
                'order_length_unit' => $request['order']['order_length_unit'],
                'order_width' => $request['order']['order_width'],
                'order_width_unit' => $request['order']['order_width_unit'],
                'order_gaplr' => $request['order']['order_gaplr'],
                'order_gaplr_unit' => $request['order']['order_gaplr_unit'],
                'order_gaptb' => $request['order']['order_gaptb'],
                'order_gaptb_unit' => $request['order']['order_gaptb_unit'],
                'order_remarks' => $request['order']['order_remarks'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            $primary_materials_id = OrderMaterialsPrimary::where('order_id', $id)->pluck('id')->toArray();
            
            $primary_materials = $request['primary_materials'];
            $updated_primary_materials_id = [];
            foreach($primary_materials as $primary_material){
                array_push($updated_primary_materials_id, $primary_material['id']);
            }

            $deleted_ids = array_diff($primary_materials_id, $updated_primary_materials_id);
            OrderMaterialsPrimary::whereIn('id', $deleted_ids)->where('order_id', $id)->delete();

            for($i = 0; $i < count($request['primary_materials']); $i++){
                $order_material_primary = OrderMaterialsPrimary::where('id', $request['primary_materials'][$i]['id'])->where('order_id', $id)->first();
                if($order_material_primary){
                    OrderMaterialsPrimary::where('id', $request['primary_materials'][$i]['id'])
                        ->update([
                            'order_id' => $id, 
                            'material_id' => $request['primary_materials'][$i]['material_id'],
                            'order_material_length' => $request['primary_materials'][$i]['order_material_length'],
                            'order_material_length_unit' => $request['primary_materials'][$i]['order_material_length_unit'],
                            'order_material_width' => $request['primary_materials'][$i]['order_material_width'],
                            'order_material_width_unit' => $request['primary_materials'][$i]['order_material_width_unit'],
                            'order_material_margintb' => $request['primary_materials'][$i]['order_material_margintb'],
                            'order_material_margintb_unit' => $request['primary_materials'][$i]['order_material_margintb_unit'],
                            'order_material_marginlr' => $request['primary_materials'][$i]['order_material_marginlr'],
                            'order_material_marginlr_unit' => $request['primary_materials'][$i]['order_material_marginlr_unit'],
                            'order_pages' => $request['primary_materials'][$i]['order_pages'],
                            'order_rolls' => $request['primary_materials'][$i]['order_rolls'],
                            'order_material_cut' => $request['primary_materials'][$i]['order_material_cut'],
                            'order_material_slit' => $request['primary_materials'][$i]['order_material_slit'],
                            'order_material_cylinder' => $request['primary_materials'][$i]['order_material_cylinder'],
                            'order_material_layout_overlap' => $request['primary_materials'][$i]['order_material_layout_overlap'],
                            'order_material_layout_overlap_unit' => $request['primary_materials'][$i]['order_material_layout_overlap_unit'],
                            'order_material_outs' => $request['primary_materials'][$i]['order_material_outs'],
                            'employee_id' => auth('api')->user()->id,
                            'updated_at' => Carbon::now(),
                        ]);
                }
                else{
                    OrderMaterialsPrimary::create([
                        'order_id' => $id,
                        'material_id' => $request['primary_materials'][$i]['material_id'],
                        'order_material_length' => $request['primary_materials'][$i]['order_material_length'],
                        'order_material_length_unit' => $request['primary_materials'][$i]['order_material_length_unit'],
                        'order_material_width' => $request['primary_materials'][$i]['order_material_width'],
                        'order_material_width_unit' => $request['primary_materials'][$i]['order_material_width_unit'],
                        'order_material_margintb' => $request['primary_materials'][$i]['order_material_margintb'],
                        'order_material_margintb_unit' => $request['primary_materials'][$i]['order_material_margintb_unit'],
                        'order_material_marginlr' => $request['primary_materials'][$i]['order_material_marginlr'],
                        'order_material_marginlr_unit' => $request['primary_materials'][$i]['order_material_marginlr_unit'],
                        'order_pages' => $request['primary_materials'][$i]['order_pages'],
                        'order_rolls' => $request['primary_materials'][$i]['order_rolls'],
                        'order_material_cut' => $request['primary_materials'][$i]['order_material_cut'],
                        'order_material_slit' => $request['primary_materials'][$i]['order_material_slit'],
                        'order_material_cylinder' => $request['primary_materials'][$i]['order_material_cylinder'],
                        'order_material_layout_overlap' => $request['primary_materials'][$i]['order_material_layout_overlap'],
                        'order_material_layout_overlap_unit' => $request['primary_materials'][$i]['order_material_layout_overlap_unit'],
                        'order_material_outs' => $request['primary_materials'][$i]['order_material_outs'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            $secondary_materials_id = OrderMaterialsSecondary::where('order_id', $id)->pluck('id')->toArray();
            
            $secondary_materials = $request['secondary_materials'];
            $updated_secondary_materials_id = [];
            foreach($secondary_materials as $secondary_material){
                array_push($updated_secondary_materials_id, $secondary_material['id']);
            }

            $deleted_ids = array_diff($secondary_materials_id, $updated_secondary_materials_id);
            OrderMaterialsSecondary::whereIn('id', $deleted_ids)->where('order_id', $id)->delete();

            for($i = 0; $i < count($request['secondary_materials']); $i++){
                $order_material_secondary = OrderMaterialsSecondary::where('id', $request['secondary_materials'][$i]['id'])->where('order_id', $id)->first();
                if($order_material_secondary){
                    OrderMaterialsSecondary::where('id', $request['secondary_materials'][$i]['id'])->update([
                        'order_id' => $id, 
                        'material_id' => $request['secondary_materials'][$i]['material_id'],
                        'order_material_length' => $request['secondary_materials'][$i]['order_material_length'],
                        'order_material_length_unit' => $request['secondary_materials'][$i]['order_material_length_unit'],
                        'order_material_width' => $request['secondary_materials'][$i]['order_material_width'],
                        'order_material_width_unit' => $request['secondary_materials'][$i]['order_material_width_unit'],
                        'employee_id' => auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    OrderMaterialsSecondary::create([
                        'order_id' => $id,
                        'material_id' => $request['secondary_materials'][$i]['material_id'],
                        'order_material_length' => $request['secondary_materials'][$i]['order_material_length'],
                        'order_material_length_unit' => $request['secondary_materials'][$i]['order_material_length_unit'],
                        'order_material_width' => $request['secondary_materials'][$i]['order_material_width'],
                        'order_material_width_unit' => $request['secondary_materials'][$i]['order_material_width_unit'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            OrderMaterialsSubcon::where('id', $request['subcontractor_material']['id'])->update([
                'order_id' => $id, 
                'material_id' => $request['subcontractor_material']['material_id'],
                'order_material_length' => $request['subcontractor_material']['order_material_length'],
                'order_material_length_unit' => $request['subcontractor_material']['order_material_length_unit'],
                'order_material_width' => $request['subcontractor_material']['order_material_width'],
                'order_material_width_unit' => $request['subcontractor_material']['order_material_width_unit'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            $prints_id = OrderPrints::where('order_id', $id)->pluck('id')->toArray();
            
            $prints = $request['prints'];
            $updated_prints_id = [];
            foreach($prints as $print){
                array_push($updated_prints_id, $print['id']);
            }

            $deleted_ids = array_diff($prints_id, $updated_prints_id);
            OrderPrints::whereIn('id', $deleted_ids)->where('order_id', $id)->delete();

            for($i = 0; $i < count($request['prints']); $i++){
                $order_material_print = OrderPrints::where('id', $request['prints'][$i]['id'])->where('order_id', $id)->first();
                if($order_material_print){
                    OrderPrints::where('id', $request['prints'][$i]['id'])->update([
                        'order_id' => $id, 
                        'material_id' => $request['prints'][$i]['material_id'], 
                        'order_print_item' => $request['prints'][$i]['order_print_item'],
                        'order_print_machine_id' => $request['prints'][$i]['order_print_machine_id'],
                        'order_print_front' => $request['prints'][$i]['order_print_front'],
                        'order_print_front_regular' => $request['prints'][$i]['order_print_front_regular'],
                        'order_print_front_special' => $request['prints'][$i]['order_print_front_special'],
                        'order_print_back' => $request['prints'][$i]['order_print_back'],
                        'order_print_back_regular' => $request['prints'][$i]['order_print_back_regular'],
                        'order_print_back_special' => $request['prints'][$i]['order_print_back_special'],
                        'order_print_regular' => $request['prints'][$i]['order_print_regular'],
                        'order_print_special' => $request['prints'][$i]['order_print_special'],
                        'order_print_cutsize' => $request['prints'][$i]['order_print_cutsize'],
                        'order_print_outs' => $request['prints'][$i]['order_print_outs'],
                        'order_print_pages' => $request['prints'][$i]['order_print_pages'],
                        'order_print_wit' => $request['prints'][$i]['order_print_wit'],
                        'employee_id' => auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    OrderPrints::create([
                        'order_id' => $id, 
                        'material_id' => $request['prints'][$i]['material_id'], 
                        'order_print_item' => $request['prints'][$i]['order_print_item'],
                        'order_print_machine_id' => $request['prints'][$i]['order_print_machine_id'],
                        'order_print_front' => $request['prints'][$i]['order_print_front'],
                        'order_print_front_regular' => $request['prints'][$i]['order_print_front_regular'],
                        'order_print_front_special' => $request['prints'][$i]['order_print_front_special'],
                        'order_print_back' => $request['prints'][$i]['order_print_back'],
                        'order_print_back_regular' => $request['prints'][$i]['order_print_back_regular'],
                        'order_print_back_special' => $request['prints'][$i]['order_print_back_special'],
                        'order_print_regular' => $request['prints'][$i]['order_print_regular'],
                        'order_print_special' => $request['prints'][$i]['order_print_special'],
                        'order_print_cutsize' => $request['prints'][$i]['order_print_cutsize'],
                        'order_print_outs' => $request['prints'][$i]['order_print_outs'],
                        'order_print_pages' => $request['prints'][$i]['order_print_pages'],
                        'order_print_wit' => $request['prints'][$i]['order_print_wit'],
                        'employee_id' => auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }

            $processes_id = OrderProcess::where('order_id', $id)->pluck('id')->toArray();
            
            $process_lists = $request['process_list'];
            $updated_processes_id = [];
            foreach($process_lists as $process_list){
                array_push($updated_processes_id, $process_list['id']);
            }

            $deleted_ids = array_diff($processes_id, $updated_processes_id);
            OrderProcess::whereIn('id', $deleted_ids)->where('order_id', $id)->delete();

            for($i = 0; $i < count($request['process_list']); $i++){
                $order_process = OrderProcess::where('id', $request['process_list'][$i]['id'])->where('order_id', $id)->first();
                if($order_process){
                    OrderProcess::where('id', $request['process_list'][$i]['id'])
                    ->update([
                        'order_id'              	=> $id,
                        'order_process_description' => $request['process_list'][$i]['order_process_description'],
                        'order_process_id'	        => $request['process_list'][$i]['order_process_id'],
                        'order_process_cuts'	    => $request['process_list'][$i]['order_process_cuts'],
                        'order_process_folds'	    => $request['process_list'][$i]['order_process_folds'],
                        'order_process_outs'	    => $request['process_list'][$i]['order_process_outs'],
                        'order_process_paper_size'	=> $request['process_list'][$i]['order_process_paper_size'],
                        'order_process_signature'	=> $request['process_list'][$i]['order_process_signature'],
                        'order_process_gsm'	        => $request['process_list'][$i]['order_process_gsm'],
                        'employee_id'	            => auth('api')->user()->id,
                        'updated_at'                => Carbon::now(),
                    ]);
                }
                else{
                    OrderProcess::create([
                        'order_id'              	=> $id,
                        'order_process_description' => $request['process_list'][$i]['order_process_description'],
                        'order_process_id'	        => $request['process_list'][$i]['order_process_id'],
                        'order_process_cuts'	    => $request['process_list'][$i]['order_process_cuts'],
                        'order_process_folds'	    => $request['process_list'][$i]['order_process_folds'],
                        'order_process_outs'	    => $request['process_list'][$i]['order_process_outs'],
                        'order_process_paper_size'	=> $request['process_list'][$i]['order_process_paper_size'],
                        'order_process_signature'	=> $request['process_list'][$i]['order_process_signature'],
                        'order_process_gsm'	        => $request['process_list'][$i]['order_process_gsm'],
                        'employee_id'	            => auth('api')->user()->id,
                        'created_at'                => Carbon::now(),
                    ]);
                }
            }

            $sc_processes_id = OrderProcessSubcon::where('order_id', $id)->pluck('id')->toArray();
            
            $sc_process_lists = $request['process_subcon'];
            $updated_sc_processes_id = [];
            foreach($sc_process_lists as $sc_process_list){
                array_push($updated_sc_processes_id, $sc_process_list['id']);
            }

            $deleted_ids = array_diff($sc_processes_id, $updated_sc_processes_id);
            OrderProcessSubcon::whereIn('id', $deleted_ids)->where('order_id', $id)->delete();

            for($i = 0; $i < count($request['process_subcon']); $i++){
                $order_process = OrderProcessSubcon::where('id', $request['process_subcon'][$i]['id'])->where('order_id', $id)->first();
                if($order_process){
                    OrderProcessSubcon::where('id', $request['process_subcon'][$i]['id'])
                    ->update([
                        'order_id'              	        => $id,
                        'order_process_subcon'	            => $request['process_subcon'][$i]['order_process_subcon'],
                        'order_process_subcon_description'  => $request['process_subcon'][$i]['order_process_subcon_description'],
                        'employee_id'	                    => auth('api')->user()->id,
                        'updated_at'                        => Carbon::now(),
                    ]);
                }
                else{
                    OrderProcessSubcon::create([
                    'order_id'              	        => $id,
                    'order_process_subcon'	            => $request['process_subcon'][$i]['order_process_subcon'],
                    'order_process_subcon_description'  => $request['process_subcon'][$i]['order_process_subcon_description'],
                    'employee_id'	                    => auth('api')->user()->id,
                    'created_at'	                    => Carbon::now(),
                    'updated_at'                        => Carbon::now(),
                    ]);
                }
            }


            DB::commit();
            return ['message' => 'Order successfully registered.'];
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
    
            $supplier = Orders::withTrashed()->findOrFail($id);

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

    public function getOrderCategories()
    {
        return DB::table('order_categories')->get();
    }

    public function getOrderTypeCodes()
    {
        return TypeCode::where('is_fg', 1)->get();
    }

    public function getOrderClassifications(Request $request, $id)
    {
        return MaterialClassifications::where('type_code_id', $id)->get();
    }

    public function getOrderMaterials(Request $request)
    {
        return Materials::select('materials.*', DB::raw('SUBSTR(`material_code`, 1, 3) as material_code_prefix'))
            ->orderBy('materials.material_description')
            ->leftJoin('supplier_material', 'supplier_material.material_id', 'materials.id')
            ->groupBy('materials.id')
            ->where(DB::raw('SUBSTR(`material_code`, 1, 3)'), 'AD2')
            ->get();
    }

    public function getOrderSCMaterials(Request $request)
    {
        return SCMaterials::orderBy('sc_material_name')->get();
    }

    public function getProcesses(){
        return Process::get();
    }
}   
