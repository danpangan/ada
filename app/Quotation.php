<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'quotation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quotation_number',
        'client_id',
        'order_id',
        'client_pic_id',
        'quotation_qty_unit',
        'order_qty_per_quotation_unit',
        'quotation_specs',
        'quotation_approved_date',
        'quotation_status',
        'quotation_is_valid',
        'remarks',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
