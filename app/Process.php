<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Process extends Model
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'process';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

        'id', 
        'name', 
        'description', 
        'bundle', 
        'source_table', 
        'updated_at',
        
    ];
        
}