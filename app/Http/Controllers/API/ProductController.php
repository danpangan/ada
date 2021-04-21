<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use App\MaterialClassifications;
use App\TypeCode;
use App\Products;
use App\ProductsTracking;
use App\Client;

class ProductController extends Controller
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
            return Products::onlyTrashed()
                ->where(function($query) use ($request){
                    $query->where('product_code','like','%'.$request['search'].'%')
                    ->orWhere('product_part_number','like','%'.$request['search'].'%')
                    ->orWhere('product_name','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Products::where(function($query) use ($request){
                $query->where('product_code','like','%'.$request['search'].'%')
                    ->orWhere('product_part_number','like','%'.$request['search'].'%')
                    ->orWhere('product_name','like','%'.$request['search'].'%');
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
        // return $request;
        try{
            DB::beginTransaction();

            $type_code = TypeCode::where('id', $request['product_type_code_id'])->first()->type_code;

            $series = '';
            $series_number = 1;
            if(Products::first()) $series_number =  (int)Products::select(DB::raw('SUBSTR(`product_code`, 7, 12) as product_code_substr'))->orderBy('product_code_substr', 'desc')->first()->product_code_substr + 1;
            $series_length = 7;
            $series = substr('0000000'.$series_number, - $series_length);

            $product_code = 'AD'.$request['product_category_id'].'-'.$type_code.''.$series;

            $product_id = Products::insertGetId([
                'ss_reference_no' => $request['ss_reference_no'], 
                'order_item_code' => $request['order_item_code'], 
                'product_code' => $product_code, 
                'product_part_number' => $request['product_part_number'], 
                'product_name' => $request['product_name'], 
                'product_category_id' => $request['product_category_id'],
                'product_classification_id' => $request['product_classification_id'],
                'product_type_code_id' => $request['product_type_code_id'],
                'product_stock_quantity' => $request['product_stock_quantity'],
                'product_stock_quantity_unit' => $request['product_stock_quantity_unit'],
                'product_location' => $request['product_location'],
                'product_customer' => $request['product_customer'],
                'product_last_in' => Carbon::now(),
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // ProductsTracking::create([
            //     'product_id' => $product_id, 
            //     'product_flow' => 1, 
            //     'product_quantity' => $request['product_stock_quantity'],
            //     'employee_id' => auth('api')->user()->id,
            // ]);

            DB::commit();
            return ['message' => 'Product successfully registered.'];
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
        return Products::select('products.*', 'clients.id as product_customer')
            ->where('products.id', $id)
            ->leftJoin('clients', 'clients.id', 'products.product_customer')
            ->first();
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

            Products::where('id', $id)->update([
                'ss_reference_no' => $request['ss_reference_no'], 
                'order_item_code' => $request['order_item_code'], 
                'product_part_number' => $request['product_part_number'], 
                'product_name' => $request['product_name'], 
                'product_category_id' => $request['product_category_id'],
                'product_classification_id' => $request['product_classification_id'],
                'product_type_code_id' => $request['product_type_code_id'],
                'product_stock_quantity' => $request['product_stock_quantity'],
                'product_stock_quantity_unit' => $request['product_stock_quantity_unit'],
                'product_location' => $request['product_location'],
                'product_customer' => $request['product_customer'],
                'employee_id' => auth('api')->user()->id,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return ['message' => 'Product successfully updated.'];
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
    
            $product = Products::withTrashed()->findOrFail($id);

            if($product->deleted_at){
                $product->restore();
            }
            else{
                $product->delete();
            }

            DB::commit();
            return ['message' => 'Finish product deleted.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function getProductTypeCodes(){
        return TypeCode::select('id', 'type_code', 'type_code_description', 'is_fg')->where('is_fg', 1)->get();
    }

    public function getProductCustomers(){
        return Client::get();
    }
}
