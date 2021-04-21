<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesSheet extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'sales_sheet';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ss_reference_number', 
        'cs_id', 
        'ss_qty', 
        'ss_qty_remaining',
        'ss_total_amount',
        'ss_cost_price',
        'ss_cost_rate',
        'ss_gross_sales',
        'ss_sales_price',
        'ss_gross_profit',
        'ss_quotation_qty',
        'ss_payment_method',
        'ss_status',
        'ss_islinked',
        'ss_is_closed',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
