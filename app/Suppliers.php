<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suppliers extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'suppliers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'supplier_code', 
        'supplier_name', 
        'supplier_short_name', 
        'supplier_province_id',
        'supplier_municipality_id',
        'supplier_address',
        'supplier_email',
        'supplier_contact',
        'supplier_fax',
        'transaction_currency',
        'supplier_materials',
        'supplier_category',
        'supplier_classification_id',
        'employee_id',
        'created_at',
    ]; 

    protected $dates = ['deleted_at'];
}
