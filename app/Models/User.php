<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'country_id',
        'region_id',
        'district_id',
        'city_id',
        'school_id',
        'school_class_id',
        'role_id',
        'points',
        'birth_date',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'datetime',
            'password' => 'hashed',
            'points' => 'integer',
        ];
    }

    /**
     * Get the country that owns the user.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the region that owns the user.
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the district that owns the user.
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the city that owns the user.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the school that owns the user.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the school class that owns the user.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

