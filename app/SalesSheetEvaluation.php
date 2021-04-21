<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesSheetEvaluation extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'sales_sheet_evaluation';

    protected $fillable = [
        'ss_id', 
        'evaluator_id',
        'evaluation_type',
        'evaluator_remarks',
        'evaluation_status',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
