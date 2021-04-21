<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use \stdClass;
use App\User;

class EvaluationController extends Controller
{
    public function getEvaluators(Request $request)
    {
        $user_id = auth('api')->user()->id;

        $checkers = User::select('users.id', 'users.employee_number', 'users.username', 'users.first_name', 'users.last_name', 'departments.department_code', 'departments.department_name')
            ->leftJoin('departments', 'departments.id', 'users.department_id')
            ->where('users.id', '!=', $user_id)
            ->where('users.is_checker', 1)
            ->get();

        $approvers = User::select('users.id', 'users.employee_number', 'users.username', 'users.first_name', 'users.last_name', 'departments.department_code', 'departments.department_name')
            ->leftJoin('departments', 'departments.id', 'users.department_id')
            ->where('users.id', '!=', $user_id)
            ->where('users.is_approver', 1)
            ->get();

        return [
            'user_id' => $user_id,
            'checkers' => $checkers,
            'approvers' => $approvers
        ];
    }
}
