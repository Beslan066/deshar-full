<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\TaskConfigValidator;

class Task extends Model
{

    protected $table = 'tasks';

    protected $fillable = [
        'lesson_id',
        'task_type_id',
        'sort_order',
        'title',
        'description',
        'config',
        'max_attempts',
        'time_limit_seconds',
        'xp_reward',
        'hints',
        'is_published',
        'is_required',
        'metadata',
    ];

    protected $casts = [
        'config' => 'array',
        'hints' => 'array',
        'metadata' => 'array',
        'is_published' => 'boolean',
        'is_required' => 'boolean',
        'max_attempts' => 'integer',
        'time_limit_seconds' => 'integer',
        'xp_reward' => 'integer',
        'sort_order' => 'integer',
    ];

    // ============================================================
    // 🔗 СВЯЗИ (Relationships)
    // ============================================================

    /**
     * Связь с уроком
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function taskType()
    {
        return $this->belongsTo(TaskType::class);
    }

    public function userProgress()
    {
        return $this->hasMany(UserTaskProgress::class);
    }

    /**
     * Связь с разделом (через урок)
     */
    public function piece()
    {
        return $this->hasOneThrough(
            EducationModulePiece::class,
            Lesson::class,
            'id',           // ключ в Lessons
            'id',           // ключ в EducationModulePiece
            'lesson_id',    // локальный ключ в Task
            'piece_id'      // локальный ключ в Lessons
        );
    }

    /**
     * Связь с модулем (через урок и раздел)
     */
    public function module()
    {
        return $this->hasOneThrough(
            EducationModule::class,
            EducationModulePiece::class,
            'id',                    // ключ в EducationModulePiece
            'id',                    // ключ в EducationModule
            'lesson_id',             // локальный ключ в Task
            'education_module_id'    // локальный ключ в EducationModulePiece
        );
    }

    // ============================================================
    // 📊 СКОУПЫ (Scopes)
    // ============================================================

    /**
     * Только опубликованные задания
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Только обязательные задания
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Сортировка по порядку
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Фильтр по типу задания
     */
    public function scopeByType($query, string $typeSlug)
    {
        return $query->whereHas('taskType', function ($q) use ($typeSlug) {
            $q->where('slug', $typeSlug);
        });
    }

    /**
     * Фильтр по уроку
     */
    public function scopeInLesson($query, int $lessonId)
    {
        return $query->where('lesson_id', $lessonId);
    }

    /**
     * Только задания, доступные пользователю (не пройденные)
     */
    public function scopeAvailableForUser($query, int $userId)
    {
        return $query->whereDoesntHave('userProgress', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->whereIn('status', [
                    UserTaskProgress::STATUS_COMPLETED,
                    UserTaskProgress::STATUS_FAILED
                ]);
        });
    }

    /**
     * Только завершенные задания пользователем
     */
    public function scopeCompletedByUser($query, int $userId)
    {
        return $query->whereHas('userProgress', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('status', UserTaskProgress::STATUS_COMPLETED);
        });
    }

    // ============================================================
    // 🎯 АКСЕССОРЫ (Accessors)
    // ============================================================

    /**
     * Завершено ли задание текущим пользователем
     */
    public function getIsCompletedAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return $this->userProgress()
            ->where('user_id', auth()->id())
            ->where('status', UserTaskProgress::STATUS_COMPLETED)
            ->exists();
    }

    /**
     * Прогресс текущего пользователя по заданию
     */
    public function getUserProgressAttribute(): ?UserTaskProgress
    {
        if (!auth()->check()) {
            return null;
        }

        return $this->userProgress()
            ->where('user_id', auth()->id())
            ->first();
    }

    /**
     * Количество оставшихся попыток
     */
    public function getAttemptsLeftAttribute(): int
    {
        $progress = $this->user_progress;
        if (!$progress) {
            return $this->max_attempts ?? 3;
        }

        return max(0, ($this->max_attempts ?? 3) - $progress->attempts_count);
    }

    /**
     * Исчерпаны ли попытки
     */
    public function getIsAttemptsExhaustedAttribute(): bool
    {
        return $this->attempts_left === 0;
    }

    /**
     * Получить XP за задание (если не указано, берется из урока)
     */
    public function getXpRewardAttribute(): int
    {
        return $this->attributes['xp_reward'] ?? $this->lesson?->xp_reward ?? 10;
    }

    /**
     * Получить название типа задания
     */
    public function getTypeNameAttribute(): string
    {
        return $this->taskType?->name ?? 'Неизвестный тип';
    }

    /**
     * Получить slug типа задания
     */
    public function getTypeSlugAttribute(): string
    {
        return $this->taskType?->slug ?? 'unknown';
    }

    /**
     * Есть ли подсказки
     */
    public function getHasHintsAttribute(): bool
    {
        return !empty($this->hints);
    }

    /**
     * Количество подсказок
     */
    public function getHintsCountAttribute(): int
    {
        return count($this->hints ?? []);
    }

    /**
     * Получить статус задания (для текущего пользователя)
     */
    public function getStatusAttribute(): string
    {
        if (!auth()->check()) {
            return 'pending';
        }

        $progress = $this->user_progress;
        if (!$progress) {
            return 'pending';
        }

        return $progress->status;
    }

    // ============================================================
    // 🔧 МЕТОДЫ (Methods)
    // ============================================================

    /**
     * Получить правильный ответ для задания
     */
    public function getCorrectAnswer(): mixed
    {
        return match ($this->type_slug) {
            'choose_one' => $this->getCorrectOption(),
            'choose_three' => $this->getCorrectOptions(),
            'fill_word' => $this->config['correct'] ?? null,
            'match_pairs' => $this->config['pairs'] ?? [],
            'build_word' => $this->config['correct_word'] ?? null,
            'stress_mark' => $this->config['correct_index'] ?? null,
            'drag_drop_text' => $this->config['sentences'] ?? [],
            'story_order' => $this->config['parts'] ?? [],
            'fix_word' => $this->config['correct_form'] ?? null,
            'color_categories' => $this->config['items'] ?? [],
            'alphabet_letters' => $this->config['letters'] ?? [],
            'alphabet_images' => $this->config['items'] ?? [],
            'alphabet_words' => $this->config['words'] ?? [],
            'connect_letters' => $this->config['correct_order'] ?? [],
            'word_from_image' => $this->config['correct_word'] ?? null,
            'find_by_rule' => $this->config['words'] ?? [],
            'find_extra_letter' => $this->config['correct_index'] ?? null,
            'connect_category' => $this->config['items'] ?? [],
            'drag_to_image' => $this->config['pairs'] ?? [],
            'find_by_condition' => $this->config['condition']['correct_indices'] ?? [],
            'match_behavior' => $this->config['items'] ?? [],
            'build_dialogue' => $this->config['dialogues'] ?? [],
            default => null,
        };
    }

    /**
     * Получить правильный вариант (для choose_one)
     */
    private function getCorrectOption(): ?array
    {
        foreach ($this->config['options'] ?? [] as $option) {
            if ($option['is_correct'] ?? false) {
                return $option;
            }
        }
        return null;
    }

    /**
     * Получить правильные варианты (для choose_three)
     */
    private function getCorrectOptions(): array
    {
        $correct = [];
        foreach ($this->config['options'] ?? [] as $option) {
            if ($option['is_correct'] ?? false) {
                $correct[] = $option;
            }
        }
        return $correct;
    }

    /**
     * Получить подсказку по уровню
     */
    public function getHint(int $level = 0): ?string
    {
        $hints = $this->hints ?? [];
        return $hints[$level] ?? null;
    }

    /**
     * Получить все подсказки
     */
    public function getHints(): array
    {
        return $this->hints ?? [];
    }

    /**
     * Получить значение из конфига
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Установить значение в конфиг
     */
    public function setConfigValue(string $key, $value): void
    {
        $config = $this->config ?? [];
        $config[$key] = $value;
        $this->config = $config;
    }

    /**
     * Получить дефолтный конфиг для типа задания
     */
    public function getDefaultConfig(): array
    {
        if ($this->taskType && $this->taskType->default_config) {
            return $this->taskType->default_config;
        }

        // Дефолтные конфиги для каждого типа
        return match ($this->type_slug) {
            'choose_one' => [
                'question' => '',
                'options' => [
                    ['id' => 'a', 'text' => '', 'is_correct' => false],
                    ['id' => 'b', 'text' => '', 'is_correct' => false],
                    ['id' => 'c', 'text' => '', 'is_correct' => false],
                    ['id' => 'd', 'text' => '', 'is_correct' => false],
                ],
                'shuffle_options' => true,
                'explanation' => null,
            ],
            'choose_three' => [
                'question' => '',
                'options' => [
                    ['id' => 'a', 'text' => '', 'is_correct' => false],
                    ['id' => 'b', 'text' => '', 'is_correct' => false],
                    ['id' => 'c', 'text' => '', 'is_correct' => false],
                    ['id' => 'd', 'text' => '', 'is_correct' => false],
                    ['id' => 'e', 'text' => '', 'is_correct' => false],
                    ['id' => 'f', 'text' => '', 'is_correct' => false],
                ],
                'min_select' => 3,
                'max_select' => 3,
                'shuffle_options' => true,
            ],
            'match_images' => [
                'pairs' => [
                    ['text' => '', 'image' => '', 'correct_match' => ''],
                ],
                'shuffle_pairs' => true,
            ],
            'build_word' => [
                'image' => null,
                'correct_word' => '',
                'letters' => [],
                'extra_letters' => [],
                'hint' => null,
                'shuffle_letters' => true,
            ],
            'stress_mark' => [
                'word' => '',
                'letters' => [],
                'correct_index' => 0,
            ],
            'drag_drop_text' => [
                'sentences' => [],
                'words' => [],
                'extra_words' => [],
                'shuffle_words' => true,
            ],
            'story_order' => [
                'parts' => [],
                'shuffle_parts' => true,
                'show_numbers' => false,
            ],
            'fix_word' => [
                'sentence' => '',
                'wrong_word' => '',
                'correct_forms' => [],
                'correct_form' => '',
                'hint' => null,
            ],
            'color_categories' => [
                'items' => [],
                'categories' => [],
                'shuffle_items' => true,
            ],
            'alphabet_letters' => [
                'letters' => [],
                'shuffled_letters' => [],
                'alphabet' => 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ',
            ],
            'alphabet_images' => [
                'items' => [],
                'shuffled_items' => [],
                'show_names' => true,
            ],
            'alphabet_words' => [
                'words' => [],
                'shuffled_words' => [],
                'alphabet' => 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ',
            ],
            'connect_letters' => [
                'letters' => [],
                'correct_order' => [],
                'alphabet' => 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ',
            ],
            'word_from_image' => [
                'image' => '',
                'correct_word' => '',
                'letters' => [],
                'extra_letters' => [],
                'hint' => null,
                'shuffle_letters' => true,
            ],
            'find_by_rule' => [
                'words' => [],
                'rule' => ['type' => '', 'description' => '', 'example' => ''],
                'min_select' => 1,
                'shuffle_words' => true,
            ],
            'find_extra_letter' => [
                'image' => null,
                'word' => '',
                'letters' => [],
                'extra_letter' => '',
                'correct_index' => 0,
                'hint' => null,
            ],
            'connect_category' => [
                'items' => [],
                'categories' => [],
                'shuffle_items' => true,
                'line_colors' => ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'],
            ],
            'drag_to_image' => [
                'pairs' => [],
                'shuffle_words' => true,
                'shuffle_images' => true,
            ],
            'find_by_condition' => [
                'images' => [],
                'condition' => ['text' => '', 'type' => '', 'correct_indices' => []],
                'min_select' => 1,
                'max_select' => 1,
            ],
            'match_behavior' => [
                'items' => [],
                'behaviors' => [],
                'shuffle_items' => true,
            ],
            'build_dialogue' => [
                'dialogues' => [],
                'options' => [],
                'shuffle_options' => true,
                'show_speakers' => true,
            ],
            default => [],
        };
    }

    /**
     * Валидация конфига задания
     */
    public function validateConfig(): bool
    {
        if (!$this->taskType) {
            return false;
        }

        try {
            $validator = new TaskConfigValidator();
            $validator->validate($this->config, $this->taskType->slug);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Валидация конфига с возвратом ошибок
     */
    public function validateConfigWithErrors(): array
    {
        if (!$this->taskType) {
            return ['error' => 'Тип задания не найден'];
        }

        try {
            $validator = new TaskConfigValidator();
            $validator->validate($this->config, $this->taskType->slug);
            return ['valid' => true];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'valid' => false,
                'errors' => $e->errors(),
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Проверить ответ пользователя
     */
    public function checkAnswer(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        return match ($this->type_slug) {
            'choose_one' => $this->checkChooseOne($userAnswer),
            'choose_three' => $this->checkChooseThree($userAnswer),
            'fill_word' => $this->checkFillWord($userAnswer),
            'match_pairs' => $this->checkMatchPairs($userAnswer),
            'build_word' => $this->checkBuildWord($userAnswer),
            'stress_mark' => $this->checkStressMark($userAnswer),
            'drag_drop_text' => $this->checkDragDropText($userAnswer),
            'story_order' => $this->checkStoryOrder($userAnswer),
            'fix_word' => $this->checkFixWord($userAnswer),
            'color_categories' => $this->checkColorCategories($userAnswer),
            'alphabet_letters' => $this->checkAlphabetLetters($userAnswer),
            'alphabet_images' => $this->checkAlphabetImages($userAnswer),
            'alphabet_words' => $this->checkAlphabetWords($userAnswer),
            'connect_letters' => $this->checkConnectLetters($userAnswer),
            'word_from_image' => $this->checkWordFromImage($userAnswer),
            'find_by_rule' => $this->checkFindByRule($userAnswer),
            'find_extra_letter' => $this->checkFindExtraLetter($userAnswer),
            'connect_category' => $this->checkConnectCategory($userAnswer),
            'drag_to_image' => $this->checkDragToImage($userAnswer),
            'find_by_condition' => $this->checkFindByCondition($userAnswer),
            'match_behavior' => $this->checkMatchBehavior($userAnswer),
            'build_dialogue' => $this->checkBuildDialogue($userAnswer),
            default => false,
        };
    }

    // ============================================================
    // 🔍 МЕТОДЫ ПРОВЕРКИ ОТВЕТОВ
    // ============================================================

    private function checkChooseOne($userAnswer): bool
    {
        $correct = $this->getCorrectOption();
        return $correct && ($userAnswer == $correct['id'] || $userAnswer == $correct['text']);
    }

    private function checkChooseThree($userAnswer): bool
    {
        $correct = $this->getCorrectOptions();
        $correctIds = array_column($correct, 'id');
        $userIds = is_array($userAnswer) ? $userAnswer : [$userAnswer];
        sort($correctIds);
        sort($userIds);
        return $correctIds == $userIds;
    }

    private function checkFillWord($userAnswer): bool
    {
        $correct = $this->config['correct'] ?? '';
        $caseSensitive = $this->config['case_sensitive'] ?? false;

        if (!$caseSensitive) {
            return strtolower($userAnswer) === strtolower($correct);
        }

        return $userAnswer === $correct;
    }

    private function checkMatchPairs($userAnswer): bool
    {
        $pairs = $this->config['pairs'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($pairs)) {
            return false;
        }

        foreach ($pairs as $pair) {
            $match = $userAnswer[$pair['text']] ?? null;
            if ($match !== $pair['correct_match']) {
                return false;
            }
        }

        return true;
    }

    private function checkBuildWord($userAnswer): bool
    {
        $correct = $this->config['correct_word'] ?? '';
        return strtoupper($userAnswer) === strtoupper($correct);
    }

    private function checkStressMark($userAnswer): bool
    {
        $correctIndex = $this->config['correct_index'] ?? -1;
        return (int)$userAnswer === $correctIndex;
    }

    private function checkDragDropText($userAnswer): bool
    {
        $sentences = $this->config['sentences'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($sentences)) {
            return false;
        }

        foreach ($sentences as $index => $sentence) {
            $userWord = $userAnswer[$sentence['id']] ?? null;
            if ($userWord !== $sentence['correct_word']) {
                return false;
            }
        }

        return true;
    }

    private function checkStoryOrder($userAnswer): bool
    {
        $parts = $this->config['parts'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($parts)) {
            return false;
        }

        foreach ($parts as $part) {
            $userOrder = array_search($part['id'], $userAnswer);
            if ($userOrder === false || $userOrder + 1 !== $part['correct_order']) {
                return false;
            }
        }

        return true;
    }

    private function checkFixWord($userAnswer): bool
    {
        $correct = $this->config['correct_form'] ?? '';
        return $userAnswer === $correct;
    }

    private function checkColorCategories($userAnswer): bool
    {
        $items = $this->config['items'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($items)) {
            return false;
        }

        foreach ($items as $item) {
            $userCategory = $userAnswer[$item['id']] ?? null;
            if ($userCategory !== $item['category']) {
                return false;
            }
        }

        return true;
    }

    private function checkAlphabetLetters($userAnswer): bool
    {
        $letters = $this->config['letters'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($letters)) {
            return false;
        }

        foreach ($letters as $index => $letter) {
            if ($userAnswer[$index] !== $letter['letter']) {
                return false;
            }
        }

        return true;
    }

    private function checkAlphabetImages($userAnswer): bool
    {
        $items = $this->config['items'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($items)) {
            return false;
        }

        $correctOrder = array_column($items, 'id');
        return $userAnswer === $correctOrder;
    }

    private function checkAlphabetWords($userAnswer): bool
    {
        $words = $this->config['words'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($words)) {
            return false;
        }

        $correctOrder = array_column($words, 'id');
        return $userAnswer === $correctOrder;
    }

    private function checkConnectLetters($userAnswer): bool
    {
        $correct = $this->config['correct_order'] ?? [];
        return is_array($userAnswer) && $userAnswer === $correct;
    }

    private function checkWordFromImage($userAnswer): bool
    {
        $correct = $this->config['correct_word'] ?? '';
        return strtoupper($userAnswer) === strtoupper($correct);
    }

    private function checkFindByRule($userAnswer): bool
    {
        $words = $this->config['words'] ?? [];
        if (!is_array($userAnswer)) {
            return false;
        }

        foreach ($words as $word) {
            $isCorrect = $word['is_correct'] ?? false;
            $userSelected = in_array($word['id'], $userAnswer);

            if ($isCorrect !== $userSelected) {
                return false;
            }
        }

        return true;
    }

    private function checkFindExtraLetter($userAnswer): bool
    {
        $correctIndex = $this->config['correct_index'] ?? -1;
        return (int)$userAnswer === $correctIndex;
    }

    private function checkConnectCategory($userAnswer): bool
    {
        $items = $this->config['items'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($items)) {
            return false;
        }

        foreach ($items as $item) {
            $userCategory = $userAnswer[$item['id']] ?? null;
            if ($userCategory !== $item['category']) {
                return false;
            }
        }

        return true;
    }

    private function checkDragToImage($userAnswer): bool
    {
        $pairs = $this->config['pairs'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($pairs)) {
            return false;
        }

        foreach ($pairs as $pair) {
            $userImage = $userAnswer[$pair['word']] ?? null;
            if ($userImage !== $pair['image']) {
                return false;
            }
        }

        return true;
    }

    private function checkFindByCondition($userAnswer): bool
    {
        $correctIndices = $this->config['condition']['correct_indices'] ?? [];
        $userIndices = is_array($userAnswer) ? $userAnswer : [$userAnswer];
        sort($correctIndices);
        sort($userIndices);
        return $correctIndices == $userIndices;
    }

    private function checkMatchBehavior($userAnswer): bool
    {
        $items = $this->config['items'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($items)) {
            return false;
        }

        foreach ($items as $item) {
            $userBehavior = $userAnswer[$item['id']] ?? null;
            if ($userBehavior !== $item['behavior']) {
                return false;
            }
        }

        return true;
    }

    private function checkBuildDialogue($userAnswer): bool
    {
        $dialogues = $this->config['dialogues'] ?? [];
        if (!is_array($userAnswer) || count($userAnswer) !== count($dialogues)) {
            return false;
        }

        foreach ($dialogues as $dialogue) {
            $userOrder = array_search($dialogue['id'], $userAnswer);
            if ($userOrder === false || $userOrder + 1 !== $dialogue['correct_order']) {
                return false;
            }
        }

        return true;
    }

    // ============================================================
    // 📊 СТАТИСТИКА
    // ============================================================

    /**
     * Получить статистику по заданию
     */
    public function getStats(): array
    {
        $totalAttempts = $this->userProgress()->count();
        $completed = $this->userProgress()->where('status', UserTaskProgress::STATUS_COMPLETED)->count();
        $failed = $this->userProgress()->where('status', UserTaskProgress::STATUS_FAILED)->count();
        $pending = $this->userProgress()->where('status', UserTaskProgress::STATUS_PENDING)->count();
        $averageScore = $this->userProgress()->avg('score') ?? 0;
        $averageTime = $this->userProgress()->avg('time_spent_seconds') ?? 0;

        return [
            'total_attempts' => $totalAttempts,
            'completed' => $completed,
            'failed' => $failed,
            'pending' => $pending,
            'completion_rate' => $totalAttempts > 0
                ? round(($completed / $totalAttempts) * 100, 2)
                : 0,
            'average_score' => round($averageScore, 2),
            'average_time' => round($averageTime, 2),
            'total_users' => $this->userProgress()->distinct('user_id')->count(),
            'max_attempts' => $this->max_attempts,
            'xp_reward' => $this->xp_reward,
        ];
    }

    /**
     * Получить прогресс конкретного пользователя
     */
    public function getProgressForUser(int $userId): ?array
    {
        $progress = $this->userProgress()
            ->where('user_id', $userId)
            ->first();

        if (!$progress) {
            return null;
        }

        return [
            'status' => $progress->status,
            'attempts' => $progress->attempts_count,
            'score' => $progress->score,
            'max_score' => $progress->max_score,
            'completed_at' => $progress->completed_at,
            'is_completed' => $progress->status === UserTaskProgress::STATUS_COMPLETED,
            'time_spent' => $progress->time_spent_seconds,
            'attempts_left' => max(0, ($this->max_attempts ?? 3) - $progress->attempts_count),
        ];
    }

    /**
     * Получить количество пользователей, выполнивших задание
     */
    public function getUsersCount(): int
    {
        return $this->userProgress()
            ->where('status', UserTaskProgress::STATUS_COMPLETED)
            ->distinct('user_id')
            ->count();
    }

    // ============================================================
    // 🚀 ДОПОЛНИТЕЛЬНЫЕ МЕТОДЫ
    // ============================================================

    /**
     * Создать прогресс для пользователя, если его нет
     */
    public function initializeProgress(int $userId): UserTaskProgress
    {
        return UserTaskProgress::firstOrCreate([
            'user_id' => $userId,
            'task_id' => $this->id,
        ], [
            'status' => UserTaskProgress::STATUS_PENDING,
            'attempts_count' => 0,
            'score' => 0,
        ]);
    }

    /**
     * Проверить, доступно ли задание пользователю
     */
    public function isAvailableForUser(int $userId): bool
    {
        // Проверяем, не завершено ли задание
        $progress = $this->userProgress()
            ->where('user_id', $userId)
            ->first();

        if ($progress && $progress->status === UserTaskProgress::STATUS_COMPLETED) {
            return false;
        }

        // Проверяем, не исчерпаны ли попытки
        if ($progress && $progress->status === UserTaskProgress::STATUS_FAILED) {
            return false;
        }

        return true;
    }

    /**
     * Получить следующее задание в уроке
     */
    public function getNextTask(): ?Task
    {
        return $this->lesson->tasks()
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Получить предыдущее задание в уроке
     */
    public function getPreviousTask(): ?Task
    {
        return $this->lesson->tasks()
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
    }

    /**
     * Проверить, является ли задание последним в уроке
     */
    public function isLastInLesson(): bool
    {
        $lastTask = $this->lesson->tasks()
            ->orderBy('sort_order', 'desc')
            ->first();

        return $lastTask && $lastTask->id === $this->id;
    }

    /**
     * Получить сложность задания (на основе конфига)
     */
    public function getDifficulty(): int
    {
        // Можно вычислить на основе количества вариантов, времени и т.д.
        $baseDifficulty = $this->lesson?->piece?->educationModule?->complexity ?? 1;

        // Дополнительные факторы
        $factors = 0;
        if ($this->time_limit_seconds > 0 && $this->time_limit_seconds < 30) {
            $factors += 1; // Сложнее, если мало времени
        }
        if (count($this->hints ?? []) === 0) {
            $factors += 1; // Сложнее, если нет подсказок
        }

        return min(5, $baseDifficulty + $factors);
    }

    /**
     * Получить теги задания (для поиска и фильтрации)
     */
    public function getTags(): array
    {
        $tags = [];

        // Добавляем теги на основе типа
        if ($this->taskType) {
            $tags[] = 'type_' . $this->taskType->slug;
        }

        // Добавляем теги на основе сложности
        $difficulty = $this->getDifficulty();
        $tags[] = 'difficulty_' . $difficulty;

        // Добавляем теги на основе конфига
        if ($this->has_hints) {
            $tags[] = 'has_hints';
        }

        if ($this->time_limit_seconds > 0) {
            $tags[] = 'timed';
        }

        return $tags;
    }

    // ============================================================
    // 🔄 BOOT / EVENTS
    // ============================================================

    protected static function boot()
    {
        parent::boot();

        // При создании проверяем конфиг
        static::creating(function ($task) {
            if (empty($task->config)) {
                $task->config = $task->getDefaultConfig();
            }
        });

        // При обновлении проверяем конфиг
        static::updating(function ($task) {
            if ($task->isDirty('config') && !$task->validateConfig()) {
                throw new \Exception('Невалидная конфигурация задания');
            }
        });

        // При сохранении обновляем XP в уроке, если не указано
        static::saving(function ($task) {
            if (empty($task->xp_reward) && $task->lesson) {
                $task->xp_reward = $task->lesson->xp_reward ?? 10;
            }
        });
    }

    // ============================================================
    // 🎨 ДЛЯ АДМИНКИ / API
    // ============================================================

    /**
     * Преобразовать в массив для API
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type_name,
            'type_slug' => $this->type_slug,
            'config' => $this->config,
            'hints' => $this->hints,
            'max_attempts' => $this->max_attempts,
            'time_limit' => $this->time_limit_seconds,
            'xp_reward' => $this->xp_reward,
            'sort_order' => $this->sort_order,
            'is_completed' => $this->is_completed,
            'is_required' => $this->is_required,
            'attempts_left' => $this->attempts_left,
            'progress' => $this->user_progress,
            'has_hints' => $this->has_hints,
            'hints_count' => $this->hints_count,
            'tags' => $this->getTags(),
            'difficulty' => $this->getDifficulty(),
        ];
    }
}
