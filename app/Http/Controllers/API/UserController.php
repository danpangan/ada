<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use PDF;
use DB;
use App\User;
use App\Department;
use App\PermissionsUser;

class UserController extends Controller
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
        $user_id = auth('api')->user()->id;

        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'employee_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return User::select('users.*', 'departments.department_name as department_name')
                ->onlyTrashed()
                ->leftJoin('departments', 'departments.id', 'department_id')
                ->where('users.id', '!=', $user_id)
                ->where(function($query) use ($request){
                    $query->where('username','like','%'.$request['search'].'%')
                        ->orWhere('first_name','like','%'.$request['search'].'%')
                        ->orWhere('last_name','like','%'.$request['search'].'%')
                        ->orWhere('address','like','%'.$request['search'].'%')
                        ->orWhere('contact','like','%'.$request['search'].'%')
                        ->orWhere('departments.department_name','like','%'.$request['search'].'%')
                        ->orWhere('email','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return User::select('users.*', 'departments.department_name as department_name')
                ->leftJoin('departments', 'departments.id', 'department_id')
                ->where('users.id', '!=', $user_id)
                ->where(function($query) use ($request){
                    $query->where('username','like','%'.$request['search'].'%')
                        ->orWhere('first_name','like','%'.$request['search'].'%')
                        ->orWhere('last_name','like','%'.$request['search'].'%')
                        ->orWhere('address','like','%'.$request['search'].'%')
                        ->orWhere('contact','like','%'.$request['search'].'%')
                        ->orWhere('departments.department_name','like','%'.$request['search'].'%')
                        ->orWhere('email','like','%'.$request['search'].'%');
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

            $address = $request['address'];
            $first_name = $request['first_name'];
            $middle_name = $request['middle_name'];
            $last_name = $request['last_name'];
            $email = $request['email'];
            $contact = $request['contact'];
            $month_value = $request['month_value'];
            $day_value = $request['day_value'];
            $year_value = $request['year_value'];
            $gender = $request['gender_value'];
            $department = $request['department_value'];
            $user_role = $request['user_role'];

            $year = substr((string)$request['hired_year_value'], 2, 2);
            $month = substr('00'.$request['hired_month_value'], -2);
            $day = substr('00'.$request['hired_day_value'], -2);

            $series_key = $year.'-'.$month.''.$day;

            $series = '';
            $series_number = 1;
            $check_series_key = User::where('employee_number', 'LIKE', '%'.$series_key.'%')->first();
            if($check_series_key) $series_number =  (int)substr(User::where('employee_number', 'LIKE', '%'.$series_key.'%')->orderBy('employee_number', 'desc')->first()->employee_number, 9, 3) + 1;
            
            $series_length = 3;
            $series = substr('000'.$series_number, -$series_length);

            $employee_number = $year.'-'.$month.''.$day.'-'.$series;

            $password = Hash::make($employee_number);

            $user_id = User::create([
                'employee_number' => $employee_number,
                'username' => $employee_number,
                'password' => $password,
                'email' => $email,
                'first_name' => $first_name,
                'middle_name' => $middle_name,
                'last_name' => $last_name,
                'address' => $address,
                'contact' => $contact,
                'birthdate' => $year_value.'-'.$month_value.'-'.$day_value,
                'gender' => $gender,
                'department_id' => $department,
                'user_role' => $user_role,
                'employee_id' => auth('api')->user()->id,
            ]);
            
            $permissions = $request['permissions'];

            for($i = 0; $i < count($permissions); $i++){
                for($j = 0; $j < count($permissions[$i]); $j++){
                    $selected_count = 0;
                    if($permissions[$i][$j]['isSelected'] == true){
                        $selected_count++;
                        PermissionsUser::create([
                            'item_id' => $permissions[$i][$j]['item_id'],
                            'user_id' => $user_id,
                            'category' => $permissions[$i][0]['permission_description'],
                            'description' => $permissions[$i][$j]['permission_description'],
                        ]);
                    }
                }
            }

            DB::commit();
            return ['message' => 'User successfully registered'];
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
        return User::with('department')->with('permissions')->withTrashed()->where('id', $id)->first();
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

            $user = User::findOrFail($id);

            if($user->id == auth('api')->user()->id && strlen($request['password']) > 0){
                $this->validate($request, [
                    'password' => 'required|min:8',
                    'new_password' => 'required|different:password|min:8',
                    'retype_password' => 'required|same:new_password|min:8',
                ]);
                
                $user->update(['password' => Hash::make($request['new_password'])]);
            }

            if($request['month_value'] && $request['day_value'] && $request['year_value']){
                $birthdate = $request['year_value'].'-'.$request['month_value'].'-'.$request['day_value'];
                $user->update(['birthdate' => date('Y-m-d H:i:s', strtotime($birthdate))]);
            }

            if($request['gender_value']){
                $user->update(['gender' => $request['gender_value']]);
            }

            if($request['department_value']){
                $user->update(['department_id' => $request['department_value']]);
            }

            $user->update($request->except('password', 'birthdate', 'gender', 'department_id'));

            $permissions = $request['permissions'];

            for($i = 0; $i < count($permissions); $i++){
                $selected_count = count($permissions[$i]) - 1;
                for($j = 0; $j < count($permissions[$i]); $j++){
                    if($permissions[$i][$j]['isSelected'] == true){
                        $existing_permissions = count(PermissionsUser::where('user_id', $user->id)->where('item_id', $permissions[$i][$j]['item_id'])->get());
                        if($existing_permissions == 0){
                            PermissionsUser::create([
                                'item_id' => $permissions[$i][$j]['item_id'],
                                'user_id' => $user->id,
                                'category' => $permissions[$i][0]['permission_description'],
                                'description' => $permissions[$i][$j]['permission_description'],
                            ]);
                        }
                    }
                    else{
                        $selected_count--;
                        $existing_permissions = count(PermissionsUser::where('user_id', $user->id)->where('item_id', $permissions[$i][$j]['item_id'])->get());
                        if($existing_permissions > 0){
                            PermissionsUser::where('user_id', $user->id)->where('item_id', $permissions[$i][$j]['item_id'])->delete();
                        }
                    }
                }

                if($selected_count == 0){
                    PermissionsUser::where('user_id', $user->id)->where('item_id', $permissions[$i][0]['item_id'])->delete();
                }
            }

            DB::commit();
            return ['message' => 'User details updated'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }

    }

    /**
     * Remove the specified resource from storage.
     *s
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            DB::beginTransaction();
    
            $user = User::withTrashed()->findOrFail($id);

            if($user->deleted_at){
                $user->restore();
            }
            else{
                $user->delete();
            }

            DB::commit();
            return ['message' => 'User deleted'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }
    }

    public function resetPassword(Request $request, $employee_number){
        $reset_password = Hash::make($employee_number);

        try{
            DB::beginTransaction();
    
            User::where('employee_number', $employee_number)->update(['password' => $reset_password]);

            DB::commit();
            return ['message' => 'Password successfully updated.'];
        }
        catch(\Exception $e){
            DB::rollback();
            throw $e;
        }

    }

    public function departmentUsers(Request $request, $department_id){
        $sortProp = $request['sortProp'];
        $sortOrder = $request['sortOrder'];

        if($sortProp != 'undefined' && $sortOrder != 'undefined'){
            if($sortOrder === 'descending') $sortOrder = 'desc';
            else $sortOrder = 'asc';
        }
        else{
            $sortProp = 'employee_number';
            $sortOrder = 'asc';
        }

        if($request['showDeleted'] == 'true'){
            return User::select('users.*', 'departments.department_name as department_name')
                ->onlyTrashed()
                ->leftJoin('departments', 'departments.id', 'department_id')
                ->where('department_id', $department_id)
                ->where(function($query) use ($request){
                    $query->where('username','like','%'.$request['search'].'%')
                        ->orWhere('first_name','like','%'.$request['search'].'%')
                        ->orWhere('last_name','like','%'.$request['search'].'%')
                        ->orWhere('address','like','%'.$request['search'].'%')
                        ->orWhere('contact','like','%'.$request['search'].'%')
                        ->orWhere('departments.department_name','like','%'.$request['search'].'%')
                        ->orWhere('email','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
        else{
            return User::select('users.*', 'departments.department_name as department_name')
                ->leftJoin('departments', 'departments.id', 'department_id')
                // ->where('users.id', '!=', $user_id)
                ->where('department_id', $department_id)
                ->where(function($query) use ($request){
                    $query->where('username','like','%'.$request['search'].'%')
                        ->orWhere('first_name','like','%'.$request['search'].'%')
                        ->orWhere('last_name','like','%'.$request['search'].'%')
                        ->orWhere('address','like','%'.$request['search'].'%')
                        ->orWhere('contact','like','%'.$request['search'].'%')
                        ->orWhere('departments.department_name','like','%'.$request['search'].'%')
                        ->orWhere('email','like','%'.$request['search'].'%');
                })
                ->orderBy($sortProp, $sortOrder)
                ->paginate($request['take']);
        }
    }
}
