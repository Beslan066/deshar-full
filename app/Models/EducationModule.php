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
        'school_class_type_id',
        'slug',
        'description',
        'is_published',
        'sort_order',
        'metadata',
        'total_xp_reward',
    ];

    protected $casts = [
        'complexity' => 'integer',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
        'total_xp_reward' => 'integer',
        'metadata' => 'array',
    ];

    // 🔗 Связи
    public function schoolClassType()
    {
        return $this->belongsTo(SchoolClassType::class);
    }

    public function pieces()
    {
        return $this->hasMany(EducationModulePiece::class)->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, EducationModulePiece::class);
    }

    public function tasks()
    {
        return $this->hasManyThrough(Task::class, EducationModulePiece::class, 'education_module_id', 'piece_id');
    }

    // 📊 Скоупы
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // 🎯 Аксессоры
    public function getTotalPiecesAttribute(): int
    {
        return $this->pieces()->count();
    }

    public function getTotalLessonsAttribute(): int
    {
        return $this->lessons()->count();
    }

    public function getTotalTasksAttribute(): int
    {
        return $this->tasks()->count();
    }
}
