<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Department;
use App\PermissionsDepartment;
use Carbon\Carbon;

class DepartmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

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
            $sortProp = 'department_name';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return Department::onlyTrashed()->
                where(function($query) use ($request){
                    $query->where('department_name','like','%'.$request['search'].'%')
                        ->orWhere('department_contact','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return Department::where(function($query) use ($request){
                    $query->where('department_name','like','%'.$request['search'].'%')
                        ->orWhere('department_contact','like','%'.$request['search'].'%');
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

            $department_id = Department::insertGetId([
                'department_name' => $request['department_name'],
                'department_contact' => $request['department_contact'],
                'department_code' => 0,
                'employee_id' => auth('api')->user()->id,
                'created_at' => Carbon::now(),
            ]);
            
            $permissions = $request['permissions'];

            for($i = 0; $i < count($permissions); $i++){
                for($j = 0; $j < count($permissions[$i]); $j++){
                    $selected_count = 0;
                    if($permissions[$i][$j]['isSelected'] == true){
                        $selected_count++;
                        PermissionsDepartment::create([
                            'item_id' => $permissions[$i][$j]['item_id'],
                            'department_id' => $department_id,
                            'category' => $permissions[$i][0]['permission_description'],
                            'description' => $permissions[$i][$j]['permission_description'],
                        ]);
                    }
                }
            }

            DB::commit();
            return ['message' => 'Department successfully registered.'];
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
    public function show($department)
    {
        $department = Department::with('permissions')->withTrashed()->where('id', $department)->first();
        return $department;
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
        $department = Department::findOrFail($id);

        $this->validate($request, [
            'department_name' => 'required|regex:/^[\pL\s\-]+$/u|max:30',
            'department_contact' => 'required|regex:/(0)[0-9]/',
        ]);

        try{
            DB::beginTransaction();    

            $department->update($request->all());

            $permissions = $request['permissions'];

            for($i = 0; $i < count($permissions); $i++){
                $selected_count = count($permissions[$i]) - 1;
                for($j = 0; $j < count($permissions[$i]); $j++){
                    if($permissions[$i][$j]['isSelected'] == true){
                        $existing_permissions = count(PermissionsDepartment::where('department_id', $department->id)->where('item_id', $permissions[$i][$j]['item_id'])->get());
                        if($existing_permissions == 0){
                            PermissionsDepartment::create([
                                'item_id' => $permissions[$i][$j]['item_id'],
                                'department_id' => $department->id,
                                'category' => $permissions[$i][0]['permission_description'],
                                'description' => $permissions[$i][$j]['permission_description'],
                            ]);
                        }
                    }
                    else{
                        $selected_count--;
                        $existing_permissions = count(PermissionsDepartment::where('department_id', $department->id)->where('item_id', $permissions[$i][$j]['item_id'])->get());
                        if($existing_permissions > 0){
                            PermissionsDepartment::where('department_id', $department->id)->where('item_id', $permissions[$i][$j]['item_id'])->delete();
                        }
                    }
                }

                if($selected_count == 0){
                    PermissionsDepartment::where('department_id', $department->id)->where('item_id', $permissions[$i][0]['item_id'])->delete();
                }
            }

            DB::commit();
            return ['message' => 'Department details updated'];
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
        $department = Department::withTrashed()->findOrFail($id);
        
        try{
            DB::beginTransaction();    

            if($department->deleted_at){
                $department->restore();
            }
            else{
                $department->delete();
            }

            DB::commit();
            return ['message' => 'Department deleted'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }

    }

    public function getAllDepartments(){
        return Department::get();
    }
}
