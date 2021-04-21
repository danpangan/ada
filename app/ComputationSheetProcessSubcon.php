<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetProcessSubcon extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_processes_subcon';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
       'cs_id',
       'cs_order_process_subcon_id',
       'cs_process_subcon_qty',
       'cs_process_subcon_unit_amount',
       'cs_process_subcon_total_amount',
       'employee_id',
       'created_at',
       'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
