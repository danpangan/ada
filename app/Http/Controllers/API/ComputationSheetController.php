<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use \stdClass;
use App\Orders;
use App\ComputationSheet;
use App\ComputationSheetMaterialsPrimary;
use App\ComputationSheetMaterialsPrimarySupplier;
use App\ComputationSheetMaterialsSecondary;
use App\ComputationSheetMaterialsSecondarySupplier;
use App\ComputationSheetMaterialsSubcon;
use App\ComputationSheetMaterialsSubconSupplier;
use App\ComputationSheetPrinting;
use App\ComputationSheetPrintingSupplier;
use App\ComputationSheetProcesses;
use App\ComputationSheetProcessSubcon;
use App\ComputationSheetMisc;
use App\ComputationSheetMiscOthers;
use App\ComputationSheetEvaluation;
use App\OrderMaterialsSecondary;
use App\OrderMaterialsSubcon;
use App\OrderPrints;
use App\OrderMaterialsPrimary;
use App\OrderProcess;
use App\ExchangeRate;
use App\OrderProcessSubcon;
use App\Process;

class ComputationSheetController extends Controller
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
            $sortProp = 'cs_control_number';
            $sortOrder = 'asc';
        }

        $cs_six_months_ago = ComputationSheet::where('created_at', '<', Carbon::now()->addMonths(-6))->where('cs_is_valid', 1)->pluck('id');
        ComputationSheet::whereIn('id', $cs_six_months_ago)->update(['cs_is_valid' => 0]);

        if($request['showDeleted'] == 'true'){
            return ComputationSheet::select('computation_sheet.id','computation_sheet.cs_control_number', 'computation_sheet.cs_version_number', 'computation_sheet.cs_total_amount', 'computation_sheet.cs_currency', 'orders.order_part_number', 'clients.client_short_name')
                ->onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('computation_sheet.cs_control_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
                ->leftJoin('clients', 'clients.id', 'computation_sheet.cs_client_id')
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return ComputationSheet::select('computation_sheet.id','computation_sheet.cs_control_number', 'computation_sheet.cs_version_number', 'computation_sheet.cs_total_amount', 'computation_sheet.cs_currency', 'orders.order_part_number', 'clients.client_short_name')
                ->where(function($query) use ($request){
                    $query->where('computation_sheet.cs_control_number','like','%'.$request['search'].'%');
                })
                ->leftJoin('orders', 'orders.id', 'computation_sheet.client_order_id')
                ->leftJoin('clients', 'clients.id', 'computation_sheet.cs_client_id')
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

            $date_now = Carbon::now();
            $year = $date_now->year;
            $month = $date_now->format('m');
            $day = $date_now->format('d');

            $series_key = $year.''.$month.''.$day;

            $series = '';
            $series_number = 1;
            $pre_series_number = 1;
            $check_series_key = ComputationSheet::where('cs_control_number', 'LIKE', '%'.$series_key.'%')->first();
            if($check_series_key){
                $series_number =  (int)substr(ComputationSheet::where('cs_control_number', 'LIKE', '%'.$series_key.'%')->orderBy('cs_control_number', 'desc')->first()->cs_control_number, 9, 3) + 1;
                $pre_series_number = $series_number - 1;
            
            }
            $series_length = 3;
            $series = substr('000'.$series_number, -$series_length);
            $pre_series = substr('000'.$pre_series_number, -$series_length);

            $version_key = $year.''.$month.''.$day.'-'.$pre_series;
            $version = '';
            $version_number = 1;
            if(ComputationSheet::first() && $request['is_new_version']){
                $ref_cs = ComputationSheet::where('id', $request['cs']['id'])->first();
                $cs_control_number_key = substr($ref_cs->cs_control_number, 0, 8);
                $cs_series = substr($ref_cs->cs_control_number, 9, 3);
                $version_key = $cs_control_number_key.'-'.$cs_series;
                $cs = ComputationSheet::where('cs_control_number', 'LIKE', '%'.$version_key.'%')->orderBy('cs_version_number', 'desc')->first();
                $version_number = $cs->cs_version_number + 1;

                if($cs->cs_version_number != $ref_cs->cs_version_number) return response()->json(['cs_version' => 'CS version is not allowed for update. Use the latest CS: '.$cs->cs_control_number.' for your reference.'], 401);
            }
            $version_length = 3;
            $version = substr('000'.$version_number, -$version_length);

            if(!$request['is_new_version']) $cs_control_number = $year.''.$month.''.$day.'-'.$series.'-'.$version;
            else $cs_control_number = $cs_control_number_key.'-'.$cs_series.'-'.$version;

            $cs_id = ComputationSheet::insertGetId([
                'cs_control_number' => $cs_control_number, 
                'cs_version_number' => $version_number, 
                'cs_client_id' => $request['cs']['cs_client_id'],
                'client_order_id' => $request['cs']['client_order_id'],
                'cs_qty' => $request['cs']['cs_qty'],
                'cs_qty_unit' => $request['cs']['cs_qty_unit'],
                'cs_currency' => $request['cs']['cs_currency'],
                'cs_material_subtotal' => $request['cs']['cs_material_subtotal'],
                'cs_material_subtotal_php' => $request['cs']['cs_material_subtotal_php'],
                'cs_material_loss' => $request['cs']['cs_material_loss'],
                'cs_material_loss_php' => $request['cs']['cs_material_loss_php'],
                'cs_material_total' => $request['cs']['cs_material_total'],
                'cs_material_total_php' => $request['cs']['cs_material_total_php'],
                'cs_printing_subtotal' => $request['cs']['cs_printing_subtotal'],
                'cs_printing_plate' => $request['cs']['cs_printing_plate'],
                'cs_printing_plate_amount' => $request['cs']['cs_printing_plate_amount'],
                'cs_printing_color' => $request['cs']['cs_printing_color'],
                'cs_printing_color_amount' => $request['cs']['cs_printing_color_amount'],
                'cs_printing_total' => $request['cs']['cs_printing_total'],
                'cs_printing_total_php' => $request['cs']['cs_printing_total_php'],
                'cs_process_total' => $request['cs']['cs_process_total'],
                'cs_process_total_php' => $request['cs']['cs_process_total_php'],
                'cs_miscellaneous_total' => $request['cs']['cs_miscellaneous_total'],
                'cs_miscellaneous_total_php' => $request['cs']['cs_miscellaneous_total_php'],
                'cs_total_amount' => $request['cs']['cs_total_amount'],
                'cs_total_amount_php' => $request['cs']['cs_total_amount_php'],
                'cs_markup_percentage' => $request['cs']['cs_markup_percentage'],
                'cs_markup_piece' => $request['cs']['cs_markup_piece'],
                'cs_markup_total' => $request['cs']['cs_markup_total'],
                'cs_markup_profit' => $request['cs']['cs_markup_profit'],
                'cs_status' => 1,
                'cs_is_valid' => 1,
                'cs_remarks' => $request['cs']['cs_remarks'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            for ($i = 0; $i < count($request['cs_order']['primary_materials']); $i++) { 
                $cs_materials_primary_id = ComputationSheetMaterialsPrimary::insertGetId([
                    'cs_id' => $cs_id, 
                    'cs_order_materials_primary_id' => $request['cs_order']['primary_materials'][$i]['id'],
                    'cs_material_id' => $request['cs_order']['primary_materials'][$i]['material_id'],
                    'cs_material_outs' => $request['cs_order']['primary_materials'][$i]['cs_material_outs'],
                    'cs_material_prm' => $request['cs_order']['primary_materials'][$i]['cs_material_prm'],
                    'cs_material_actual' => $request['cs_order']['primary_materials'][$i]['cs_material_actual'],
                    'cs_material_total_actual' => 1,
                    'cs_material_actual_rate' => 1,
                    'cs_total_material_quantity' => $request['cs_order']['primary_materials'][$i]['total_material_quantity'],
                    'cs_subtotal_material_amount' => $request['cs_order']['primary_materials'][$i]['total_material_amount'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                for ($j = 0; $j < count($request['cs_order']['primary_materials'][$i]['supplier_materials']); $j++) { 
                    ComputationSheetMaterialsPrimarySupplier::create([
                        'cs_id' => $cs_id, 
                        'cs_materials_primary_id' => $cs_materials_primary_id,
                        'cs_materials_primary_supplier_id' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_id'],
                        'supplier_material_quantity' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_material_quantity'],
                        'supplier_material_amount' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_material_amount'],
                        'supplier_material_total' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_material_total'],
                        'is_selected' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['is_selected'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
            }

            for ($i = 0; $i < count($request['cs_order']['secondary_materials']); $i++) { 
                $cs_materials_secondary_id = ComputationSheetMaterialsSecondary::insertGetId([
                    'cs_id' => $cs_id, 
                    'cs_order_materials_secondary_id' => $request['cs_order']['secondary_materials'][$i]['id'],
                    'cs_material_id' => $request['cs_order']['secondary_materials'][$i]['material_id'],
                    'cs_total_material_quantity' => $request['cs_order']['secondary_materials'][$i]['total_material_quantity'],
                    'cs_subtotal_material_amount' => $request['cs_order']['secondary_materials'][$i]['total_material_amount'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                for ($j = 0; $j < count($request['cs_order']['secondary_materials'][$i]['supplier_materials']); $j++) { 
                    ComputationSheetMaterialsSecondarySupplier::create([
                        'cs_id' => $cs_id, 
                        'cs_materials_secondary_id' => $cs_materials_secondary_id,
                        'cs_materials_secondary_supplier_id' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_id'],
                        'supplier_material_quantity' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_material_quantity'],
                        'supplier_material_amount' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_material_amount'],
                        'supplier_material_total' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_material_total'],
                        'is_selected' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['is_selected'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
            }

            $cs_material_subcon_id = ComputationSheetMaterialsSubcon::insertGetId([
                'cs_id' => $cs_id, 
                'cs_order_materials_subcon_id' => $request['cs_order']['subcontractor_material'][0]['id'],
                'cs_material_id' => $request['cs_order']['subcontractor_material'][0]['material_id'],
                'cs_total_material_quantity' => $request['cs_order']['subcontractor_material'][0]['total_sc_material_quantity'],
                'cs_subtotal_material_amount' => $request['cs_order']['subcontractor_material'][0]['total_sc_material_amount'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            for ($j = 0; $j < count($request['cs_order']['subcontractor_material'][0]['supplier_sc_materials']); $j++) { 
                ComputationSheetMaterialsSubconSupplier::create([
                    'cs_id' => $cs_id, 
                    'cs_material_subcon_id' => $cs_material_subcon_id,
                    'cs_material_subcon_supplier_id' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_id'],
                    'supplier_sc_material_quantity' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_sc_material_quantity'],
                    'supplier_sc_material_amount' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_sc_material_amount'],
                    'supplier_sc_material_total' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_sc_material_total'],
                    'is_selected' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['is_selected'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            for ($i = 0; $i < count($request['cs_order']['prints']); $i++) { 
                $cs_printing_id = ComputationSheetPrinting::insertGetId([
                    'cs_id' => $cs_id, 
                    'cs_order_prints_id' => $request['cs_order']['prints'][$i]['id'],
                    'cs_material_id' => $request['cs_order']['prints'][$i]['material_id'],
                    'cs_total_material_quantity' => $request['cs_order']['prints'][$i]['total_material_quantity'],
                    'cs_total_material_amount' => $request['cs_order']['prints'][$i]['total_material_amount'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                for ($j = 0; $j < count($request['cs_order']['prints'][$i]['supplier_materials']); $j++) { 
                    ComputationSheetPrintingSupplier::create([
                        'cs_id' => $cs_id, 
                        'cs_printing_id' => $cs_printing_id,
                        'cs_printing_supplier_id' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_id'],
                        'supplier_material_quantity' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_material_quantity'],
                        'supplier_material_amount' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_material_amount'],
                        'supplier_material_total' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_material_total'],
                        'is_selected' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['is_selected'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
            }

            for ($i = 0; $i < count($request['cs_order']['process_list']); $i++) { 
                ComputationSheetProcesses::create([
                    'cs_id' => $cs_id, 
                    'cs_order_processes_id' => $request['cs_order']['process_list'][$i]['id'],
                    'cs_process_id' => $request['cs_order']['process_list'][$i]['order_process_id'],
                    'cs_process_qty' => $request['cs_order']['process_list'][$i]['total_process_quantity'],
                    'cs_process_unit_amount' => $request['cs_order']['process_list'][$i]['total_process_amount'],
                    'cs_process_total_amount' => $request['cs_order']['process_list'][$i]['process_subtotal'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            for ($i = 0; $i < count($request['cs_order']['process_subcon']); $i++) {
                ComputationSheetProcessSubcon::create([
                    'cs_id' => $cs_id, 
                    'cs_order_process_subcon_id' => $request['cs_order']['process_subcon'][$i]['id'],
                    'cs_process_subcon_qty' => $request['cs_order']['process_subcon'][$i]['total_process_quantity'],
                    'cs_process_subcon_unit_amount' => $request['cs_order']['process_subcon'][$i]['total_process_amount'],
                    'cs_process_subcon_total_amount' => $request['cs_order']['process_subcon'][$i]['process_subtotal'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            $cs_misc_id = ComputationSheetMisc::insertGetId([
                'cs_id' => $cs_id, 
                'cs_is_label' => $request['cs_misc']['cs_is_label'],
                'cs_is_package' => $request['cs_misc']['cs_is_package'],
                'cs_is_transpo' => $request['cs_misc']['cs_is_transpo'],
                'cs_is_layout' => $request['cs_misc']['cs_is_layout'],
                'cs_is_film' => $request['cs_misc']['cs_is_film'],
                'cs_is_plate' => $request['cs_misc']['cs_is_plate'],
                'cs_is_diecut' => $request['cs_misc']['cs_is_diecut'],
                'cs_is_peza' => $request['cs_misc']['cs_is_peza'],
                'cs_is_package_label' => $request['cs_misc']['cs_is_package_label'],
                'cs_is_item_label' => $request['cs_misc']['cs_is_item_label'],
                'cs_is_packaging_cartoon' => $request['cs_misc']['cs_is_packaging_cartoon'],
                'cs_is_packaging_others' => $request['cs_misc']['cs_is_packaging_others'],
                'cs_packaging_cartoon_qty_per_box' => $request['cs_misc']['cs_packaging_cartoon_qty_per_box'],
                'cs_packing_others_desc' => $request['cs_misc']['cs_packing_others_desc'],
                'cs_transpo_desc' => $request['cs_misc']['cs_transpo_desc'],
                'cs_layout_desc' => $request['cs_misc']['cs_layout_desc'],
                'cs_film_desc' => $request['cs_misc']['cs_film_desc'],
                'cs_plate_desc' => $request['cs_misc']['cs_plate_desc'],
                'cs_diecut_desc' => $request['cs_misc']['cs_diecut_desc'],
                'cs_peza_desc' => $request['cs_misc']['cs_peza_desc'],
                'cs_qty_label' => $request['cs_misc']['cs_qty_label'],
                'cs_qty_package' => $request['cs_misc']['cs_qty_package'],
                'cs_qty_transpo' => $request['cs_misc']['cs_qty_transpo'],
                'cs_qty_layout' => $request['cs_misc']['cs_qty_layout'],
                'cs_qty_film' => $request['cs_misc']['cs_qty_film'],
                'cs_qty_plate' => $request['cs_misc']['cs_qty_plate'],
                'cs_qty_diecut' => $request['cs_misc']['cs_qty_diecut'],
                'cs_qty_peza' => $request['cs_misc']['cs_qty_peza'],
                'cs_unit_amt_label' => $request['cs_misc']['cs_unit_amt_label'],
                'cs_unit_amt_package' => $request['cs_misc']['cs_unit_amt_package'],
                'cs_unit_amt_transpo' => $request['cs_misc']['cs_unit_amt_transpo'],
                'cs_unit_amt_layout' => $request['cs_misc']['cs_unit_amt_layout'],
                'cs_unit_amt_film' => $request['cs_misc']['cs_unit_amt_film'],
                'cs_unit_amt_plate' => $request['cs_misc']['cs_unit_amt_plate'],
                'cs_unit_amt_diecut' => $request['cs_misc']['cs_unit_amt_diecut'],
                'cs_unit_amt_peza' => $request['cs_misc']['cs_unit_amt_peza'],
                'cs_total_amt_label' => $request['cs_misc']['cs_total_amt_label'],
                'cs_total_amt_package' => $request['cs_misc']['cs_total_amt_package'],
                'cs_total_amt_transpo' => $request['cs_misc']['cs_total_amt_transpo'],
                'cs_total_amt_layout' => $request['cs_misc']['cs_total_amt_layout'],
                'cs_total_amt_film' => $request['cs_misc']['cs_total_amt_film'],
                'cs_total_amt_plate' => $request['cs_misc']['cs_total_amt_plate'],
                'cs_total_amt_diecut' => $request['cs_misc']['cs_total_amt_diecut'],
                'cs_total_amt_peza' => $request['cs_misc']['cs_total_amt_peza'],
                'cs_miscellaneous_total' => $request['cs']['cs_miscellaneous_total'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            for($i = 0; $i < count($request['cs_misc_others']); $i++) {
                ComputationSheetMiscOthers::create([
                    'cs_id' => $cs_id, 
                    'cs_misc_id' => $cs_misc_id,
                    'other_misc_description' => $request['cs_misc_others'][$i]['other_misc_description'],
                    'other_misc_quantity' => $request['cs_misc_others'][$i]['other_misc_quantity'],
                    'other_misc_price' => $request['cs_misc_others'][$i]['other_misc_price'],
                    'other_misc_total' => $request['cs_misc_others'][$i]['other_misc_total'],
                    'employee_id' => auth('api')->user()->id
                ]);
            }

            for($i = 0; $i < count($request['cs_evaluation_checkers']); $i++) {
                ComputationSheetEvaluation::create([
                    'cs_id' => $cs_id, 
                    'evaluator_id' => $request['cs_evaluation_checkers'][$i]['evaluator_id'],
                    'evaluation_type' => 1,
                    'evaluator_remarks' => $request['cs_evaluation_checkers'][$i]['evaluator_remarks'],
                    'evaluation_status' => 1,
                    'employee_id' => auth('api')->user()->id
                ]);
            }

            for($i = 0; $i < count($request['cs_evaluation_approvers']); $i++) {
                ComputationSheetEvaluation::create([
                    'cs_id' => $cs_id, 
                    'evaluator_id' => $request['cs_evaluation_approvers'][$i]['evaluator_id'],
                    'evaluation_type' => 2,
                    'evaluator_remarks' => $request['cs_evaluation_approvers'][$i]['evaluator_remarks'],
                    'evaluation_status' => 3,
                    'employee_id' => auth('api')->user()->id
                ]);
            }

            $cs_control_number = ComputationSheet::where('id', $cs_id)->first()->cs_control_number;

            DB::commit();
            return [
                'message' => 'CS successfully registered.',
                'cs_control_number' => $cs_control_number,
            ];
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
        $cs = ComputationSheet::where('id', $id)->first();

        $order = new stdClass();
        $order->order = Orders::where('id', $cs->client_order_id)->first();

        $order->primary_materials = OrderMaterialsPrimary::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_materials_primary.*', 'materials.material_description as material_name')
            ->where('order_materials_primary.order_id', $cs->client_order_id)
            ->leftJoin('materials', 'materials.id', 'order_materials_primary.material_id')
            ->get();

        $order->secondary_materials = OrderMaterialsSecondary::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_materials_secondary.*', 'materials.material_description as material_name')
            ->where('order_materials_secondary.order_id', $cs->client_order_id)
            ->leftJoin('materials', 'materials.id', 'order_materials_secondary.material_id')
            ->get();

        $order->subcontractor_material = OrderMaterialsSubcon::with('supplier_sc_materials.supplier', 'supplier_sc_materials.tracking', 'sc_material', 'order')
            ->select('order_materials_subcon.*', 'sc_materials.sc_material_name as material_name')
            ->where('order_materials_subcon.order_id', $cs->client_order_id)
            ->leftJoin('sc_materials', 'sc_materials.id', 'order_materials_subcon.material_id')
            ->get();

        $order->prints = OrderPrints::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_prints.*', 'materials.material_description as material_name')
            ->where('order_prints.order_id', $cs->client_order_id)
            ->leftJoin('materials', 'materials.id', 'order_prints.material_id')
            ->get();

        $order->process_list = OrderProcess::select('order_processes.*', 'process.name as process_name')
            ->where('order_processes.order_id', $cs->client_order_id)
            ->leftJoin('process', 'process.id', 'order_processes.order_process_id')
            ->get();

        $order->process_subcon = OrderProcessSubcon::where('order_id', $cs->client_order_id)->get();

        foreach($order->primary_materials as $primary_material){
            $cs_materials_primary = ComputationSheetMaterialsPrimary::where('cs_id', $id)->where('cs_order_materials_primary_id', $primary_material->id)->first();

            $primary_material->cs_material_outs = $cs_materials_primary->cs_material_outs;
            $primary_material->cs_material_actual = $cs_materials_primary->cs_material_actual;
            $primary_material->cs_material_prm = $cs_materials_primary->cs_material_prm;
            $primary_material->total_material_quantity = $cs_materials_primary->cs_total_material_quantity;
            $primary_material->total_material_amount = number_format($cs_materials_primary->cs_subtotal_material_amount, 4);

            foreach($primary_material->supplier_materials as $supplier_material){
                $cs_materials_primary_supplier = ComputationSheetMaterialsPrimarySupplier::where('cs_id', $id)->where('cs_materials_primary_id', $cs_materials_primary->id)->where('cs_materials_primary_supplier_id', $supplier_material->supplier_id)->first();

                $supplier_material->supplier_material_quantity = $cs_materials_primary_supplier->supplier_material_quantity;
                $supplier_material->supplier_material_amount = number_format($cs_materials_primary_supplier->supplier_material_amount, 4);
                $supplier_material->supplier_material_total = number_format($cs_materials_primary_supplier->supplier_material_total, 4);
                $supplier_material->is_selected = boolval($cs_materials_primary_supplier->is_selected);
            }
        }

        foreach($order->secondary_materials as $secondary_material){
            $cs_materials_secondary = ComputationSheetMaterialsSecondary::where('cs_id', $id)->where('cs_order_materials_secondary_id', $secondary_material->id)->first();

            $secondary_material->total_material_quantity = $cs_materials_secondary->cs_total_material_quantity;
            $secondary_material->total_material_amount = number_format($cs_materials_secondary->cs_subtotal_material_amount, 4);

            foreach($secondary_material->supplier_materials as $supplier_material){
                $cs_materials_secondary_supplier = ComputationSheetMaterialsSecondarySupplier::where('cs_id', $id)->where('cs_materials_secondary_id', $cs_materials_secondary->id)->where('cs_materials_secondary_supplier_id', $supplier_material->supplier_id)->first();

                $supplier_material->supplier_material_quantity = $cs_materials_secondary_supplier->supplier_material_quantity;
                $supplier_material->supplier_material_amount = number_format($cs_materials_secondary_supplier->supplier_material_amount, 4);
                $supplier_material->supplier_material_total = number_format($cs_materials_secondary_supplier->supplier_material_total, 4);
                $supplier_material->is_selected = boolval($cs_materials_secondary_supplier->is_selected);
            }
        }

        $cs_material_subcon = ComputationSheetMaterialsSubcon::where('cs_id', $id)->where('cs_order_materials_subcon_id', $order->subcontractor_material[0]['id'])->first();

        $order->subcontractor_material[0]->total_sc_material_quantity = $cs_material_subcon->cs_total_material_quantity;
        $order->subcontractor_material[0]->total_sc_material_amount = number_format($cs_material_subcon->cs_subtotal_material_amount, 4);
        foreach($order->subcontractor_material[0]->supplier_sc_materials as $supplier_sc_material){
            $cs_material_subcon_supplier = ComputationSheetMaterialsSubconSupplier::where('cs_id', $id)->where('cs_material_subcon_id', $cs_material_subcon->id)->where('cs_material_subcon_supplier_id', $supplier_sc_material->supplier_id)->first();

            $supplier_sc_material->supplier_sc_material_quantity = $cs_material_subcon_supplier->supplier_sc_material_quantity;
            $supplier_sc_material->supplier_sc_material_amount = number_format($cs_material_subcon_supplier->supplier_sc_material_amount, 4);
            $supplier_sc_material->supplier_sc_material_total = number_format($cs_material_subcon_supplier->supplier_sc_material_total, 4);
            $supplier_sc_material->is_selected = boolval($cs_material_subcon_supplier->is_selected);
        }

        foreach($order->prints as $print){
            $cs_printing = ComputationSheetPrinting::where('cs_id', $id)->where('cs_order_prints_id', $print->id)->first();

            $print->total_material_quantity = $cs_printing->cs_total_material_quantity;
            $print->total_material_amount = number_format($cs_printing->cs_total_material_amount, 4);

            foreach($print->supplier_materials as $supplier_material){
                $cs_printing_supplier = ComputationSheetPrintingSupplier::where('cs_id', $id)->where('cs_printing_id', $cs_printing->id)->where('cs_printing_supplier_id', $supplier_material->supplier_id)->first();

                $supplier_material->supplier_material_quantity = $cs_printing_supplier->supplier_material_quantity;
                $supplier_material->supplier_material_amount = number_format($cs_printing_supplier->supplier_material_amount, 4);
                $supplier_material->supplier_material_total = number_format($cs_printing_supplier->supplier_material_total, 4);
                $supplier_material->is_selected = boolval($cs_printing_supplier->is_selected);
            }
        }

        foreach($order->process_list as $process_list){
            $cs_processes = ComputationSheetProcesses::where('cs_id', $id)->where('cs_order_processes_id', $process_list->id)->first();

            $process_list->total_process_quantity = $cs_processes->cs_process_qty;
            $process_list->total_process_amount = $cs_processes->cs_process_unit_amount;
            $process_list->process_subtotal = $cs_processes->cs_process_total_amount;
        }

        foreach($order->process_subcon as $process_subcon){
            $cs_process_subcon = ComputationSheetProcessSubcon::where('cs_id', $id)->where('cs_order_process_subcon_id', $process_subcon->id)->first();

            $process_subcon->total_process_quantity = $cs_process_subcon->cs_process_subcon_qty;
            $process_subcon->total_process_amount = $cs_process_subcon->cs_process_subcon_unit_amount;
            $process_subcon->process_subtotal = $cs_process_subcon->cs_process_subcon_total_amount;
        }

        $cs_misc = ComputationSheetMisc::where('cs_id', $id)->first();
        $cs_misc_others = ComputationSheetMiscOthers::where('cs_misc_id', $cs_misc->id)->get();

        $cs_evaluation_checkers = ComputationSheetEvaluation::select('computation_sheet_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'computation_sheet_evaluation.evaluator_id')
            ->where('cs_id', $id)
            ->where('evaluation_type', 1)
            ->get();
        $cs_evaluation_approvers = ComputationSheetEvaluation::select('computation_sheet_evaluation.*', 'users.id as user_id', 'users.username', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'computation_sheet_evaluation.evaluator_id')
            ->where('cs_id', $id)
            ->where('evaluation_type', 2)
            ->get();

        $cs_evaluation_remarks = ComputationSheetEvaluation::select('computation_sheet_evaluation.id', 'computation_sheet_evaluation.evaluator_id', 'computation_sheet_evaluation.evaluation_type', 'computation_sheet_evaluation.evaluator_remarks', 'computation_sheet_evaluation.updated_at', 'computation_sheet_evaluation.evaluation_status', 'users.first_name', 'users.last_name')
            ->leftJoin('users', 'users.id', 'computation_sheet_evaluation.evaluator_id')
            ->where('computation_sheet_evaluation.cs_id', $id)
            ->where('computation_sheet_evaluation.evaluator_remarks', '!=', null)
            ->orderBy('computation_sheet_evaluation.evaluation_type')
            ->get();

        $order->exchange_rates = [];
        $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
        for ($i = 1; $i <= 4; $i++) {
            $exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first();
            $order->exchange_rates[$exchange_rate_label[$i - 1]] = $exchange_rate;
        }

        $user_id = auth('api')->user()->id;

        return [
            'cs' => $cs, 
            'cs_order' => $order,
            'cs_misc' => $cs_misc,
            'cs_misc_others' => $cs_misc_others,
            'cs_evaluation_checkers' => $cs_evaluation_checkers,
            'cs_evaluation_approvers' => $cs_evaluation_approvers,
            'cs_evaluation_remarks' => $cs_evaluation_remarks,
            'exchange_rates' => $order->exchange_rates,
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

            ComputationSheet::where('id', $id)->update([
                'cs_qty' => $request['cs']['cs_qty'],
                'cs_material_subtotal' => $request['cs']['cs_material_subtotal'],
                'cs_material_loss' => $request['cs']['cs_material_loss'],
                'cs_material_total' => $request['cs']['cs_material_total'],
                'cs_printing_subtotal' => $request['cs']['cs_printing_subtotal'],
                'cs_printing_plate' => $request['cs']['cs_printing_plate'],
                'cs_printing_plate_amount' => $request['cs']['cs_printing_plate_amount'],
                'cs_printing_color' => $request['cs']['cs_printing_color'],
                'cs_printing_color_amount' => $request['cs']['cs_printing_color_amount'],
                'cs_printing_total' => $request['cs']['cs_printing_total'],
                'cs_process_total' => $request['cs']['cs_process_total'],
                'cs_miscellaneous_total' => $request['cs']['cs_miscellaneous_total'],
                'cs_total_amount' => $request['cs']['cs_total_amount'],
                'cs_markup_percentage' => $request['cs']['cs_markup_percentage'],
                'cs_markup_piece' => $request['cs']['cs_markup_piece'],
                'cs_markup_total' => $request['cs']['cs_markup_total'],
                'cs_markup_profit' => $request['cs']['cs_markup_profit'],
                'cs_status' => 1,
                'cs_is_valid' => 1,
                'cs_remarks' => $request['cs']['cs_remarks'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            for ($i = 0; $i < count($request['cs_order']['primary_materials']); $i++) { 
                $cs_materials_primary_id = ComputationSheetMaterialsPrimary::where('cs_id', $id)->where('cs_order_materials_primary_id', $request['cs_order']['primary_materials'][$i]['id'])->first()->id;
                ComputationSheetMaterialsPrimary::where('cs_id', $id)->where('cs_order_materials_primary_id', $request['cs_order']['primary_materials'][$i]['id'])->update([
                    'cs_material_outs' => $request['cs_order']['primary_materials'][$i]['cs_material_outs'],
                    'cs_material_prm' => $request['cs_order']['primary_materials'][$i]['cs_material_prm'],
                    'cs_material_actual' => $request['cs_order']['primary_materials'][$i]['cs_material_actual'],
                    'cs_material_total_actual' => 1,
                    'cs_material_actual_rate' => 1,
                    'cs_total_material_quantity' => $request['cs_order']['primary_materials'][$i]['total_material_quantity'],
                    'cs_subtotal_material_amount' => $request['cs_order']['primary_materials'][$i]['total_material_amount'],
                    'employee_id' => auth('api')->user()->id,
                    'updated_at' => Carbon::now(),
                ]);

                for ($j = 0; $j < count($request['cs_order']['primary_materials'][$i]['supplier_materials']); $j++) { 
                    ComputationSheetMaterialsPrimarySupplier::where('cs_id', $id)->where('cs_materials_primary_id', $cs_materials_primary_id)->where('cs_materials_primary_supplier_id', $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_id'])->update([
                        'supplier_material_quantity' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_material_quantity'],
                        'supplier_material_amount' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_material_amount'],
                        'supplier_material_total' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['supplier_material_total'],
                        'is_selected' => $request['cs_order']['primary_materials'][$i]['supplier_materials'][$j]['is_selected'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
            }

            for ($i = 0; $i < count($request['cs_order']['secondary_materials']); $i++) { 
                $cs_materials_secondary_id = ComputationSheetMaterialsSecondary::where('cs_id', $id)->where('cs_order_materials_secondary_id', $request['cs_order']['secondary_materials'][$i]['id'])->first()->id;
                ComputationSheetMaterialsSecondary::where('cs_id', $id)->where('cs_order_materials_secondary_id', $request['cs_order']['secondary_materials'][$i]['id'])->update([
                    'cs_total_material_quantity' => $request['cs_order']['secondary_materials'][$i]['total_material_quantity'],
                    'cs_subtotal_material_amount' => $request['cs_order']['secondary_materials'][$i]['total_material_amount'],
                    'employee_id' => auth('api')->user()->id,
                    'updated_at' => Carbon::now(),
                ]);

                for ($j = 0; $j < count($request['cs_order']['secondary_materials'][$i]['supplier_materials']); $j++) { 
                    ComputationSheetMaterialsSecondarySupplier::where('cs_id', $id)->where('cs_materials_secondary_id', $cs_materials_secondary_id)->where('cs_materials_secondary_supplier_id', $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_id'])->update([
                        'supplier_material_quantity' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_material_quantity'],
                        'supplier_material_amount' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_material_amount'],
                        'supplier_material_total' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['supplier_material_total'],
                        'is_selected' => $request['cs_order']['secondary_materials'][$i]['supplier_materials'][$j]['is_selected'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
            }

            $cs_material_subcon_id = ComputationSheetMaterialsSubcon::where('cs_id', $id)->where('cs_order_materials_subcon_id', $request['cs_order']['subcontractor_material'][0]['id'])->first()->id;
            ComputationSheetMaterialsSubcon::where('cs_id', $id)->where('cs_order_materials_subcon_id', $request['cs_order']['subcontractor_material'][0]['id'])->update([
                'cs_total_material_quantity' => $request['cs_order']['subcontractor_material'][0]['total_sc_material_quantity'],
                'cs_subtotal_material_amount' => $request['cs_order']['subcontractor_material'][0]['total_sc_material_amount'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            for ($j = 0; $j < count($request['cs_order']['subcontractor_material'][0]['supplier_sc_materials']); $j++) { 
                ComputationSheetMaterialsSubconSupplier::where('cs_id', $id)->where('cs_material_subcon_id', $cs_material_subcon_id)->where('cs_material_subcon_supplier_id', $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_id'])->update([
                    'supplier_sc_material_quantity' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_sc_material_quantity'],
                    'supplier_sc_material_amount' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_sc_material_amount'],
                    'supplier_sc_material_total' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['supplier_sc_material_total'],
                    'is_selected' => $request['cs_order']['subcontractor_material'][0]['supplier_sc_materials'][$j]['is_selected'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            for ($i = 0; $i < count($request['cs_order']['prints']); $i++) { 
                $cs_prints_id = ComputationSheetPrinting::where('cs_id', $id)->where('cs_order_prints_id', $request['cs_order']['prints'][$i]['id'])->first()->id;
                ComputationSheetPrinting::where('cs_id', $id)->where('cs_order_prints_id', $request['cs_order']['prints'][$i]['id'])->update([
                    'cs_total_material_quantity' => $request['cs_order']['prints'][$i]['total_material_quantity'],
                    'cs_total_material_amount' => $request['cs_order']['prints'][$i]['total_material_amount'],
                    'employee_id' => auth('api')->user()->id,
                    'updated_at' => Carbon::now(),
                ]);

                for ($j = 0; $j < count($request['cs_order']['prints'][$i]['supplier_materials']); $j++) { 
                    ComputationSheetPrintingSupplier::where('cs_id', $id)->where('cs_printing_id', $cs_prints_id)->where('cs_printing_supplier_id', $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_id'])->update([
                        'supplier_material_quantity' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_material_quantity'],
                        'supplier_material_amount' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_material_amount'],
                        'supplier_material_total' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['supplier_material_total'],
                        'is_selected' => $request['cs_order']['prints'][$i]['supplier_materials'][$j]['is_selected'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
            }
            
            for ($i = 0; $i < count($request['cs_order']['process_list']); $i++) { 
                $cs_processes = ComputationSheetProcesses::where('cs_id', $id)->where('cs_order_processes_id', $request['cs_order']['process_list'][$i]['id'])->where('cs_process_id', $request['cs_order']['process_list'][$i]['order_process_id'])->update([
                    'cs_process_qty' => $request['cs_order']['process_list'][$i]['total_process_quantity'],
                    'cs_process_unit_amount' => $request['cs_order']['process_list'][$i]['total_process_amount'],
                    'cs_process_total_amount' => $request['cs_order']['process_list'][$i]['process_subtotal'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }
    
            for ($i = 0; $i < count($request['cs_order']['process_subcon']); $i++) {
                $cs_process_subcon = ComputationSheetProcessSubcon::where('cs_id', $id)->where('cs_order_process_subcon_id', $request['cs_order']['process_subcon'][$i]['id'])->update([
                    'cs_process_subcon_qty' => $request['cs_order']['process_subcon'][$i]['total_process_quantity'],
                    'cs_process_subcon_unit_amount' => $request['cs_order']['process_subcon'][$i]['total_process_amount'],
                    'cs_process_subcon_total_amount' => $request['cs_order']['process_subcon'][$i]['process_subtotal'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            ComputationSheetMisc::where('id', $request['cs_misc']['id'])->update([
                'cs_is_label' => $request['cs_misc']['cs_is_label'],
                'cs_is_package' => $request['cs_misc']['cs_is_package'],
                'cs_is_transpo' => $request['cs_misc']['cs_is_transpo'],
                'cs_is_layout' => $request['cs_misc']['cs_is_layout'],
                'cs_is_film' => $request['cs_misc']['cs_is_film'],
                'cs_is_plate' => $request['cs_misc']['cs_is_plate'],
                'cs_is_diecut' => $request['cs_misc']['cs_is_diecut'],
                'cs_is_peza' => $request['cs_misc']['cs_is_peza'],
                'cs_is_package_label' => $request['cs_misc']['cs_is_package_label'],
                'cs_is_item_label' => $request['cs_misc']['cs_is_item_label'],
                'cs_is_packaging_cartoon' => $request['cs_misc']['cs_is_packaging_cartoon'],
                'cs_is_packaging_others' => $request['cs_misc']['cs_is_packaging_others'],
                'cs_packaging_cartoon_qty_per_box' => $request['cs_misc']['cs_packaging_cartoon_qty_per_box'],
                'cs_packing_others_desc' => $request['cs_misc']['cs_packing_others_desc'],
                'cs_transpo_desc' => $request['cs_misc']['cs_transpo_desc'],
                'cs_layout_desc' => $request['cs_misc']['cs_layout_desc'],
                'cs_film_desc' => $request['cs_misc']['cs_film_desc'],
                'cs_plate_desc' => $request['cs_misc']['cs_plate_desc'],
                'cs_diecut_desc' => $request['cs_misc']['cs_diecut_desc'],
                'cs_peza_desc' => $request['cs_misc']['cs_peza_desc'],
                'cs_qty_label' => $request['cs_misc']['cs_qty_label'],
                'cs_qty_package' => $request['cs_misc']['cs_qty_package'],
                'cs_qty_transpo' => $request['cs_misc']['cs_qty_transpo'],
                'cs_qty_layout' => $request['cs_misc']['cs_qty_layout'],
                'cs_qty_film' => $request['cs_misc']['cs_qty_film'],
                'cs_qty_plate' => $request['cs_misc']['cs_qty_plate'],
                'cs_qty_diecut' => $request['cs_misc']['cs_qty_diecut'],
                'cs_qty_peza' => $request['cs_misc']['cs_qty_peza'],
                'cs_unit_amt_label' => $request['cs_misc']['cs_unit_amt_label'],
                'cs_unit_amt_package' => $request['cs_misc']['cs_unit_amt_package'],
                'cs_unit_amt_transpo' => $request['cs_misc']['cs_unit_amt_transpo'],
                'cs_unit_amt_layout' => $request['cs_misc']['cs_unit_amt_layout'],
                'cs_unit_amt_film' => $request['cs_misc']['cs_unit_amt_film'],
                'cs_unit_amt_plate' => $request['cs_misc']['cs_unit_amt_plate'],
                'cs_unit_amt_diecut' => $request['cs_misc']['cs_unit_amt_diecut'],
                'cs_unit_amt_peza' => $request['cs_misc']['cs_unit_amt_peza'],
                'cs_total_amt_label' => $request['cs_misc']['cs_total_amt_label'],
                'cs_total_amt_package' => $request['cs_misc']['cs_total_amt_package'],
                'cs_total_amt_transpo' => $request['cs_misc']['cs_total_amt_transpo'],
                'cs_total_amt_layout' => $request['cs_misc']['cs_total_amt_layout'],
                'cs_total_amt_film' => $request['cs_misc']['cs_total_amt_film'],
                'cs_total_amt_plate' => $request['cs_misc']['cs_total_amt_plate'],
                'cs_total_amt_diecut' => $request['cs_misc']['cs_total_amt_diecut'],
                'cs_total_amt_peza' => $request['cs_misc']['cs_total_amt_peza'],
                'cs_miscellaneous_total' => $request['cs']['cs_miscellaneous_total'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            $cs_misc_others_id = ComputationSheetMiscOthers::where('cs_id', $id)->pluck('id')->toArray();

            $cs_misc_others = $request['cs_misc_others'];
            $updated_cs_misc_others_id = [];
            foreach($cs_misc_others as $cs_misc_other){
                array_push($updated_cs_misc_others_id, $cs_misc_other['id']);
            }

            $deleted_ids = array_diff($cs_misc_others_id, $updated_cs_misc_others_id);
            ComputationSheetMiscOthers::whereIn('id', $deleted_ids)->where('cs_id', $id)->delete();

            for($i = 0; $i < count($request['cs_misc_others']); $i++) {
                $misc_other = ComputationSheetMiscOthers::where('id', $request['cs_misc_others'][$i]['id'])->where('cs_id', $id)->first();

                if($misc_other){
                    ComputationSheetMiscOthers::where('id', $request['cs_misc_others'][$i]['id'])->where('cs_id', $id)->update([
                        'other_misc_description' => $request['cs_misc_others'][$i]['other_misc_description'],
                        'other_misc_quantity' => $request['cs_misc_others'][$i]['other_misc_quantity'],
                        'other_misc_price' => $request['cs_misc_others'][$i]['other_misc_price'],
                        'other_misc_total' => $request['cs_misc_others'][$i]['other_misc_total'],
                        'employee_id' => auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    ComputationSheetMiscOthers::create([
                        'cs_id' => $id, 
                        'cs_misc_id' => $request['cs_misc']['id'],
                        'other_misc_description' => $request['cs_misc_others'][$i]['other_misc_description'],
                        'other_misc_quantity' => $request['cs_misc_others'][$i]['other_misc_quantity'],
                        'other_misc_price' => $request['cs_misc_others'][$i]['other_misc_price'],
                        'other_misc_total' => $request['cs_misc_others'][$i]['other_misc_total'],
                        'employee_id' => auth('api')->user()->id
                    ]);
                }
            }

            $cs_checkers_id = ComputationSheetEvaluation::where('cs_id', $id)
                ->where('evaluation_type', 1)
                ->pluck('id')
                ->toArray();

            $cs_evaluation_checkers = $request['cs_evaluation_checkers'];
            $updated_cs_checkers_id = [];
            foreach($cs_evaluation_checkers as $cs_evaluation_checker){
                array_push($updated_cs_checkers_id, $cs_evaluation_checker['id']);
            }

            $deleted_ids = array_diff($cs_checkers_id, $updated_cs_checkers_id);
            ComputationSheetEvaluation::whereIn('id', $deleted_ids)
                ->where('cs_id', $id)
                ->where('evaluation_type', 1)
                ->delete();

            for($i = 0; $i < count($request['cs_evaluation_checkers']); $i++) {
                $checker = ComputationSheetEvaluation::where('id', $request['cs_evaluation_checkers'][$i]['id'])
                    ->where('cs_id', $id)
                    ->where('evaluation_type', 1)
                    ->first();

                if($checker){
                    ComputationSheetEvaluation::where('id', $request['cs_evaluation_checkers'][$i]['id'])->update([
                        'evaluator_id' => $request['cs_evaluation_checkers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['cs_evaluation_checkers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['cs_evaluation_checkers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 1,
                        'employee_id' => auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    ComputationSheetEvaluation::create([
                        'cs_id' => $id, 
                        'evaluator_id' => $request['cs_evaluation_checkers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['cs_evaluation_checkers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['cs_evaluation_checkers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 1,
                        'employee_id' => auth('api')->user()->id
                    ]);
                }
            }

            $cs_approvers_id = ComputationSheetEvaluation::where('cs_id', $id)
                ->where('evaluation_type', 2)
                ->pluck('id')
                ->toArray();

            $cs_evaluation_approvers = $request['cs_evaluation_approvers'];
            $updated_cs_approvers_id = [];
            foreach($cs_evaluation_approvers as $cs_evaluation_approver){
                array_push($updated_cs_approvers_id, $cs_evaluation_approver['id']);
            }

            $deleted_ids = array_diff($cs_approvers_id, $updated_cs_approvers_id);
            ComputationSheetEvaluation::whereIn('id', $deleted_ids)
                ->where('cs_id', $id)
                ->where('evaluation_type', 2)
                ->delete();

            for($i = 0; $i < count($request['cs_evaluation_approvers']); $i++) {
                $approver = ComputationSheetEvaluation::where('id', $request['cs_evaluation_approvers'][$i]['id'])
                    ->where('cs_id', $id)
                    ->where('evaluation_type', 2)
                    ->first();

                if($approver){
                    ComputationSheetEvaluation::where('id', $request['cs_evaluation_approvers'][$i]['id'])->update([
                        'evaluator_id' => $request['cs_evaluation_approvers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['cs_evaluation_approvers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['cs_evaluation_approvers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 3,
                        'employee_id' => auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    ComputationSheetEvaluation::create([
                        'cs_id' => $id, 
                        'evaluator_id' => $request['cs_evaluation_approvers'][$i]['evaluator_id'],
                        'evaluation_type' => $request['cs_evaluation_approvers'][$i]['evaluation_type'],
                        'evaluator_remarks' => $request['cs_evaluation_approvers'][$i]['evaluator_remarks'],
                        'evaluation_status' => 3,
                        'employee_id' => auth('api')->user()->id
                    ]);
                }
            }

            DB::commit();
            return ['message' => 'CS successfully updated.'];
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
    
            $cs = ComputationSheet::withTrashed()->findOrFail($id);

            $cs_control_number_key = substr($cs->cs_control_number, 0, 8);
            $cs_series = substr($cs->cs_control_number, 9, 3);
            $version_key = $cs_control_number_key.'-'.$cs_series;
            $cs_search = ComputationSheet::withTrashed()->where('cs_control_number', 'LIKE', '%'.$version_key.'%')->orderBy('cs_version_number', 'desc')->first();
            $version_number = $cs_search->cs_version_number + 1;

            if($cs_search->cs_version_number != $cs->cs_version_number) return response()->json(['cs_version' => 'CS version is not allowed for delete. Use the latest CS: '.$cs_search->cs_control_number.' for your reference.'], 401);

            if($cs->deleted_at){
                $cs->restore();
            }
            else{
                $cs->delete();
            }

            DB::commit();
            return ['message' => 'CS successfully deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function getOrders(){
        return Orders::get();
    }

    public function getCSOrder(Request $request, $id)
    {
        $order = new stdClass();
        $order->order = Orders::where('id', $id)->first();

        $order->primary_materials = OrderMaterialsPrimary::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_materials_primary.*', 'materials.material_description as material_name')
            ->where('order_materials_primary.order_id', $id)
            ->leftJoin('materials', 'materials.id', 'order_materials_primary.material_id')
            ->get();

        $order->secondary_materials = OrderMaterialsSecondary::with('supplier_materials.supplier', 'material', 'supplier_materials.tracking', 'order')
            ->select('order_materials_secondary.*', 'materials.material_description as material_name')
            ->where('order_materials_secondary.order_id', $id)
            ->leftJoin('materials', 'materials.id', 'order_materials_secondary.material_id')
            ->get();

        $order->subcontractor_material = OrderMaterialsSubcon::with('supplier_sc_materials.supplier', 'supplier_sc_materials.tracking', 'sc_material', 'order')
            ->select('order_materials_subcon.*', 'sc_materials.sc_material_name as material_name')
            ->where('order_materials_subcon.order_id', $id)
            ->leftJoin('sc_materials', 'sc_materials.id', 'order_materials_subcon.material_id')
            ->get();

        $order->prints = OrderPrints::with('supplier_materials.supplier', 'supplier_materials.tracking', 'material', 'order')
            ->select('order_prints.*', 'materials.material_description as material_name')
            ->where('order_prints.order_id', $id)
            ->leftJoin('materials', 'materials.id', 'order_prints.material_id')
            ->get();

        $order->process_list = OrderProcess::select('order_processes.*', 'process.name as process_name')
            ->where('order_processes.order_id', $id)
            ->leftJoin('process', 'process.id', 'order_processes.order_process_id')
            ->get();

        $order->process_subcon = OrderProcessSubcon::where('order_id', $id)->get();

        $order->user_id = auth('api')->user()->id;

        foreach($order->primary_materials as $primary_material){
            $primary_material->total_material_quantity = '';
            $primary_material->total_material_amount = 0;
            $primary_material->cs_material_outs = '';
            $primary_material->cs_material_actual = '';
            $primary_material->cs_material_prm = '';

            foreach($primary_material->supplier_materials as $supplier_material){
                $supplier_material->supplier_material_quantity = '';
                $supplier_material->supplier_material_amount = 0;
                $supplier_material->supplier_material_total = '';
                $supplier_material->is_selected = false;
                $supplier_material_amount = 0;

                for ($i = 0; $i < count($supplier_material->tracking); $i++) {
                    if($supplier_material->tracking[$i]['material_flow'] > 1) unset($supplier_material->tracking[$i]);
                }
                
                $material_quantities = $supplier_material->tracking->pluck('material_quantity');
                $supplier_material_tracking = $supplier_material->tracking->pluck('material_unit_amount');
                $material_flow = $supplier_material->tracking->pluck('material_flow');
                for($i = 0; $i < count($supplier_material_tracking); $i++){
                    $supplier_material_amount += $supplier_material_tracking[$i] / $material_quantities[$i];
                }

                $exchange_rate = '';
                $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
                for ($i = 1; $i <= 4; $i++) {
                    $convert_from = substr($exchange_rate_label[$i - 1], 0, 3);
                    $convert_to = substr($exchange_rate_label[$i - 1], 5, 3);
                    if($convert_from == $supplier_material->supplier->transaction_currency && $convert_to == $order->order->order_currency) (float)$exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first()->rate;
                    else if($supplier_material->supplier->transaction_currency == $order->order->order_currency) $exchange_rate = 1;
                }

                $supplier_material->supplier_material_amount = round(((float)$supplier_material_amount / count($supplier_material_tracking) * (float)$exchange_rate), 4);
            }
        }    

        foreach($order->secondary_materials as $secondary_material){
            $secondary_material->total_material_quantity = '';
            $secondary_material->total_material_amount = '';

            foreach($secondary_material->supplier_materials as $supplier_material){
                $supplier_material->supplier_material_quantity = '';
                $supplier_material->supplier_material_amount = '';
                $supplier_material->supplier_material_total = '';
                $supplier_material->is_selected = false;
                $supplier_material_amount = 0;

                for ($i = 0; $i < count($supplier_material->tracking); $i++) {
                    if($supplier_material->tracking[$i]['material_flow'] > 1) unset($supplier_material->tracking[$i]);
                }
                
                $material_quantities = $supplier_material->tracking->pluck('material_quantity');
                $supplier_material_tracking = $supplier_material->tracking->pluck('material_unit_amount');
                $material_flow = $supplier_material->tracking->pluck('material_flow');
                for($i = 0; $i < count($supplier_material_tracking); $i++){
                    $supplier_material_amount += $supplier_material_tracking[$i] / $material_quantities[$i];
                }

                $exchange_rate = '';
                $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
                for ($i = 1; $i <= 4; $i++) {
                    $convert_from = substr($exchange_rate_label[$i - 1], 0, 3);
                    $convert_to = substr($exchange_rate_label[$i - 1], 5, 3);
                    if($convert_from == $supplier_material->supplier->transaction_currency && $convert_to == $order->order->order_currency) (float)$exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first()->rate;
                    else if($supplier_material->supplier->transaction_currency == $order->order->order_currency) $exchange_rate = 1;
                }

                $supplier_material->supplier_material_amount = round(((float)$supplier_material_amount / count($supplier_material_tracking) * (float)$exchange_rate), 4);
            }
        }

        $order->subcontractor_material[0]->total_sc_material_quantity = '';
        $order->subcontractor_material[0]->total_sc_material_amount = '';
        foreach($order->subcontractor_material[0]->supplier_sc_materials as $supplier_sc_material){
            $supplier_sc_material->supplier_sc_material_quantity = '';
            $supplier_sc_material->supplier_sc_material_amount = 0;
            $supplier_sc_material->supplier_sc_material_total = '';
            $supplier_sc_material->is_selected = false;
            $supplier_sc_material_amount = 0;

            for ($i = 0; $i < count($supplier_sc_material->tracking); $i++) {
                if($supplier_sc_material->tracking[$i]['sc_material_flow'] > 1) unset($supplier_sc_material->tracking[$i]);
            }

            $material_quantities = $supplier_sc_material->tracking->pluck('sc_material_quantity');
            $supplier_sc_material_tracking = $supplier_sc_material->tracking->pluck('sc_material_unit_amount');
            $sc_material_flow = $supplier_sc_material->tracking->pluck('sc_material_flow');
            for($i = 0; $i < count($supplier_sc_material_tracking); $i++){
                $supplier_sc_material_amount += $supplier_sc_material_tracking[$i] / $material_quantities[$i];
            }

            $exchange_rate = '';
            $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
            for ($i = 1; $i <= 4; $i++) {
                $convert_from = substr($exchange_rate_label[$i - 1], 0, 3);
                $convert_to = substr($exchange_rate_label[$i - 1], 5, 3);
                if($convert_from == $supplier_sc_material->supplier->transaction_currency && $convert_to == $order->order->order_currency) (float)$exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first()->rate;
                else if($supplier_sc_material->supplier->transaction_currency == $order->order->order_currency) $exchange_rate = 1;
            }

            $supplier_sc_material->supplier_sc_material_amount = round(((float)$supplier_sc_material_amount / count($supplier_sc_material_tracking) * (float)$exchange_rate), 4);
        }

        foreach($order->prints as $print){
            $print->total_material_quantity = '';
            $print->total_material_amount = '';

            foreach($print->supplier_materials as $supplier_material){
                $supplier_material->supplier_material_quantity = '';
                $supplier_material->supplier_material_amount = 0;
                $supplier_material->supplier_material_total = '';
                $supplier_material->is_selected = false;
                $supplier_material_amount = 0;

                for ($i = 0; $i < count($supplier_material->tracking); $i++) {
                    if($supplier_material->tracking[$i]['material_flow'] > 1) unset($supplier_material->tracking[$i]);
                }
                
                $material_quantities = $supplier_material->tracking->pluck('material_quantity');
                $supplier_material_tracking = $supplier_material->tracking->pluck('material_unit_amount');
                $material_flow = $supplier_material->tracking->pluck('material_flow');
                for($i = 0; $i < count($supplier_material_tracking); $i++){
                    $supplier_material_amount += $supplier_material_tracking[$i] / $material_quantities[$i];
                }

                $exchange_rate = '';
                $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
                for ($i = 1; $i <= 4; $i++) {
                    $convert_from = substr($exchange_rate_label[$i - 1], 0, 3);
                    $convert_to = substr($exchange_rate_label[$i - 1], 5, 3);
                    if($convert_from == $supplier_material->supplier->transaction_currency && $convert_to == $order->order->order_currency) (float)$exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first()->rate;
                    else if($supplier_material->supplier->transaction_currency == $order->order->order_currency) $exchange_rate = 1;
                }

                $supplier_material->supplier_material_amount = round(((float)$supplier_material_amount / count($supplier_material_tracking) * (float)$exchange_rate), 4);
            }
        }

        foreach($order->process_list as $process_list){
            $process_list->total_process_quantity = '';
            $process_list->total_process_amount = '';
            $process_list->process_subtotal = '';
        }

        foreach($order->process_subcon as $process){
            $process->total_process_quantity = '';
            $process->total_process_amount = '';
            $process->process_subtotal = '';
        }

        $order->exchange_rates = [];
        $exchange_rate_label = ['PHPToUSD', 'USDToPHP', 'JPYToUSD', 'JPYToPHP'];
        for ($i = 1; $i <= 4; $i++) {
            $exchange_rate = ExchangeRate::where('conversion_id', $i)->orderBy('id', 'desc')->first();
            $order->exchange_rates[$exchange_rate_label[$i - 1]] = $exchange_rate;
        }

        return response()->json($order);
    }

    public function evaluateCS(Request $request)
    {
        try{
            DB::beginTransaction();

            if($request['evaluation_status'] == 1) $evaluation_status = 2;
            else $evaluation_status = 1;

            if($request['evaluation_type'] == 2){
                if($request['evaluation_status'] == 3) $evaluation_status = 4;
                else $evaluation_status = 3;
            }
    
            $cs_evaluation = ComputationSheetEvaluation::where('id', $request['id'])->where('cs_id', $request['cs_id'])->update([
                'evaluation_status' => $evaluation_status,
                'evaluator_remarks' => $request['message']
            ]);

            DB::commit();
            return ['message' => 'CS evaluation successfully completed.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function rejectCSEvaluation(Request $request)
    {
        try{
            DB::beginTransaction();

            if($request['evaluation_status'] == 5 && $request['evaluation_type'] == 1) $evaluation_status = 1;
            else $evaluation_status = 5;

            if($request['evaluation_type'] == 2){
                if($request['evaluation_status'] == 5 && $request['evaluation_type'] == 3) $evaluation_status = 3;
                else $evaluation_status = 3;
            }
    
            $cs_evaluation = ComputationSheetEvaluation::where('id', $request['id'])->where('cs_id', $request['cs_id'])->update([
                'evaluation_status' => $evaluation_status,
                'evaluator_remarks' => $request['message']
            ]);

            DB::commit();
            return ['message' => 'CS evaluation successfully completed.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
