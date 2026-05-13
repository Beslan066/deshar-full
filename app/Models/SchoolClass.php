<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'school_id',
        'teacher_id',
        'fon',
        'complexity',
    ];
}
