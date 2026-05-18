<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationModule extends Model
{
    protected $table = 'education_modules';

    protected $fillable = [
      'name',
      'image',
      'complexity',
      'school_class_type_id'

];

    public function schoolClassType() {
        return $this->belongsTo(SchoolClassType::class, 'school_class_type_id');
    }
}
