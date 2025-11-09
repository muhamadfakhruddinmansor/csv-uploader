<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'original_name','stored_path','status','rows_processed','error'
    ];
}
