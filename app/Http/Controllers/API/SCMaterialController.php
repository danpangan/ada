<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use App\SCMaterials;
use App\SCMaterialsTracking;
use App\SupplierSCMaterial;
use App\MaterialClassifications;
use App\TypeCode;
use App\Suppliers;

class SCMaterialController extends Controller
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
            return SCMaterials::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('sc_material_name','like','%'.$request['search'].'%')
                    ->orWhere('sc_order_part_no','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return SCMaterials::where(function($query) use ($request){
                    $query->where('sc_material_name','like','%'.$request['search'].'%')
                    ->orWhere('sc_order_part_no','like','%'.$request['search'].'%');
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

            $type_code = TypeCode::where('id', $request['sc_material_type_id'])->first()->type_code;

            $series = '';
            $series_number = 1;
            if(SupplierSCMaterial::first()) $series_number =  (int)SupplierSCMaterial::select(DB::raw('SUBSTR(`sc_material_code`, 7, 12) as sc_material_code_substr'))->orderBy('sc_material_code_substr', 'desc')->first()->sc_material_code_substr + 1;
            $series_length = 7;
            $series = substr('0000000'.$series_number, -$series_length);

            $sc_material_code = 'AD2-'.$type_code.''.$series;

            $sc_material_id = SCMaterials::insertGetId([
                'sc_material_name' => $request['sc_material_name'],
                'sc_order_part_no' => $request['sc_order_part_number'],
                'sc_material_classification_id' => $request['sc_material_classification_id'],
                'sc_material_type_id' => $request['sc_material_type_id'],
                'sc_material_last_in' => Carbon::now(),
                'sc_material_last_out' => NULL,
                'sc_material_location' => $request['sc_material_location'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $supplier_sc_material_id = SupplierSCMaterial::insertGetId([
                'sc_material_id' => $sc_material_id,
                'supplier_id' => $request['supplier_sc_material_id'],
                'sc_material_code' => $sc_material_code,
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $material_tracking_id = SCMaterialsTracking::insertGetId([
                'sc_material_id' => $sc_material_id,
                'supplier_sc_material_id' => $supplier_sc_material_id,
                'sc_material_flow' => 1,
                'sc_material_quantity' => $request['sc_material_quantity'],
                'sc_material_quantity_unit' => $request['sc_material_quantity_unit'],
                'sc_material_unit_amount' => $request['sc_material_unit_amount'],
                'sc_material_unit_amount_currency' => $request['sc_material_unit_amount_currency'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Subcontract material successfully registered.'];
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
        // return $id;
        $material = SCMaterials::where('id', $id)->first();

        $flows = SCMaterialsTracking::where('sc_material_id', $id)->pluck('sc_material_flow')->toArray();
        $in_flows = 0;
        $out_flows = 0;

        if(in_array(1, $flows) || in_array(2, $flows)) $in_flows = array_sum(SCMaterialsTracking::where('sc_material_id', $id)->whereIn('sc_material_flow', [1, 2])->pluck('sc_material_quantity')->toArray());
        if(in_array(3, $flows) || in_array(4, $flows)) $out_flows = array_sum(SCMaterialsTracking::where('sc_material_id', $id)->whereIn('sc_material_flow', [3, 4])->pluck('sc_material_quantity')->toArray());

        $material->sc_material_quantity = $in_flows - $out_flows;
        $material->sc_material_quantity_unit = SCMaterialsTracking::where('sc_material_id', $id)->first()->sc_material_quantity_unit;
        $material->sc_material_id = $id;

        return $material;
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
        $supplier_sc_material = SupplierSCMaterial::where('id', $id)->first();

        try{
            DB::beginTransaction();

            SCMaterials::where('id', $id)->update([
                'sc_material_name' => $request['sc_material_name'],
                'sc_order_part_no' => $request['sc_order_part_no'],
                'sc_material_classification_id' => $request['sc_material_classification_id'],
                'sc_material_type_id' => $request['sc_material_type_id'],
                'sc_material_location' => $request['sc_material_location'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            SupplierSCMaterial::where('id', $id)->update([
                'supplier_id' => $request['supplier_id'],
            ]);

            DB::commit();
            return ['message' => 'Subcontract material successfully updated.'];
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
    
            $supplier = SCMaterials::withTrashed()->findOrFail($id);

            if($supplier->deleted_at){
                $supplier->restore();
            }
            else{
                $supplier->delete();
            }

            DB::commit();
            return ['message' => 'Subcontract material deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function getSCTypeCodes(){
        return TypeCode::select('id', 'type_code', 'type_code_description', 'is_sc')->where('is_sc', 1)->get();
    }

    public function getMaterialSuppliers(Request $request, $id){
        $sc_material_id = $request['sc_material_id'];

        $suppliers_table = SupplierSCMaterial::select('supplier_sc_material.*', 'suppliers.id as supplier_id', 'suppliers.supplier_code', 'suppliers.supplier_name')
            ->where('supplier_sc_material.sc_material_id', $sc_material_id)
            ->leftJoin('suppliers', 'suppliers.id', 'supplier_sc_material.supplier_id')
            ->paginate($request['take']);

        $tracking_details = SCMaterialsTracking::select('id', DB::raw("SUM(sc_material_quantity) as total_quantity"), 'sc_material_quantity_unit', 'sc_material_unit_amount', 'sc_material_unit_amount_currency','supplier_sc_material_id', 'created_at')
            ->where('sc_material_id', $id)
            ->whereIn('sc_material_flow', [1, 2])
            ->groupBy('supplier_sc_material_id')
            ->get();

    return ['suppliers' => $suppliers_table, 'tracking_details' => $tracking_details];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMaterialsTracking(Request $request)
    {
        $sc_material_id = $request['sc_material_id'];

        $flow = [(int)$request['flow']];
        if($flow[0] == 0) $flow = [1, 2, 3, 4];

        if($request['showDeleted'] == 'true'){
            return SCMaterialsTracking::select('sc_materials_tracking.*', 'supplier_sc_material.id as supplier_sc_material_id', 'supplier_sc_material.sc_material_code', 'suppliers.supplier_name', 'suppliers.supplier_code')
                ->where('sc_materials_tracking.material_id', $sc_material_id)
                ->onlyTrashed()
                ->whereIn('sc_material_flow', $flow)
                ->leftJoin('supplier_sc_material', 'supplier_sc_material.id', 'sc_materials_tracking.supplier_sc_material_id')
                ->leftJoin('suppliers', 'suppliers.id', 'supplier_sc_material.supplier_id')
                ->orderBy('id', 'desc')
                ->paginate($request['take']);
        }
        else{
            return SCMaterialsTracking::select('sc_materials_tracking.*', 'supplier_sc_material.id as supplier_sc_material_id', 'supplier_sc_material.sc_material_code', 'suppliers.supplier_name', 'suppliers.supplier_code')
                ->where('sc_materials_tracking.sc_material_id', $sc_material_id)
                ->whereIn('sc_material_flow', $flow)
                ->leftJoin('supplier_sc_material', 'supplier_sc_material.id', 'sc_materials_tracking.supplier_sc_material_id')
                ->leftJoin('suppliers', 'suppliers.id', 'supplier_sc_material.supplier_id')
                ->orderBy('id', 'desc')
                ->paginate($request['take']);
        }
    }

    public function storeMaterialSupplier(Request $request, $id)
    {
        try{
            DB::beginTransaction();

            $type_code = TypeCode::where('id', $request['sc_material_type_id'])->first()->type_code;

            $supplier = SupplierSCMaterial::where('sc_material_id', $id)->where('supplier_id', $request['sc_supplier_id'])->first();

            if(!$supplier){
                $series = '';
                $series_number = 1;
                if(SupplierSCMaterial::first()) $series_number =  (int)SupplierSCMaterial::select(DB::raw('SUBSTR(`sc_material_code`, 7, 12) as sc_material_code_substr'))->orderBy('sc_material_code_substr', 'desc')->first()->sc_material_code_substr + 1;
                $series_length = 7;
                $series = substr('0000000'.$series_number, -$series_length);
    
                $sc_material_code = 'AD2-'.$type_code.''.$series;
                
                $supplier_sc_material_id = SupplierSCMaterial::insertGetId([
                    'sc_material_id' => $id,
                    'supplier_id' => $request['sc_supplier_id'],
                    'sc_material_code' => $sc_material_code,
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                SCMaterialsTracking::insertGetId([
                    'sc_material_id' => $id,
                    'supplier_sc_material_id' => $supplier_sc_material_id,
                    'sc_material_flow' => $request['material_flow_id'],
                    'sc_material_quantity' => $request['sc_material_quantity'],
                    'sc_material_quantity_unit' => $request['sc_material_quantity_unit'],
                    'sc_material_unit_amount' => $request['sc_material_unit_amount'],
                    'sc_material_unit_amount_currency' => $request['sc_material_unit_amount_currency'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            else{
                $series = '';
                $series_number = 1;
                if(SupplierSCMaterial::first()) $series_number =  (int)SupplierSCMaterial::select(DB::raw('SUBSTR(`sc_material_code`, 7, 12) as sc_material_code_substr'))->orderBy('sc_material_code_substr', 'desc')->first()->sc_material_code_substr + 1;
                $series_length = 7;
                $series = substr('0000000'.$series_number, -$series_length);
    
                $sc_material_code = 'AD2-'.$type_code.''.$series;

                SCMaterialsTracking::create([
                    'sc_material_id' => $id,
                    'supplier_sc_material_id' => $supplier->id,
                    'sc_material_flow' => $request['material_flow_id'],
                    'sc_material_quantity' => $request['sc_material_quantity'],
                    'sc_material_quantity_unit' => $request['sc_material_quantity_unit'],
                    'sc_material_unit_amount' => $request['sc_material_unit_amount'],
                    'sc_material_unit_amount_currency' => $request['sc_material_unit_amount_currency'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            if($request['material_flow_id'] >= 3) SCMaterials::where('id', $id)->update([
                'sc_material_last_out' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Subcontract material successfully transacted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function addMaterialClassification(Request $request, $id){
        try{
            DB::beginTransaction();
    
            MaterialClassifications::create([
                'type_code_id' => $request['type_code_id'],
                'material_classification_name' => $request['material_classification_name'],
                'employee_id' => auth('api')->user()->id,
            ]);

            DB::commit();
            return ['message' => 'Subcontract material classification successfully registered.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    
    }
}
