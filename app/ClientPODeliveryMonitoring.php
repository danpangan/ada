<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPODeliveryMonitoring extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_po_delivery_monitoring';

    protected $fillable = [
        'po_id', 
        'invoice_id',
        'receipt_id',
        'order_id',
        'delivery_qty',
        'quantity_unit',
        'unit_cost',
        'total_cost',
        'del_balance',
        'del_remarks',
        'del_qty',
        'delivered_at',
        'ref_number',
        'unit_cost',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
