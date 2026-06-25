<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserModuleProgress extends Model
{
    use HasFactory;

    protected $table = 'user_module_progress';

    protected $fillable = [
        'user_id',
        'module_id',
        'status',
        'progress_percentage',
        'started_at',
        'completed_at',
        'last_activity_at',
        'time_spent_seconds',
        'metadata',
    ];

    protected $casts = [
        'progress_percentage' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'metadata' => 'array',
    ];

    // Константы статусов
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // 🔗 Связи
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(EducationModule::class, 'module_id');
    }

    // 📊 Скоупы
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // 🎯 Аксессоры
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getProgressFormattedAttribute(): string
    {
        return round($this->progress_percentage, 1) . '%';
    }

    public function getTotalPiecesAttribute(): int
    {
        return $this->module?->pieces()->count() ?? 0;
    }

    public function getCompletedPiecesAttribute(): int
    {
        if (!$this->module) {
            return 0;
        }

        return $this->module->pieces()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserPieceProgress::STATUS_COMPLETED);
            })
            ->count();
    }

    // 🔧 Методы
    public function updateProgress(): void
    {
        $module = $this->module;
        if (!$module) {
            return;
        }

        $totalPieces = $module->pieces()->count();

        if ($totalPieces === 0) {
            $this->progress_percentage = 100;
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
            $this->save();
            return;
        }

        $completedPieces = $module->pieces()
            ->whereHas('userProgress', function ($query) {
                $query->where('user_id', $this->user_id)
                    ->where('status', UserPieceProgress::STATUS_COMPLETED);
            })
            ->count();

        $this->progress_percentage = round(($completedPieces / $totalPieces) * 100, 2);

        if ($this->progress_percentage >= 100) {
            $this->status = self::STATUS_COMPLETED;
            $this->completed_at = now();
        } elseif ($this->progress_percentage > 0) {
            $this->status = self::STATUS_IN_PROGRESS;
            if (!$this->started_at) {
                $this->started_at = now();
            }
        }

        $this->last_activity_at = now();
        $this->save();
    }

    public function toApiArray(): array
    {
        return [
            'status' => $this->status,
            'progress' => $this->progress_percentage,
            'progress_formatted' => $this->progress_formatted,
            'completed_pieces' => $this->completed_pieces,
            'total_pieces' => $this->total_pieces,
            'is_completed' => $this->is_completed,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
}
