<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Location;
use App\Provinces;
use App\Municipalities;
use Carbon\Carbon;
use DB;

class LocationsController extends Controller
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

        if($request['filter'] == 1){
            $customer_filter = [1];
            $supplier_filter = [0, 1];
        }
        else if($request['filter'] == 2){
            $customer_filter = [0, 1];
            $supplier_filter = [1];
        }
        else{
            $customer_filter = [0, 1];
            $supplier_filter = [0, 1];
        }

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'province_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return Provinces::onlyTrashed()->
                where(function($query) use ($request){
                    $query->where('province_name','like','%'.$request['search'].'%');
                })
                ->whereIn('is_customer_active', $customer_filter)
                ->whereIn('is_supplier_active', $supplier_filter)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Provinces::where(function($query) use ($request){
                    $query->where('province_name','like','%'.$request['search'].'%');
                })
                ->whereIn('is_customer_active', $customer_filter)
                ->whereIn('is_supplier_active', $supplier_filter)
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
        return 'CREATED';
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

            Provinces::create([
                'province_name' => $request['province_name'],
                'is_customer_active' => 0,
                'is_supplier_active' => 0,
                'employee_id' => auth('api')->user()->id,
            ]);

            DB::commit();
            return ['message' => 'Province successfully registered.'];
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
    public function show(Request $request, $id)
    {
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($request['filter'] == 1){
            $customer_filter = [1];
            $supplier_filter = [0, 1];
        }
        else if($request['filter'] == 2){
            $customer_filter = [0, 1];
            $supplier_filter = [1];
        }
        else{
            $customer_filter = [0, 1];
            $supplier_filter = [0, 1];
        }

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'municipality_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return Municipalities::where('province_id', $id)->onlyTrashed()->
                where(function($query) use ($request){
                    $query->where('municipality_name','like','%'.$request['search'].'%');
                })
                ->whereIn('is_customer_active', $customer_filter)
                ->whereIn('is_supplier_active', $supplier_filter)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Municipalities::where('province_id', $id)
                ->where(function($query) use ($request){
                    $query->where('municipality_name','like','%'.$request['search'].'%');
                })
                ->whereIn('is_customer_active', $customer_filter)
                ->whereIn('is_supplier_active', $supplier_filter)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
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
        try{
            DB::beginTransaction();    

            Municipalities::where('id', $id)->update([
                'municipality_name' => $request['municipality_name'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Municipality successfully updated.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
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

            Provinces::where('id', $id)->update([
                'province_name' => $request['province_name'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Province successfully updated.'];
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
            $province = Provinces::withTrashed()->findOrFail($id);
            if($province->deleted_at){
                $province->restore();
            }
            else{
                $province->delete();
            }
        }
        catch(\Exception $e){
            Provinces::rollback();
            throw $e;
        }
    }

    public function getMunicipality(Request $request, $id)
    {
        return Municipalities::select('municipalities.id as municipality_id', 'municipalities.municipality_name as municipality_name', 'provinces.id as province_id', 'provinces.province_name')
            ->where('municipalities.id', $id)
            ->leftJoin('provinces', 'provinces.id', 'municipalities.province_id')
            ->first();
    }

    public function deleteMunicipality($id)
    {
        $municipality = Municipalities::withTrashed()->findOrFail($id);

        if($municipality->deleted_at){
            $municipality->restore();
        }
        else{
            $municipality->delete();
        }
    }

    public function addMunicipality(Request $request, $id)
    {
        try{
            DB::beginTransaction();  

            Municipalities::create([
                'province_id' => $id,
                'municipality_name' => $request['municipality_name'],
                'is_customer_active' => 0,
                'is_supplier_active' => 0,
                'employee_id' => auth('api')->user()->id,
            ]);

            DB::commit();
            return ['message' => 'Municipality successfully registered.'];
        }
        catch(\Exception $e){
                DB::rollback();
                throw $e;
        }
    }

    public function updateMunicipality(Request $request, $id)
    {
        try{
            DB::beginTransaction();    

            Municipalities::where('id', $id)->update([
                'municipality_name' => $request['municipality_name'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Municipality successfully updated.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
