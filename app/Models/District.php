<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';

    protected $fillable = [
        'name',
        'region_id',
        'manager_id'
    ];

    public function region() {
        return $this->belongsTo(Region::class);
    }

    public function manager() {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function schools() {
        return $this->hasMany(School::class);
    }
}
