<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationModulePiece extends Model
{
    protected $table = 'education_module_pieces';

    protected $fillable = [
        'name',
        'fon',
        'education_module_id',
    ];

    public function educationModule() {
        return $this->belongsTo(EducationModule::class);
    }
}
