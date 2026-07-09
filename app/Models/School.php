<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'schools';

    protected $fillable = [
        'name',
        'country_id',
        'city_id',
        'region_id',
        'district_id',
        'locality_id',
        'director_id',
        'manager_id',
    ];

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function city() {
        return $this->belongsTo(City::class);
    }

    public function region() {
        return $this->belongsTo(Region::class);
    }

    public function district() {
        return $this->belongsTo(District::class);
    }

    public function locality() {
        return $this->belongsTo(Locality::class);
    }

    public function director() {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function manager() {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role_id', 9);
    }


    public function teachers() {
        return $this->hasMany(User::class)->where('role_id', 8);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }
}
