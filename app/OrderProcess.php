<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderProcess extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'order_processes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'order_process_description',
        'order_process_id',
        'order_process_cuts',
        'order_process_folds',
        'order_process_outs',
        'order_process_paper_size',
        'order_process_signature',
        'order_process_gsm',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}