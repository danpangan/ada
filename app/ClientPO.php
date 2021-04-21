<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPO extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_po';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_po_number', 
        'client_id',
        'client_name',
        'client_address',
        'client_pic',
        'client_po_date',
        'client_po_received_date',
        'client_po_amount',
        'client_po_amount_currency', 
        'client_po_amount_php',
        'client_po_status',
        'employee_id',
        'created_at',
        'updated_at',
        
    ]; 

    protected $dates = ['deleted_at'];
}

