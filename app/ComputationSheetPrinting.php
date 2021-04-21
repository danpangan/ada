<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetPrinting extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_printing';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
        'cs_order_prints_id',
        'cs_material_id',
        'cs_front_amount',
        'cs_front_regular_amount',
        'cs_front_special_amount',
        'cs_back_amount',
        'cs_back_regular_amount',
        'cs_back_special_amount',
        'cs_regular_amount',
        'cs_special_amount',
        'cs_total_material_amount',
        'cs_total_material_quantity',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
