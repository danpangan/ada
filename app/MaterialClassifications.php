<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialClassifications extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'material_classifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_code_id', 
        'material_classification_name', 
        'employee_id',
        'created_at',
    ]; 

    protected $dates = ['deleted_at'];
}
