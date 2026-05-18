<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'school_id',
        'teacher_id',
        'school_class_type_id',
    ];

    public function school() {
        return $this->belongsTo(School::class);
    }

    public function teacher() {

        return $this->belongsTo(User::class, 'teacher_id');
    }
}
