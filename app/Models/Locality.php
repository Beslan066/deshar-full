<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locality extends Model
{
    protected $table = 'localities';

    protected $fillable = [
        'name',
        'district_id',
    ];

    public function district() {
        return $this->belongsTo(District::class);
    }
}
