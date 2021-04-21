<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationEvaluation extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'quotation_evaluation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quotation_id',
        'evaluator_id',
        'evaluation_type',
        'evaluation_status',
        'evaluator_name', 
        'evaluator_permission', 
        'evaluator_remarks',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
