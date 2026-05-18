<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClassType extends Model
{
    protected $table = 'school_class_types';

    protected $fillable = [
        'name',
    ];
}
