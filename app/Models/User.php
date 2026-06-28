<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'level',
        'current_streak',
        'max_streak',
        'last_activity_at',
        'last_login_at',
        'total_tasks_completed',
        'total_lessons_completed',
        'total_pieces_completed',
        'total_modules_completed',
        'preferences',
        'settings',
        'is_active',
        'is_banned',
        'banned_until',
        'ban_reason',
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
            'level' => 'integer',
            'current_streak' => 'integer',
            'max_streak' => 'integer',
            'last_activity_at' => 'datetime',
            'last_login_at' => 'datetime',
            'total_tasks_completed' => 'integer',
            'total_lessons_completed' => 'integer',
            'total_pieces_completed' => 'integer',
            'total_modules_completed' => 'integer',
            'preferences' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_banned' => 'boolean',
            'banned_until' => 'datetime',
        ];
    }

    // ============================================================
    // 🔗 СВЯЗИ (Relationships)
    // ============================================================

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

    /**
     * Связь с прогрессом заданий
     */
    public function taskProgress()
    {
        return $this->hasMany(UserTaskProgress::class);
    }

    /**
     * Связь с прогрессом уроков
     */
    public function lessonProgress()
    {
        return $this->hasMany(UserLessonProgress::class);
    }

    /**
     * Связь с прогрессом разделов
     */
    public function pieceProgress()
    {
        return $this->hasMany(UserPieceProgress::class);
    }

    /**
     * Связь с прогрессом модулей
     */
    public function moduleProgress()
    {
        return $this->hasMany(UserModuleProgress::class);
    }

    /**
     * Связь с завершенными заданиями
     */
    public function completedTasks()
    {
        return $this->hasMany(UserTaskProgress::class)
            ->where('status', UserTaskProgress::STATUS_COMPLETED);
    }

    /**
     * Связь с завершенными уроками
     */
    public function completedLessons()
    {
        return $this->hasMany(UserLessonProgress::class)
            ->where('status', UserLessonProgress::STATUS_COMPLETED);
    }

    /**
     * Связь с завершенными разделами
     */
    public function completedPieces()
    {
        return $this->hasMany(UserPieceProgress::class)
            ->where('status', UserPieceProgress::STATUS_COMPLETED);
    }

    /**
     * Связь с завершенными модулями
     */
    public function completedModules()
    {
        return $this->hasMany(UserModuleProgress::class)
            ->where('status', UserModuleProgress::STATUS_COMPLETED);
    }

    // ============================================================
    // 📊 СКОУПЫ (Scopes)
    // ============================================================

    /**
     * Только активные пользователи
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('is_banned', false);
    }

    /**
     * Только активные за последние 7 дней
     */
    public function scopeRecentlyActive($query)
    {
        return $query->where('last_activity_at', '>=', now()->subDays(7));
    }

    /**
     * Только онлайн (активность в последние 5 минут)
     */
    public function scopeOnline($query)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes(5));
    }

    /**
     * Только с определенной ролью
     */
    public function scopeWithRole($query, string $roleSlug)
    {
        return $query->whereHas('role', function ($q) use ($roleSlug) {
            $q->where('slug', $roleSlug);
        });
    }

    /**
     * Только ученики
     */
    public function scopeStudents($query)
    {
        return $query->where('user_type', 'student');
    }

    /**
     * Только учителя
     */
    public function scopeTeachers($query)
    {
        return $query->where('user_type', 'teacher');
    }

    /**
     * Только администраторы
     */
    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    /**
     * Только с определенным классом
     */
    public function scopeInClass($query, int $schoolClassId)
    {
        return $query->where('school_class_id', $schoolClassId);
    }

    /**
     * Только с определенной школой
     */
    public function scopeInSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Поиск по имени или email
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%");
    }

    /**
     * Сортировка по уровню (от высокого к низкому)
     */
    public function scopeByLevelDesc($query)
    {
        return $query->orderBy('level', 'desc');
    }

    /**
     * Сортировка по XP
     */
    public function scopeByXpDesc($query)
    {
        return $query->orderBy('points', 'desc');
    }

    /**
     * Сортировка по стрику
     */
    public function scopeByStreakDesc($query)
    {
        return $query->orderBy('current_streak', 'desc');
    }

    // ============================================================
    // 🎯 АКСЕССОРЫ (Accessors)
    // ============================================================

    /**
     * Получить аватар с дефолтным значением
     */
    public function getAvatarAttribute($value)
    {
        return $value ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }

    /**
     * Получить прогресс уровня (0-100)
     */
    public function getLevelProgressAttribute(): float
    {
        $xpForLevel = $this->getXpForLevel($this->level ?? 0);
        $xpForNextLevel = $this->getXpForLevel(($this->level ?? 0) + 1);

        if ($xpForNextLevel === $xpForLevel) {
            return 100;
        }

        return (($this->points - $xpForLevel) / ($xpForNextLevel - $xpForLevel)) * 100;
    }

    /**
     * Получить общее количество XP до следующего уровня
     */
    public function getXpToNextLevelAttribute(): int
    {
        $xpForNextLevel = $this->getXpForLevel(($this->level ?? 0) + 1);
        return max(0, $xpForNextLevel - $this->points);
    }

    /**
     * Получить общее количество XP в текущем уровне
     */
    public function getXpInCurrentLevelAttribute(): int
    {
        $xpForLevel = $this->getXpForLevel($this->level ?? 0);
        return $this->points - $xpForLevel;
    }

    /**
     * Получить название уровня
     */
    public function getLevelNameAttribute(): string
    {
        $levels = [
            0 => 'Новичок',
            1 => 'Ученик',
            2 => 'Знаток',
            3 => 'Мастер',
            4 => 'Эксперт',
            5 => 'Гуру',
            6 => 'Профессор',
            7 => 'Мудрец',
            8 => 'Легенда',
            9 => 'Мифический',
            10 => 'Бессмертный',
        ];

        return $levels[$this->level ?? 0] ?? 'Мастер';
    }

    /**
     * Получить цвет уровня
     */
    public function getLevelColorAttribute(): string
    {
        $colors = [
            0 => '#9E9E9E', // серый
            1 => '#4CAF50', // зеленый
            2 => '#8BC34A', // светло-зеленый
            3 => '#FFEB3B', // желтый
            4 => '#FF9800', // оранжевый
            5 => '#FF5722', // оранжево-красный
            6 => '#E91E63', // розовый
            7 => '#9C27B0', // фиолетовый
            8 => '#3F51B5', // синий
            9 => '#2196F3', // голубой
            10 => '#00BCD4', // бирюзовый
        ];

        return $colors[$this->level ?? 0] ?? '#9E9E9E';
    }

    /**
     * Проверить, забанен ли пользователь
     */
    public function getIsBannedAttribute(): bool
    {
        if (!$this->attributes['is_banned'] ?? false) {
            return false;
        }

        if ($this->banned_until && $this->banned_until->isPast()) {
            // Бан закончился
            $this->update(['is_banned' => false, 'banned_until' => null]);
            return false;
        }

        return true;
    }

    /**
     * Проверить, онлайн ли пользователь
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->last_activity_at && $this->last_activity_at->diffInMinutes(now()) < 5;
    }

    /**
     * Получить время последней активности в удобном формате
     */
    public function getLastActivityFormattedAttribute(): string
    {
        if (!$this->last_activity_at) {
            return 'Никогда';
        }

        return $this->last_activity_at->diffForHumans();
    }

    // ============================================================
    // 🔧 МЕТОДЫ РАБОТЫ С XP И УРОВНЯМИ
    // ============================================================

    /**
     * Получить XP для уровня
     */
    public function getXpForLevel(int $level): int
    {
        // Формула: 100 * level^2 (экспоненциальный рост)
        // 0: 0, 1: 100, 2: 400, 3: 900, 4: 1600, 5: 2500
        return 100 * pow($level, 2);
    }

    /**
     * Добавить XP пользователю
     */
    public function addXp(int $amount, ?string $source = null): void
    {
        $oldLevel = $this->level ?? 0;
        $this->increment('points', $amount);

        // Проверяем, не повысился ли уровень
        $newLevel = $this->calculateLevel();
        if ($newLevel > $oldLevel) {
            $this->level = $newLevel;
            $this->save();

            // Вызываем событие повышения уровня
            $this->fireLevelUpEvent($oldLevel, $newLevel);
        } else {
            $this->save();
        }

        // Логируем получение XP (опционально)
        if ($source) {
            $this->logXp($amount, $source);
        }
    }

    /**
     * Рассчитать уровень на основе XP
     */
    public function calculateLevel(): int
    {
        $level = 0;
        while ($this->points >= $this->getXpForLevel($level + 1)) {
            $level++;
        }
        return $level;
    }

    /**
     * Проверить, есть ли XP для повышения уровня
     */
    public function hasEnoughXpForNextLevel(): bool
    {
        $xpForNextLevel = $this->getXpForLevel(($this->level ?? 0) + 1);
        return $this->points >= $xpForNextLevel;
    }

    /**
     * Получить прогресс до следующего уровня в процентах
     */
    public function getProgressToNextLevel(): float
    {
        $currentLevelXp = $this->getXpForLevel($this->level ?? 0);
        $nextLevelXp = $this->getXpForLevel(($this->level ?? 0) + 1);
        $xpInLevel = $this->points - $currentLevelXp;
        $xpNeeded = $nextLevelXp - $currentLevelXp;

        if ($xpNeeded <= 0) {
            return 100;
        }

        return min(100, ($xpInLevel / $xpNeeded) * 100);
    }

    /**
     * Логировать получение XP
     */
    private function logXp(int $amount, string $source): void
    {
        // Можно создать таблицу user_xp_logs
        // Или использовать activity log
        // Для простоты пока оставим
    }

    /**
     * Вызвать событие повышения уровня
     */
    private function fireLevelUpEvent(int $oldLevel, int $newLevel): void
    {
        // Можно использовать Laravel Events
        // event(new UserLevelUp($this, $oldLevel, $newLevel));
    }

    // ============================================================
    // 🔧 МЕТОДЫ РАБОТЫ СО СТРИКАМИ
    // ============================================================

    /**
     * Обновить стрик пользователя
     */
    public function updateStreak(): void
    {
        $today = now()->toDateString();
        $lastActivity = $this->last_activity_at?->toDateString();

        if ($lastActivity === $today) {
            return; // Уже обновлено сегодня
        }

        $yesterday = now()->subDay()->toDateString();

        if ($lastActivity === $yesterday) {
            // Продолжаем серию
            $this->increment('current_streak');
            if (($this->current_streak ?? 0) > ($this->max_streak ?? 0)) {
                $this->max_streak = $this->current_streak;
            }

            // Бонус за стрик
            $this->handleStreakBonus();
        } elseif ($lastActivity !== $today) {
            // Серия прервана (если последняя активность не сегодня и не вчера)
            $this->current_streak = 0;
        }

        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Обработать бонус за стрик
     */
    private function handleStreakBonus(): void
    {
        $streak = $this->current_streak ?? 0;

        // Бонус каждые 7 дней
        if ($streak % 7 === 0 && $streak > 0) {
            $bonusXp = 50 * ($streak / 7);
            $this->addXp((int)$bonusXp, 'streak_bonus');
        }

        // Ежедневный бонус за стрик
        if ($streak > 0) {
            $dailyBonus = min(10, $streak); // до 10 XP в день
            $this->addXp($dailyBonus, 'daily_streak');
        }
    }

    /**
     * Сбросить стрик
     */
    public function resetStreak(): void
    {
        $this->current_streak = 0;
        $this->save();
    }

    // ============================================================
    // 🔧 МЕТОДЫ РАБОТЫ С ПРОГРЕССОМ
    // ============================================================

    /**
     * Получить общую статистику пользователя
     */
    public function getStats(): array
    {
        $totalTasks = Task::count();
        $totalLessons = Lesson::count();
        $totalPieces = EducationModulePiece::count();
        $totalModules = EducationModule::count();

        $completedTasks = $this->completedTasks()->count();
        $completedLessons = $this->completedLessons()->count();
        $completedPieces = $this->completedPieces()->count();
        $completedModules = $this->completedModules()->count();

        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'avatar' => $this->avatar,
                'level' => $this->level ?? 0,
                'level_name' => $this->level_name,
                'level_color' => $this->level_color,
                'level_progress' => $this->level_progress,
                'xp' => $this->points,
                'xp_to_next_level' => $this->xp_to_next_level,
                'current_streak' => $this->current_streak ?? 0,
                'max_streak' => $this->max_streak ?? 0,
                'is_online' => $this->is_online,
                'last_activity' => $this->last_activity_formatted,
            ],
            'progress' => [
                'tasks' => [
                    'completed' => $completedTasks,
                    'total' => $totalTasks,
                    'percentage' => $totalTasks > 0
                        ? round(($completedTasks / $totalTasks) * 100, 2)
                        : 0,
                ],
                'lessons' => [
                    'completed' => $completedLessons,
                    'total' => $totalLessons,
                    'percentage' => $totalLessons > 0
                        ? round(($completedLessons / $totalLessons) * 100, 2)
                        : 0,
                ],
                'pieces' => [
                    'completed' => $completedPieces,
                    'total' => $totalPieces,
                    'percentage' => $totalPieces > 0
                        ? round(($completedPieces / $totalPieces) * 100, 2)
                        : 0,
                ],
                'modules' => [
                    'completed' => $completedModules,
                    'total' => $totalModules,
                    'percentage' => $totalModules > 0
                        ? round(($completedModules / $totalModules) * 100, 2)
                        : 0,
                ],
            ],
        ];
    }

    /**
     * Получить прогресс по всем модулям
     */
    public function getModulesProgress(): array
    {
        $modules = EducationModule::with('pieces.lessons')->get();
        $result = [];

        foreach ($modules as $module) {
            $progress = $this->moduleProgress()
                ->where('module_id', $module->id)
                ->first();

            $result[] = [
                'module' => $module->only(['id', 'name', 'image', 'complexity']),
                'progress' => $progress ? [
                    'status' => $progress->status,
                    'percentage' => $progress->progress_percentage,
                    'formatted' => $progress->progress_formatted,
                    'is_completed' => $progress->is_completed,
                ] : [
                    'status' => 'not_started',
                    'percentage' => 0,
                    'formatted' => '0%',
                    'is_completed' => false,
                ],
            ];
        }

        return $result;
    }

    /**
     * Получить прогресс по определенному модулю
     */
    public function getModuleProgress(int $moduleId): ?array
    {
        $module = EducationModule::with('pieces.lessons')->find($moduleId);
        if (!$module) {
            return null;
        }

        $moduleProgress = $this->moduleProgress()
            ->where('module_id', $moduleId)
            ->first();

        $pieces = [];
        foreach ($module->pieces as $piece) {
            $pieceProgress = $this->pieceProgress()
                ->where('piece_id', $piece->id)
                ->first();

            $lessons = [];
            foreach ($piece->lessons as $lesson) {
                $lessonProgress = $this->lessonProgress()
                    ->where('lesson_id', $lesson->id)
                    ->first();

                $lessons[] = [
                    'lesson' => $lesson->only(['id', 'name']),
                    'progress' => $lessonProgress ? [
                        'status' => $lessonProgress->status,
                        'percentage' => $lessonProgress->progress_percentage,
                        'is_completed' => $lessonProgress->is_completed,
                    ] : [
                        'status' => 'not_started',
                        'percentage' => 0,
                        'is_completed' => false,
                    ],
                ];
            }

            $pieces[] = [
                'piece' => $piece->only(['id', 'name', 'fon']),
                'progress' => $pieceProgress ? [
                    'status' => $pieceProgress->status,
                    'percentage' => $pieceProgress->progress_percentage,
                    'is_completed' => $pieceProgress->is_completed,
                ] : [
                    'status' => 'not_started',
                    'percentage' => 0,
                    'is_completed' => false,
                ],
                'lessons' => $lessons,
            ];
        }

        return [
            'module' => $module->only(['id', 'name', 'image', 'complexity']),
            'progress' => $moduleProgress ? [
                'status' => $moduleProgress->status,
                'percentage' => $moduleProgress->progress_percentage,
                'is_completed' => $moduleProgress->is_completed,
            ] : [
                'status' => 'not_started',
                'percentage' => 0,
                'is_completed' => false,
            ],
            'pieces' => $pieces,
        ];
    }

    // ============================================================
    // 🔧 МЕТОДЫ ДЛЯ API И АДМИНКИ
    // ============================================================

    /**
     * Преобразовать в массив для API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'level' => $this->level ?? 0,
            'level_name' => $this->level_name,
            'level_color' => $this->level_color,
            'level_progress' => $this->level_progress,
            'xp' => $this->points,
            'xp_to_next_level' => $this->xp_to_next_level,
            'current_streak' => $this->current_streak ?? 0,
            'max_streak' => $this->max_streak ?? 0,
            'is_online' => $this->is_online,
            'last_activity' => $this->last_activity_formatted,
            'user_type' => $this->user_type,
            'role' => $this->role?->only(['id', 'name', 'slug']),
            'school' => $this->school?->only(['id', 'name']),
            'school_class' => $this->schoolClass?->only(['id', 'name', 'grade', 'letter']),
            'location' => [
                'country' => $this->country?->name,
                'region' => $this->region?->name,
                'city' => $this->city?->name,
            ],
        ];
    }

    /**
     * Получить данные для профиля
     */
    public function getProfileData(): array
    {
        return [
            'user' => $this->toApiArray(),
            'stats' => $this->getStats(),
            'recent_achievements' => $this->getRecentAchievements(),
            'recommended_modules' => $this->getRecommendedModules(),
        ];
    }

    // ============================================================
    // 🔧 МЕТОДЫ ДОСТИЖЕНИЙ
    // ============================================================

    /**
     * Получить последние достижения
     */
    public function getRecentAchievements(int $limit = 5): array
    {
        // Можно создать таблицу user_achievements
        // Или вычислять на лету
        $achievements = [];

        // Примеры достижений
        if ($this->completedTasks()->count() >= 10) {
            $achievements[] = [
                'name' => 'Первые шаги',
                'description' => 'Выполнено 10 заданий',
                'icon' => '🎯',
                'earned_at' => now(),
            ];
        }

        if ($this->completedTasks()->count() >= 100) {
            $achievements[] = [
                'name' => 'Трудоголик',
                'description' => 'Выполнено 100 заданий',
                'icon' => '💪',
                'earned_at' => now(),
            ];
        }

        if (($this->current_streak ?? 0) >= 7) {
            $achievements[] = [
                'name' => 'Недельный стрик',
                'description' => '7 дней подряд',
                'icon' => '🔥',
                'earned_at' => now(),
            ];
        }

        if (($this->level ?? 0) >= 5) {
            $achievements[] = [
                'name' => 'Мастер',
                'description' => 'Достигнут 5 уровень',
                'icon' => '⭐',
                'earned_at' => now(),
            ];
        }

        return array_slice($achievements, 0, $limit);
    }

    /**
     * Получить рекомендованные модули
     */
    public function getRecommendedModules(int $limit = 3): array
    {
        // На основе прогресса пользователя
        $completedModuleIds = $this->moduleProgress()
            ->where('status', UserModuleProgress::STATUS_COMPLETED)
            ->pluck('module_id')
            ->toArray();

        $recommended = EducationModule::whereNotIn('id', $completedModuleIds)
            ->orderBy('complexity')
            ->limit($limit)
            ->get();

        return $recommended->map(function ($module) {
            return [
                'id' => $module->id,
                'name' => $module->name,
                'image' => $module->image,
                'complexity' => $module->complexity,
                'total_lessons' => $module->lessons()->count(),
            ];
        })->toArray();
    }

    // ============================================================
    // 🔧 МЕТОДЫ АДМИНИСТРИРОВАНИЯ
    // ============================================================

    /**
     * Забанить пользователя
     */
    public function ban(?string $reason = null, ?int $days = null): void
    {
        $this->is_banned = true;
        $this->ban_reason = $reason;

        if ($days) {
            $this->banned_until = now()->addDays($days);
        }

        $this->save();
    }

    /**
     * Разбанить пользователя
     */
    public function unban(): void
    {
        $this->is_banned = false;
        $this->banned_until = null;
        $this->ban_reason = null;
        $this->save();
    }

    /**
     * Проверить, может ли пользователь выполнять задания
     */
    public function canAccessContent(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->is_banned) {
            return false;
        }

        return true;
    }

    /**
     * Проверить роль пользователя
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->role?->slug === $roleSlug;
    }

    /**
     * Проверить, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin' || $this->hasRole('admin');
    }

    /**
     * Проверить, является ли пользователь учителем
     */
    public function isTeacher(): bool
    {
        return $this->user_type === 'teacher' || $this->hasRole('teacher');
    }

    /**
     * Проверить, является ли пользователь учеником
     */
    public function isStudent(): bool
    {
        return $this->user_type === 'student' || $this->hasRole('student');
    }

    // ============================================================
    // 🔧 ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ
    // ============================================================

    /**
     * Обновить последнюю активность
     */
    public function updateActivity(): void
    {
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Записать вход пользователя
     */
    public function recordLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * Получить рейтинг пользователя
     */
    public function getRank(): int
    {
        return Cache::remember("user_rank_{$this->id}", 3600, function () {
            return User::where('points', '>', $this->points)->count() + 1;
        });
    }

    /**
     * Получить общее количество XP
     */
    public function getTotalXp(): int
    {
        return $this->points;
    }

    /**
     * Получить настройки пользователя
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Установить настройку пользователя
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Получить предпочтения пользователя
     */
    public function getPreference(string $key, $default = null)
    {
        return $this->preferences[$key] ?? $default;
    }

    /**
     * Установить предпочтение пользователя
     */
    public function setPreference(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        $preferences[$key] = $value;
        $this->preferences = $preferences;
        $this->save();
    }

    // ============================================================
    // 🔄 BOOT / EVENTS
    // ============================================================

    protected static function boot()
    {
        parent::boot();

        // При создании пользователя
        static::creating(function ($user) {
            if (empty($user->level)) {
                $user->level = 0;
            }
            if (empty($user->points)) {
                $user->points = 0;
            }
            if (empty($user->current_streak)) {
                $user->current_streak = 0;
            }
            if (empty($user->max_streak)) {
                $user->max_streak = 0;
            }
            if (empty($user->is_active)) {
                $user->is_active = true;
            }
            if (empty($user->user_type)) {
                $user->user_type = 'student';
            }
        });

        // При сохранении
        static::saving(function ($user) {
            // Автоматически обновляем уровень
            if ($user->isDirty('points')) {
                $newLevel = $user->calculateLevel();
                if ($newLevel > ($user->level ?? 0)) {
                    $user->level = $newLevel;
                }
            }
        });
    }
}
