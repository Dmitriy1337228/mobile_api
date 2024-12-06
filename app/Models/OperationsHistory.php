<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationsHistory extends Model
{
    public $timestamps = false;
    protected $table = 'OperationsHistory';
    protected $guarded = [];
}
