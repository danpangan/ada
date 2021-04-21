<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientPOInvoice extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'client_po_invoice';

    protected $fillable = [
        'client_id', 
        'si_number',
        'si_sold',
        'si_attention',
        'si_address',
        'si_status',
        'si_is_printed',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
