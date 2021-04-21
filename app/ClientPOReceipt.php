<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPOReceipt extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_po_receipt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'dr_number',
        'dr_sold',
        'dr_attention_company',
        'dr_attention_pic',
        'dr_address',
        'dr_status',
        'dr_is_printed',
        'employee_id',
        'created_at',
        'updated_at',
        
    ]; 

    protected $dates = ['deleted_at'];
}