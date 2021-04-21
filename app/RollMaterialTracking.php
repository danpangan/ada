<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class RollMaterialTracking extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'roll_material_tracking';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'material_id', 
        'material_tracking_id', 
        'roll_length',
        'roll_length_unit',
        'stock_roll_quantity',
        'employee_id',
        'created_at',
        'updated_at',
    ]; 

    protected $dates = ['deleted_at'];
}
