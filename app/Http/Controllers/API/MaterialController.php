<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use App\Materials;
use App\MaterialsTracking;
use App\SupplierMaterial;
use App\MaterialClassifications;
use App\TypeCode;
use App\Suppliers;
use App\RollMaterialTracking;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return Materials::paginate(10);

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
            return Materials::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('material_description','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Materials::where(function($query) use ($request){
                    $query->where('material_description','like','%'.$request['search'].'%');
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

            $type_code = TypeCode::where('id', $request['material_type_id'])->first()->type_code;

            $series = '';
            $series_number = 1;
            if(SupplierMaterial::first()) $series_number =  (int)SupplierMaterial::select(DB::raw('SUBSTR(`material_code`, 7, 12) as material_code_substr'))->orderBy('material_code_substr', 'desc')->first()->material_code_substr + 1;
            $series_length = 7;
            $series = substr('0000000'.$series_number, -$series_length);

            $material_code = 'AD2-'.$type_code.''.$series;

            $material_id = Materials::insertGetId([
                'material_description' => $request['material_description'],
                'material_classification_id' => $request['material_classification_id'],
                'material_type_id' => $request['material_type_id'],
                'material_length' => $request['material_length'],
                'material_length_unit' => $request['material_length_unit'],
                'material_width' => $request['material_width'],
                'material_width_unit' => $request['material_width_unit'],
                'material_last_in' => Carbon::now(),
                'material_last_out' => NULL,
                'material_location' => $request['material_location'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            
            $supplier_material_id = SupplierMaterial::insertGetId([
                'material_id' => $material_id,
                'supplier_id' => $request['supplier_material_id'],
                'material_code' => $material_code,
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $material_tracking_id = MaterialsTracking::insertGetId([
                'material_id' => $material_id,
                'supplier_material_id' => $supplier_material_id,
                'material_flow' => 1,
                'material_quantity' => $request['material_quantity'],
                'material_quantity_unit' => $request['material_quantity_unit'],
                'material_unit_amount' => $request['material_unit_amount'],
                'material_unit_amount_currency' => $request['material_unit_amount_currency'],
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if($request['material_type_id'] == 8){
                RollMaterialTracking::create([
                    'material_id' => $material_id,
                    'material_tracking_id' => $material_tracking_id,
                    'roll_length' => $request['material_length'],
                    'roll_length_unit' => $request['material_length_unit'],
                    'stock_roll_length' => $request['material_length'],
                    'stock_roll_quantity' => $request['material_quantity'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            DB::commit();
            return ['message' => 'Material successfully registered.'];
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
        $material = Materials::where('id', $id)->first();

        $flows = MaterialsTracking::where('material_id', $id)->pluck('material_flow')->toArray();
        $in_flows = 0;
        $out_flows = 0;

        if(in_array(1, $flows) || in_array(2, $flows)) $in_flows = array_sum(MaterialsTracking::where('material_id', $id)->whereIn('material_flow', [1, 2])->pluck('material_quantity')->toArray());
        if(in_array(3, $flows) || in_array(4, $flows)) $out_flows = array_sum(MaterialsTracking::where('material_id', $id)->whereIn('material_flow', [3, 4])->pluck('material_quantity')->toArray());

        $material->material_quantity = $in_flows - $out_flows;
        $material->material_quantity_unit = MaterialsTracking::where('material_id', $id)->first()->material_quantity_unit;
        $material->material_id = $id;

        $roll_materials = MaterialsTracking::select(
                'materials_tracking.material_quantity', 
                'materials_tracking.material_quantity_unit', 
                'roll_material_tracking.*', 
                'roll_material_tracking.roll_length as remaining_roll_material_length', 
                'materials.material_description', 
                'materials.material_length', 
                'materials.material_length_unit', 
                'materials.material_width', 
                'materials.material_width_unit',
                'roll_material_tracking.id as roll_material_tracking_id')
            ->where('materials_tracking.material_id', $id)
            ->leftJoin('roll_material_tracking', 'roll_material_tracking.material_tracking_id', '=', 'materials_tracking.id')
            ->leftJoin('materials', 'materials.id', '=', 'materials_tracking.material_id')
            ->where('roll_material_tracking.roll_length', '>', 0)
            ->get();
        $material->roll_materials = $roll_materials;

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
        $supplier_material = SupplierMaterial::where('id', $id)->first();

        try{
            DB::beginTransaction();

            Materials::where('id', $id)->update([
                'material_description' => $request['material_description'],
                'material_classification_id' => $request['material_classification_id'],
                'material_type_id' => $request['material_type_id'],
                'material_length' => $request['material_length'],
                'material_length_unit' => $request['material_length_unit'],
                'material_width' => $request['material_width'],
                'material_width_unit' => $request['material_width_unit'],
                'material_location' => $request['material_location'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            SupplierMaterial::where('id', $id)->update([
                'supplier_id' => $request['supplier_id'],
            ]);

            DB::commit();
            return ['message' => 'Material successfully updated.'];
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
    
            $supplier = Materials::withTrashed()->findOrFail($id);

            if($supplier->deleted_at){
                $supplier->restore();
            }
            else{
                $supplier->delete();
            }

            DB::commit();
            return ['message' => 'Raw material deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function getMaterialClassifications(){
        return MaterialClassifications::select('id', 'type_code_id', 'material_classification_name')->get();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getMaterialClassification($id){
        return MaterialClassifications::select('id', 'type_code_id', 'material_classification_name')->where('type_code_id', $id)->get();
    }

    public function getRMTypeCodes(){
        return TypeCode::select('id', 'type_code', 'type_code_description', 'is_rm')->where('is_rm', 1)->get();
    }

    public function getSuppliers(Request $request){
        if($request['material_flow_id'] >= 2 && $request['material_flow_id'] <= 4){
            $supplier_ids = SupplierMaterial::where('material_id', $request['material_id'])->pluck('supplier_id')->toArray();
            return Suppliers::select('id', 'supplier_code', 'supplier_name', 'transaction_currency')->whereIn('id', $supplier_ids)->get();
        }
        else{
            return Suppliers::select('id', 'supplier_code', 'supplier_name', 'transaction_currency')->get();
        }
    }

    public function getMaterialSuppliers(Request $request, $id){
        $material_id = $request['material_id'];

        $suppliers_table = SupplierMaterial::select('supplier_material.*', 'suppliers.id as supplier_id', 'suppliers.supplier_code', 'suppliers.supplier_name')
            ->where('supplier_material.material_id', $material_id)
            ->leftJoin('suppliers', 'suppliers.id', 'supplier_material.supplier_id')
            ->paginate($request['take']);

        $tracking_details = MaterialsTracking::select('id', DB::raw("SUM(material_quantity) as total_quantity"), 'material_quantity_unit', 'material_unit_amount', 'material_unit_amount_currency','supplier_material_id', 'created_at')
            ->where('material_id', $id)
            ->whereIn('material_flow', [1, 2])
            ->groupBy('supplier_material_id')
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
        $material_id = $request['material_id'];

        $flow = [(int)$request['flow']];
        if($flow[0] == 0) $flow = [1, 2, 3, 4];

        if($request['showDeleted'] == 'true'){
            return MaterialsTracking::select('materials_tracking.*', 'supplier_material.id as supplier_material_id', 'supplier_material.material_code', 'suppliers.supplier_name', 'suppliers.supplier_code')
                ->where('materials_tracking.material_id', $material_id)
                ->onlyTrashed()
                ->whereIn('material_flow', $flow)
                ->leftJoin('supplier_material', 'supplier_material.id', 'materials_tracking.supplier_material_id')
                ->leftJoin('suppliers', 'suppliers.id', 'supplier_material.supplier_id')
                ->orderBy('id', 'desc')
                ->paginate($request['take']);
        }
        else{
            return MaterialsTracking::select('materials_tracking.*', 'supplier_material.id as supplier_material_id', 'supplier_material.material_code', 'suppliers.supplier_name', 'suppliers.supplier_code')
                ->where('materials_tracking.material_id', $material_id)
                ->whereIn('material_flow', $flow)
                ->leftJoin('supplier_material', 'supplier_material.id', 'materials_tracking.supplier_material_id')
                ->leftJoin('suppliers', 'suppliers.id', 'supplier_material.supplier_id')
                ->orderBy('id', 'desc')
                ->paginate($request['take']);
        }
    }

    public function transactMaterialSupplier(Request $request, $id)
    {
        try{
            DB::beginTransaction();

            $type_code = TypeCode::where('id', $request['material_type_id'])->first()->type_code;

            $supplier = SupplierMaterial::where('material_id', $id)->where('supplier_id', $request['supplier_id'])->first();

            if(!$supplier){
                $series = '';
                $series_number = 1;
                if(SupplierMaterial::first()) $series_number =  (int)SupplierMaterial::select(DB::raw('SUBSTR(`material_code`, 7, 12) as material_code_substr'))->orderBy('material_code_substr', 'desc')->first()->material_code_substr + 1;
                $series_length = 7;
                $series = substr('0000000'.$series_number, -$series_length);
    
                $material_code = 'AD2-'.$type_code.''.$series;
                
                $supplier_material_id = SupplierMaterial::insertGetId([
                    'material_id' => $id,
                    'supplier_id' => $request['supplier_id'],
                    'material_code' => $material_code,
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                $material_tracking_id = MaterialsTracking::insertGetId([
                    'material_id' => $id,
                    'supplier_material_id' => $supplier_material_id,
                    'material_flow' => $request['material_flow_id'],
                    'material_quantity' => $request['material_quantity'],
                    'material_quantity_unit' => $request['material_quantity_unit'],
                    'material_unit_amount' => $request['material_unit_amount'],
                    'material_unit_amount_currency' => $request['material_unit_amount_currency'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            else{
                $series = '';
                $series_number = 1;
                if(SupplierMaterial::first()) $series_number =  (int)SupplierMaterial::select(DB::raw('SUBSTR(`material_code`, 7, 12) as material_code_substr'))->orderBy('material_code_substr', 'desc')->first()->material_code_substr + 1;
                $series_length = 7;
                $series = substr('0000000'.$series_number, -$series_length);
    
                $material_code = 'AD2-'.$type_code.''.$series;

                $material_tracking_id = MaterialsTracking::insertGetId([
                    'material_id' => $id,
                    'supplier_material_id' => $supplier->id,
                    'material_flow' => $request['material_flow_id'],
                    'material_quantity' => $request['material_quantity'],
                    'material_quantity_unit' => $request['material_quantity_unit'],
                    'material_unit_amount' => $request['material_unit_amount'],
                    'material_unit_amount_currency' => $request['material_unit_amount_currency'],
                    'employee_id' => auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            if($request['material_flow_id'] >= 3) Materials::where('id', $id)->update([
                'material_last_out' => Carbon::now(),
            ]);

            if($request['material_type_id'] == 8){
                $roll_material_tracking = RollMaterialTracking::where('id', $request['roll_material_tracking_id'])->first();

                if($request['material_flow_id'] == 1){
                    RollMaterialTracking::create([
                        'material_tracking_id' => $material_tracking_id,
                        'material_id' => $id,
                        'roll_length' => $request['material_length'],
                        'roll_length_unit' => $request['material_length_unit'],
                        'stock_roll_quantity' => $request['material_quantity'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                }
                else if($request['material_flow_id'] == 2){
                    
                    RollMaterialTracking::create([
                        'material_tracking_id' => $material_tracking_id,
                        'material_id' => $id,
                        'roll_length' => $request['material_roll_length'],
                        'roll_length_unit' => $request['material_length_unit'],
                        'stock_roll_quantity' => $request['material_quantity'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                    
                    RollMaterialTracking::where('id', $request['roll_material_tracking_id'])->update([
                        'stock_roll_quantity' => $roll_material_tracking->stock_roll_quantity,
                    ]);
                }
                else{

                    RollMaterialTracking::create([
                        'material_tracking_id' => $material_tracking_id,
                        'material_id' => $id,
                        'roll_length' => 0,
                        'roll_length_unit' => $request['material_length_unit'],
                        'stock_roll_quantity' => $request['material_quantity'],
                        'employee_id' => auth('api')->user()->id,
                    ]);
                    
                    RollMaterialTracking::where('id', $request['roll_material_tracking_id'])->update([
                        'stock_roll_quantity' => $roll_material_tracking->stock_roll_quantity - $request['material_quantity'],
                    ]);
                }
            }

            DB::commit();
            return ['message' => 'Material successfully transacted.'];
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
            return ['message' => 'Material classification successfully registered.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
    
    // Maintenance->Type Maintenance.vue
    public function showMaterialClassificationTable(Request $request, $id)
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
            return MaterialClassifications::onlyTrashed()->where('type_code_id', $id)
                ->where(function($query) use ($request){
                    $query->where('material_classification_name','like','%'.$request['search'].'%')
                    ->orWhere('type_code_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return MaterialClassifications::where('type_code_id', $id)->where(function($query) use ($request){
                    $query->where('material_classification_name','like','%'.$request['search'].'%')
                    ->orWhere('type_code_id','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
    }
    public function showTypeCodeTable(Request $request){
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
            return TypeCode::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('type_code','like','%'.$request['search'].'%')
                    ->orWhere('type_code_description','like','%'.$request['search'].'%')
                    ->orWhere('is_rm','like','%'.$request['search'].'%')
                    ->orWhere('is_sc','like','%'.$request['search'].'%')
                    ->orWhere('is_fg','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return TypeCode::where(function($query) use ($request){
                $query->where('type_code','like','%'.$request['search'].'%')
                ->orWhere('type_code_description','like','%'.$request['search'].'%')
                ->orWhere('is_rm','like','%'.$request['search'].'%')
                ->orWhere('is_sc','like','%'.$request['search'].'%')
                ->orWhere('is_fg','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
    }
    // Material classification delete
    public function deleteClassification($id)
    {
        try{
            $classification = MaterialClassifications::withTrashed()->findOrFail($id);
            if($classification->deleted_at){
                $classification->restore();
            }
            else{
                $classification->delete();
            }
        }catch(\Exception $e){
            MaterialClassifications::rollback();
            throw $e;
        }
        return $id;
    }

    public function deleteTypeCode($id)
    {
        try{
            $type_code = TypeCode::withTrashed()->findOrFail($id);
            if($type_code->deleted_at){
                $type_code->restore();
            }
            else{
                $type_code->delete();
            }
        }catch(\Exception $e){
            TypeCode::rollback();
            throw $e;
        }
    }
    //Maintenanace Management->Material Classification
    public function addClassification(Request $request, $id)
    {
        try{
        MaterialClassifications::create([
            'type_code_id'=> $request['typeCodeId'],
            'material_classification_name' => $request['name'],
        ]);
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
        return $id;
    }

    public function addTypeCode(Request $request)
    {
        try{
            TypeCode::create([
                'type_code'=> $request['type_code'],
                'type_code_description' => $request['type_code_description'],
                'is_rm	'=> $request['is_rm'],
                'is_sc	'=> $request['is_sc'],
                'is_fg	'=> $request['is_fg'], 
            ]);
        }
        catch(\Exception $e){
                DB::rollback();
                throw $e;
        }
    }

    public function updateClassification(Request $request, $id)
    {
        $name = $request['name'];
        $type_code = $request['typeCodeId'];

        MaterialClassifications::where('id', $id)->update([
            'material_classification_name' => $name,
            'type_code_id' => $type_code,
            'updated_at' => Carbon::now(),
        ]);
        return ['name'=>$name,
                'type_code'=>$type_code,
                'id'=>$id];
    }

    public function updateTypeCode(Request $request, $id)
    {
        TypeCode::where('id', $id)->update([
            'type_code' => $request['type_code'],
            'type_code_description' => $request['type_code_description'],
            'is_rm' => $request['is_rm'],
            'is_sc' => $request['is_sc'],
            'is_fg' => $request['is_fg'],
            'updated_at' => Carbon::now(),
        ]); 
    }
}
