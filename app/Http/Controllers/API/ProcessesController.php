<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Process;
use Illuminate\Support\Facades\Hash;
use PDF;
use DB;
use Carbon\Carbon;
use App\User;
use SoftDeletes;
use Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProcessImport;


class ProcessesController extends Controller
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
            return Process::onlyTrashed()
                    ->where(function($query) use ($request){
                    $query->where('id','like','%'.$request['search'].'%')
                        ->orWhere('name','like','%'.$request['search'].'%')
                        ->orWhere('description','like','%'.$request['search'].'%')
                        ->orWhere('bundle','like','%'.$request['search'].'%')
                        ->orWhere('source_table','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Process::
                        where(function($query) use ($request){
                        $query->where('id','like','%'.$request['search'].'%')
                            ->orWhere('name','like','%'.$request['search'].'%')
                            ->orWhere('description','like','%'.$request['search'].'%')
                            ->orWhere('bundle','like','%'.$request['search'].'%')
                            ->orWhere('source_table','like','%'.$request['search'].'%');
                            })
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = $request['name'];
        $bundle = substr_count($name, ',') + 1;

        if($bundle > 1){
            $source_table = 'matrix_bundled';
        }else{
            $source_table = 'matrix_independent';
        }

        Process::create([
            'name' => $name,
            'bundle' => $bundle,
            'source_table' => $source_table,
            'description' => ''
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {   
        $show_all_bundle = $request['show_all_bundle'];
        $dropdown = $request['name'];
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];
        $source_table = $request['sourceTable'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'price';
            $sortOrder = 'asc';
        }
        
        $table_name = '';
        // Check the dropdown Id for table: gsm and printing
        if($id < 0){
            if($id == -1){
                $table_name = 'matrix_gsm';
                $table_title = $dropdown;
                $source_table = $table_name;
                $number_of_bundle = 0;
                $identifier = -1;
            }

            if($id == -2){
                $table_name = 'matrix_printing';
                $source_table = $table_name;
                $number_of_bundle = 0;
                $identifier = -2;
            }

            if($request['showDeleted'] == 'true'){
                $show_table = DB::table($table_name)
                    ->where('deleted_at', '!=', NULL)
                    ->orderBy($sortProp, $sortOrder)
                    ->paginate($request['take']);
            }
            else{
                $show_table = DB::table($table_name)
                    ->where('deleted_at', NULL)
                    ->orderBy($sortProp, $sortOrder)
                    ->paginate($request['take']);
            }
        }
        else{
            $table = Process::where('id', $id)->first();
            $table_name = $table->source_table;
    
            $show_table = DB::table($table_name)
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);

            $bundle_db = $table->bundle;

            if($request['showDeleted'] == 'true'){  
                if($bundle_db > 1){
                    if($show_all_bundle == 1){
                        $show_table = DB::table($table_name)
                        ->where('deleted_at', '!=', NULL)
                        ->orderBy($sortProp, $sortOrder)
                        ->paginate($request['take']);
                    }
                    else {
                        $show_table = DB::table($table_name)
                        ->where('bundle', $bundle_db)
                        ->where('deleted_at', '!=', NULL)
                        ->orderBy($sortProp, $sortOrder)
                        ->paginate($request['take']);
                    }
                }
                elseif($source_table == 'matrix_independent'){
                    $show_table = DB::table('matrix_independent')
                    ->where('ref_id', $id)
                    ->where('deleted_at','!=', NULL)
                    ->orderBy($sortProp, $sortOrder)
                    ->paginate($request['take']);
                }
                else{
                    if($show_all_bundle == 1){
                        $show_table = DB::table($table_name)
                        ->orderBy($sortProp, $sortOrder)
                        ->paginate($request['take']);}
                        $show_table = DB::table($table_name)
                        ->where('deleted_at', NULL)
                        ->orderBy($sortProp, $sortOrder)
                        ->paginate($request['take']);
                }
            }
            else{
                if($bundle_db > 1){
                    if($show_all_bundle == 1) $show_table = DB::table($table_name)
                    ->where('deleted_at', NULL)
                    ->orderBy($sortProp, $sortOrder)
                    ->paginate($request['take']);
                    else $show_table = DB::table($table_name)->where('bundle', $bundle_db)
                    ->orderBy($sortProp, $sortOrder)
                    ->paginate($request['take']);
                }
                elseif($source_table == 'matrix_independent'){
                    $price = DB::table('matrix_independent')->max('price');
                    $independent_id = DB::table('matrix_independent')->where('price', $price)->first();
                    $show_table = DB::table('matrix_independent')
                    ->where('ref_id', $independent_id->ref_id)
                    ->where('deleted_at', NULL)
                    ->orderBy($sortProp, $sortOrder)
                    ->paginate($request['take']);
                }
                else{
                $show_table = DB::table($table_name)
                    ->where('deleted_at', NULL)
                    ->orderBy($sortProp, $sortOrder)
                    ->paginate($request['take']);
                }
            }

            $table_title = $table->name;
            $source_table = $table->source_table;
            $number_of_bundle = $bundle_db;
            $identifier = 0;
        }
        
        if(empty($dropdown))
            return ['table_data' => $show_table,
                    'table_name' => $table_title,
                    'matrix_process' => $source_table,
                    'bundle_number' => $number_of_bundle,
                    'identifier' => $identifier,
                    ];
        else 
            return ['table_data' => $show_table,
                     'table_name' => 'Matrix'.' '.$dropdown, 
                     'matrix_process' => $source_table,
                     'bundle_number' => $number_of_bundle,
                     'identifier' => $identifier,
                    ];
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // Delete Main Process
    public function destroy($id)
    {
        if($id <= 12){
            return ['message'=>'Unable to delete'];
        }
        else try{
            DB::beginTransaction();
    
            $process = Process::withTrashed()->findOrFail($id);

            if($process->deleted_at){
                $process->restore();
            }
            else{
                $process->delete();
            }
            DB::commit();
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }

    }

    public function addPriceBracket(Request $request)
    {
        $process = $request['matrix_process'];
        $qty = $request['qty'];
        $bundle = $request['bundle'];
        $price = $request['price'];
        $paper_size = $request['paper_size'];
        $status = $request['status'];
        $gsm_from = $request['gsmFrom'];
        $gsm_to = $request['gsmTo'];
        $color_type = $request['colorType'];
        $sheets = $request['sheets'];
        $id = $request['process_id'];

        if($process == 'matrix_bundled'){
            // Check if existing
            $check_existing = count(DB::table($process)->where('bundle', $bundle)->where('qty', $qty)->get());

            if($check_existing == 0){
                DB::table($process)->insert([
                    'qty' => $qty,
                    'price' => $price,
                    'bundle' => $bundle,
                    'employee_id' =>auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }else{
                $bundle_object = DB::table($process)->where('bundle', $bundle)->where('qty', $qty)->first();
                // DB::table($process)->where('id', $bundle_object->id)->update([
                DB::table($process)->where('id', $bundle_object->id)->update([
                    'price' => $data[0][$i]['price'],
                    'employee_id' =>auth('api')->user()->id,
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        elseif($process == 'matrix_gsm'){
            // For Validation **** gsm from AND gsm to
            $bundle_count = count(DB::table($process)->where('gsm_from', $gsm_from)->where('gsm_to', $gsm_to)->get());
            if($bundle_count == 0){
                DB::table($process)->insert([
                    'gsm_from' => $gsm_from,
                    'gsm_to' => $gsm_to,
                    'sheets' => $sheets,
                    'employee_id' =>auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                    ]); 
            }else{
                $bundle_object = DB::table($process)->where('gsm_from', $gsm_from)->where('gsm_to', $gsm_to)->first();

                        DB::table($process)->where('id', $bundle_object->id)->update([
                            'sheets' => $sheets,
                            'employee_id' =>auth('api')->user()->id,
                            'updated_at' => Carbon::now(),
                        ]);
            }
        }
        elseif($process == 'matrix_printing'){
                $check_existing = count(DB::table($process)->where('qty', $qty)->where('color_type', $color_type)->get());
                    if($check_existing == 0){
                        DB::table($process)->insert([
                            'color_type' => $color_type,
                            'qty' => $qty,
                            'price' => $price,
                            'employee_id' =>auth('api')->user()->id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                            ]); 
                    }else{
                        $bundle_object = DB::table($process)->where('qty', $qty)->where('color_type', $color_type)->first();

                        DB::table($process)->where('id', $bundle_object->id)->update([
                            'price' => $price,
                            'employee_id' =>auth('api')->user()->id,
                            'updated_at' => Carbon::now(),
                        ]);
                    }
        }elseif($process == 'matrix_independent'){
            $check_existing = count(DB::table($process)->where('qty', $qty)->where('ref_id', $id)->get());
            if($check_existing == 0){
                DB::table($process)->insert([
                    'ref_id' => $id,
                    'qty' => $qty,
                    'price' => $price,
                    'employee_id' =>auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                    ]); 
            }else{
                $bundle_object = DB::table($process)->where('qty', $qty)->where('ref_id', $id)->first();

                DB::table($process)->where('id', $bundle_object->id)->update([
                    'price' => $price,
                    'employee_id' =>auth('api')->user()->id,
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
        else{
            $bundle_count = count(DB::table($process)->where('qty', $qty)->where('paper_size', $paper_size)->get());
            if($bundle_count == 0){
                DB::table($process)->insert([
                    'qty' => $qty,
                    'price' => $price,
                    'status' => $status,
                    'paper_size' => $paper_size,
                    'employee_id' =>auth('api')->user()->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                    ]); 
            }else{
                $bundle_object = DB::table($process)->where('qty', $qty)->where('paper_size', $paper_size)->first();
                DB::table($process)->where('id', $bundle_object->id)->update([
                    'price' => $price,
                    'employee_id' =>auth('api')->user()->id,
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function deletePriceBracket(Request $request, $id, $source_table_id)
   {
        $process_table = Process::where('id', $source_table_id)->first();
        $source_table = $process_table->source_table;
        $table = DB::table($source_table)->where('id', $id)->where('deleted_at','!=', NULL)->first();
        
        if($table){
                 DB::table($source_table)->where('id', $id)->update(['deleted_at' => NULL]);
        }
        else{
            DB::table($source_table)->where('id', $id)->update(['deleted_at' => Carbon::now()]);
        }    
    }
    //import excel file to DB
    public function importExcel(Request $request)
    {
        $headers = $request['config'];
        $process = $request['matrix_process'];
        $excel = $request['file'];
        $data = Excel::toArray(new ProcessImport, $excel);
        $arrayLength = sizeof($data[0]);
        
        for ($i = 0; $i < $arrayLength; $i++){
            if($process == 'matrix_bundled'){
                $bundle_count = count(DB::table($process)->where('bundle', $data[0][$i]['bundle'])->where('qty', $data[0][$i]['quantity'])->get());

                if($bundle_count == 0){
                    DB::table($process)->insert([
                        'bundle' => $data[0][$i]['bundle'],
                        'qty' => $data[0][$i]['quantity'],
                        'price' => $data[0][$i]['price'],
                        'employee_id' =>auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                else{
                    $bundle_object = DB::table($process)->where('bundle', $data[0][$i]['bundle'])->where('qty', $data[0][$i]['quantity'])->first();
                    DB::table($process)->where('id', $bundle_object->id)->update([
                        'price' => $data[0][$i]['price'],
                        'employee_id' =>auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
            elseif($process == 'matrix_gsm'){
                $bundle_count = count(DB::table($process)->where('gsm_from', $data[0][$i]['gsm_from'])->where('gsm_to', $data[0][$i]['gsm_to'])->get());
                if($bundle_count == 0){
                    DB::table($process)->insert([
                        'gsm_from' => $data[0][$i]['gsm_from'],
                        'gsm_to' => $data[0][$i]['gsm_to'],
                        'sheets' => $data[0][$i]['sheets'],
                        'employee_id' =>auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                        ]); 
                }
                else{
                    $bundle_object = DB::table($process)->where('gsm_from', $data[0][$i]['gsm_from'])->where('gsm_to', $data[0][$i]['gsm_to'])->first();
                    DB::table($process)->where('id', $bundle_object->id)->update([
                        'sheets' => $data[0][$i]['sheets'],
                        'employee_id' =>auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }            
            }
            // For matrix printing table
            elseif($process == 'matrix_printing'){
                $bundle_count = count(DB::table($process)->where('qty', $data[0][$i]['quantity'])->where('color_type', $data[0][$i]['color_type'])->get());
                if($bundle_count == 0){
                    DB::table($process)->insert([
                        'color_type' => $data[0][$i]['color_type'],
                        'qty' => $data[0][$i]['quantity'],
                        'price' => $data[0][$i]['price'],
                        'employee_id' =>auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                        ]); 
                }else{
                    $bundle_object = DB::table($process)->where('qty', $data[0][$i]['quantity'])->where('color_type', $data[0][$i]['color_type'])->first();

                    DB::table($process)->where('id', $bundle_object->id)->update([
                        'price' => $data[0][$i]['price'],
                        'employee_id' =>auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }else{
                $bundle_count = count(DB::table($process)->where('qty', $data[0][$i]['quantity'])->where('paper_size', $data[0][$i]['paper_size'])->get());
                if($bundle_count == 0){
                    DB::table($process)->insert([
                        'qty' => $data[0][$i]['quantity'],
                        'price' => $data[0][$i]['price'],
                        'status' => $data[0][$i]['status'],
                        'paper_size' => $data[0][$i]['paper_size'],
                        'employee_id' =>auth('api')->user()->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                        ]); 
                }else{
                    $bundle_object = DB::table($process)->where('qty', $data[0][$i]['quantity'])->where('paper_size', $data[0][$i]['paper_size'])->first();
                    DB::table($process)->where('id', $bundle_object->id)->update([
                        'price' => $data[0][$i]['price'],
                        'employee_id' =>auth('api')->user()->id,
                        'updated_at' => Carbon::now(),
                    ]);
                }
            }
        }
    }


}


