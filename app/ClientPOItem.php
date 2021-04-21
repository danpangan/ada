<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPOItem extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_po_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_po_number', 
        'client_po_id',
        'quotation_id',
        'cs_id',
        'order_id',
        'cs_control_number',
        'client_po_item_qty',
        'client_po_item_qty_unit',
        'client_po_item_qty_remaining',
        'client_po_item_unit_amount', 
        'client_po_item_total_amount',
        'client_po_item_currency',
        'client_po_item_unit_amount_php',
        'client_po_item_status',
        'employee_id',
        'created_at',
        'updated_at',
        
    ]; 

    protected $dates = ['deleted_at'];
}


