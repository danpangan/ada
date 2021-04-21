<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationOrder extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'quotation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quotation_id',  
        'order_id',
        'order_qty',
        'order_unit_amount',
        'order_unit_amount_currency',
        'order_unit_amount_php',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
