<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComputationSheetMiscOthers extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'computation_sheet_misc_others';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cs_id', 
        'cs_misc_id',
        'other_misc_description',
        'other_misc_quantity',
        'other_misc_price',
        'other_misc_total',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
