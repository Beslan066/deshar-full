<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'school_classes';

    protected $fillable = [
        'name',
        'school_id',
        'teacher_id',
        'school_class_type_id',
    ];

    // Связи
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'school_class_id')
            ->where('user_type', 'student');
    }

    public function schoolClassType(): BelongsTo
    {
        return $this->belongsTo(SchoolClassType::class, 'school_class_type_id');
    }

    // Скоупы
    public function scopeWithStudentsCount($query)
    {
        return $query->withCount(['students' => function ($query) {
            $query->where('user_type', 'student');
        }]);
    }

    // Аксессоры
    public function getStudentsCountAttribute(): int
    {
        return $this->students()->count();
    }
}
