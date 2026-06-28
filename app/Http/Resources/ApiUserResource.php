<?php
// app/Http/Resources/Api/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'user_type' => $this->user_type,

            // Прогресс и уровень
            'level' => $this->level ?? 0,
            'level_name' => $this->level_name,
            'level_color' => $this->level_color,
            'level_progress' => $this->level_progress,
            'xp' => $this->points ?? 0,
            'xp_to_next_level' => $this->xp_to_next_level,

            // Стрики
            'current_streak' => $this->current_streak ?? 0,
            'max_streak' => $this->max_streak ?? 0,

            // Статус
            'is_online' => $this->is_online,
            'is_active' => $this->is_active,
            'is_banned' => $this->is_banned,
            'last_activity' => $this->last_activity_formatted,
            'last_login_at' => $this->last_login_at?->toISOString(),

            // Связи
            'role' => $this->whenLoaded('role', function () {
                return [
                    'id' => $this->role->id,
                    'name' => $this->role->name,
                    'slug' => $this->role->slug,
                ];
            }),

            'school' => $this->whenLoaded('school', function () {
                return [
                    'id' => $this->school->id,
                    'name' => $this->school->name,
                ];
            }),

            'school_class' => $this->whenLoaded('schoolClass', function () {
                return [
                    'id' => $this->schoolClass->id,
                    'name' => $this->schoolClass->name,
                    'grade' => $this->schoolClass->grade,
                    'letter' => $this->schoolClass->letter,
                ];
            }),

            'location' => [
                'country' => $this->whenLoaded('country', fn() => $this->country->name),
                'region' => $this->whenLoaded('region', fn() => $this->region->name),
                'district' => $this->whenLoaded('district', fn() => $this->district->name),
                'city' => $this->whenLoaded('city', fn() => $this->city->name),
            ],

            // Статистика (если загружена)
            'stats' => $this->when($this->relationLoaded('taskProgress'), function () {
                return [
                    'total_tasks_completed' => $this->total_tasks_completed ?? 0,
                    'total_lessons_completed' => $this->total_lessons_completed ?? 0,
                    'total_pieces_completed' => $this->total_pieces_completed ?? 0,
                    'total_modules_completed' => $this->total_modules_completed ?? 0,
                ];
            }),

            // Настройки и предпочтения (только для авторизованного пользователя)
            'preferences' => $this->when($this->isOwner(), function () {
                return $this->preferences;
            }),
            'settings' => $this->when($this->isOwner(), function () {
                return $this->settings;
            }),

            // Даты
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Проверяем, является ли текущий пользователь владельцем
     */
    protected function isOwner(): bool
    {
        return request()->user() && request()->user()->id === $this->id;
    }
}
