<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationInitialCharge extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'quotation_initial_charge';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quotation_id', 
        'item_name', 
        'item_qty',
        'item_unit_amt',
        'item_total_amt',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
