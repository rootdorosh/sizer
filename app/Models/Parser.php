<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parser extends Model
{
    public $timestamps = false;
    
    /**
     * table name.
     *
     * @var string
     */
    protected $table = 'parser';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'request',
        'response',
    ];
    
}
