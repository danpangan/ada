<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetEvaluation extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_evaluation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
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
