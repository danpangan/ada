<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Suppliers;
use App\SupplierContacts;
use App\Municipalities;
use App\Provinces;
use DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // return Suppliers::paginate(10);

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'supplier_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return Suppliers::onlyTrashed()->
                where(function($query) use ($request){
                    $query->where('supplier_code','like','%'.$request['search'].'%')
                        ->orWhere('supplier_name','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Suppliers::where(function($query) use ($request){
                    $query->where('supplier_code','like','%'.$request['search'].'%')
                        ->orWhere('supplier_name','like','%'.$request['search'].'%');
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
        $supplier_category = $request['supplier_category'];
        $supplier_classification_id = $request['supplier_classification_id'];

        if($supplier_category > 0) $supplier_code = Suppliers::where('supplier_category', $supplier_category)->where('supplier_classification_id', $supplier_classification_id)->orderBy('id', 'desc')->first()->supplier_code;
        else $supplier_code = Suppliers::where('supplier_province_id', NULL)->orderBy('id', 'desc')->first()->supplier_code;

        $series_number = (int)substr($supplier_code, 2, 4) + 1;

        $series_length = 3;
        $series = substr('000'.$series_number, -$series_length);

        $supplier_code = $supplier_category.''.$supplier_classification_id.''.$series;

        try{
            DB::beginTransaction();

            $supplier_id = Suppliers::insertGetId([
                'supplier_code' => $supplier_code,
                'supplier_name' => $request['supplier_name'],
                'supplier_province_id' => $request['client_province_id'],
                'supplier_municipality_id' => $request['client_municipality_id'],
                'supplier_address' => $request['supplier_address'],
                'supplier_email' => $request['supplier_email'],
                'supplier_contact' => $request['supplier_contact'],
                'supplier_fax' => $request['supplier_fax'],
                'transaction_currency' => $request['transaction_currency'],
                'supplier_materials' => $request['supplier_materials'],
                'supplier_category' => $supplier_category,
                'supplier_classification_id' => $supplier_classification_id,
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            Provinces::where('id', $request['client_province_id'])->update(['is_supplier_active' => 1]);
            Municipalities::where('id', $request['client_municipality_id'])->update(['is_supplier_active' => 1]);
            
            for($i = 0; $i < count($request['contactPersons']); $i++){
                SupplierContacts::create([
                    'supplier_id' => $supplier_id,
                    'supplier_contact_name' => $request['contactPersons'][$i]['contact_name'],
                    'supplier_contact_email' => $request['contactPersons'][$i]['contact_email'],
                    'employee_id' => auth('api')->user()->id,
                ]);
            }

            DB::commit();
            return ['message' => 'Supplier successfully registered.'];
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
        $supplier = Suppliers::withTrashed()->where('id', $id)->first();
        $supplier_contacts = SupplierContacts::where('supplier_id', $supplier->id)->get();
        return ['supplier' => $supplier, 'supplier_contacts' => $supplier_contacts];
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

            Suppliers::where('id', $id)->update([
                'supplier_name' => $request['supplier_name'],
                'supplier_province_id' => $request['supplier_province_id'],
                'supplier_municipality_id' => $request['supplier_municipality_id'],
                'supplier_address' => $request['supplier_address'],
                'supplier_email' => $request['supplier_email'],
                'supplier_contact' => $request['supplier_contact'],
                'supplier_fax' => $request['supplier_fax'],
                'transaction_currency' => $request['transaction_currency'],
                'supplier_materials' => $request['supplier_materials'],
                'supplier_category' => $supplier_category = $request['supplier_category'],
                'supplier_classification_id' => $request['supplier_classification_id'],
                'employee_id' => auth('api')->user()->id,
            ]);

            Provinces::where('id', $request['client_province_id'])->update(['is_supplier_active' => 1]);
            Municipalities::where('id', $request['client_municipality_id'])->update(['is_supplier_active' => 1]);

            for($i = 0; $i < count($request['contactPersons']); $i++){
                if($request['contactPersons'][$i]['deleted_at'] == 'false'){
                    SupplierContacts::where('id', $request['contactPersons'][$i]['id'])->delete();
                }
                else{
                    $supplier_contact_check = count(SupplierContacts::where('id', $request['contactPersons'][$i]['id'])->get());
                    if($supplier_contact_check > 0){
                        SupplierContacts::where('id', $request['contactPersons'][$i]['id'])->update([
                            'supplier_id' => $id,
                            'supplier_contact_name' => $request['contactPersons'][$i]['supplier_contact_name'],
                            'supplier_contact_email' => $request['contactPersons'][$i]['supplier_contact_email'],
                            'employee_id' => auth('api')->user()->id,
                        ]);
                    }
                    else{
                        SupplierContacts::create([
                            'supplier_id' => $id,
                            'supplier_contact_name' => $request['contactPersons'][$i]['supplier_contact_name'],
                            'supplier_contact_email' => $request['contactPersons'][$i]['supplier_contact_email'],
                            'employee_id' => auth('api')->user()->id,
                        ]);
                    }
                }
            }

            DB::commit();
            return ['message' => 'Supplier successfully registered.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }

        return ['message' => 'Supplier successfully updated.'];
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
    
            $supplier = Suppliers::withTrashed()->findOrFail($id);

            if($supplier->deleted_at){
                $supplier->restore();
            }
            else{
                $supplier->delete();
            }

            DB::commit();
            return ['message' => 'Supplier deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }
}
