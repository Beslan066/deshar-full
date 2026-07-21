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
        'audio',
        'video',
        'image'
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
            'accent_trainer' => $this->config['correct_variant_ids'] ?? [],
            'single_select_image_quiz' => $this->config['correct_variant_id'] ?? null,
            'fix_sentence' => $this->config['correctAnswer'] ?? null,
            'alphabetic_sorter' => $this->config['slots'] ?? [],
            'category_matcher' => $this->config['items'] ?? [],
            'colorize_words' => $this->config['variants'] ?? [],
            'conclusion' => $this->config['data'] ?? [],
            'delete_extra_letter' => $this->config['correctVariantIds'] ?? [],
            'drop_word_to_image' => $this->config['items'] ?? [],
            'drop_word_to_text' => $this->config['items'] ?? [],
            'multi_quiz' => $this->config['correctVariantIds'] ?? [],
            'reorder_items' => $this->config['correctOrderIds'] ?? [],
            'sequence_builder' => $this->config['slots'] ?? [],
            'single_quiz' => $this->config['correctVariantId'] ?? null,
            'word_by_image' => $this->config['correctAnswer'] ?? null,
            'word_picker' => $this->config['correctValues'] ?? [],
            'drag_word_to_pocket' => $this->config['items'] ?? [],
            'phrase_image_matcher' => $this->config['items'] ?? [],
            default => null,
        };
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
            // Дополнительные конфиги
            'accent_trainer' => [
                'variants' => [
                    ['id' => '1', 'letter' => ''],
                    ['id' => '2', 'letter' => ''],
                ],
                'correct_variant_ids' => [],
                'shuffle_variants' => true,
            ],
            'single_select_image_quiz' => [
                'variants' => [
                    ['id' => '1', 'imageUrl' => ''],
                    ['id' => '2', 'imageUrl' => ''],
                ],
                'correct_variant_id' => '1',
                'shuffle_variants' => true,
            ],
            'fix_sentence' => [
                'sentence' => '',
                'words' => [''],
                'correctAnswer' => '',
            ],
            'alphabetic_sorter' => [
                'slots' => [
                    ['id' => 'slot-1', 'correctValue' => '', 'slotTitle' => ''],
                ],
                'variants' => [
                    ['id' => 'variant-1', 'value' => ''],
                ],
            ],
            'category_matcher' => [
                'items' => [
                    [
                        'id' => '',
                        'label' => '',
                        'correct' => '',
                        'color' => '',
                    ],
                ],
                'categories' => [
                    [
                        'id' => '',
                        'label' => '',
                        'color' => '',
                    ],
                ],
            ],
            'colorize_words' => [
                'tools' => [
                    [
                        'type' => '',
                        'toolName' => '',
                        'toolColor' => null,
                    ],
                ],
                'variants' => [
                    [
                        'id' => 'var-1',
                        'content' => '',
                        'correctColor' => '',
                    ],
                ],
            ],
            'conclusion' => [
                'data' => [
                    [
                        'id' => 'conclusion-1',
                        'value' => '',
                        'completed' => false,
                        'variants' => [
                            [
                                'id' => 'variant-1',
                                'value' => '',
                            ],
                        ],
                        'slots' => [
                            [
                                'id' => 'slot-1',
                                'current' => null,
                                'correct' => '',
                            ],
                        ],
                    ],
                ],
            ],
            'delete_extra_letter' => [
                'variants' => [
                    [
                        'id' => '',
                        'letter' => '',
                    ],
                ],
                'correctVariantIds' => [''],
            ],
            'drop_word_to_image' => [
                'items' => [
                    [
                        'id' => 'item-1',
                        'imageUrl' => '',
                        'correctVariantId' => 'var-1',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 'var-1',
                        'value' => '',
                    ],
                ],
            ],
            'drop_word_to_text' => [
                'items' => [
                    [
                        'id' => 'item-1',
                        'content' => '',
                        'correctVariantId' => 'variant-1',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 'variant-1',
                        'value' => '',
                    ],
                ],
            ],

            'multi_quiz' => [
                'variants' => [
                    [
                        'id' => '1',
                        'itemNumber' => '1',
                        'title' => '',
                    ],
                    [
                        'id' => '2',
                        'itemNumber' => '2',
                        'title' => '',
                    ],
                ],
                'correctVariantIds' => [],
            ],
            'reorder_items' => [
                'data' => [
                    [
                        'id' => '',
                        'content' => '',
                    ],
                    [
                        'id' => '',
                        'content' => '',
                    ],
                ],
                'correctOrderIds' => ['', ''],
            ],
            'sequence_builder' => [
                'slots' => [
                    [
                        'slotId' => '',
                        'content' => '',
                        'correctValue' => '',
                    ],
                    [
                        'slotId' => '',
                        'content' => '',
                        'correctValue' => '',
                    ],
                ],
                'variants' => [
                    [
                        'id' => '1',
                        'content' => '',
                    ],
                    [
                        'id' => '2',
                        'content' => '',
                    ],
                ],
            ],
            'single_quiz' => [
                'variants' => [
                    [
                        'id' => '1',
                        'itemNumber' => '1',
                        'title' => '',
                    ],
                    [
                        'id' => '2',
                        'itemNumber' => '2',
                        'title' => '',
                    ],
                ],
                'correctVariantId' => '1',
            ],
            'word_by_image' => [
                'id' => '',
                'correctAnswer' => '',
                'imageUrl' => '',
                'availableLetters' => [
                    [
                        'id' => 'letter-1',
                        'letter' => '',
                    ],
                ],
            ],
            'word_picker' => [
                'text' => '',
                'correctValues' => [''],
            ],
            'drag_word_to_pocket' => [
                'items' => [
                    [
                        'id' => 'item-1',
                        'imageUrl' => '',
                        'correctVariantId' => 'variant-1',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 'variant-1',
                        'value' => '',
                    ],
                ],
            ],
            'phrase_image_matcher' => [
                'items' => [
                    [
                        'id' => 'item-1',
                        'correctVariantId' => 'variant-1',
                        'imageUrl' => '',
                    ],
                ],
                'variants' => [
                    [
                        'id' => 'variant-1',
                        'value' => '',
                    ],
                ],
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

        return match ($this->type_slug) {
            //новые
            'accent_trainer' => $this->checkAccentTrainer($userAnswer),
            'single_select_image_quiz' => $this->checkSingleSelectImageQuiz($userAnswer),
            'fix_sentence' => $this->checkFixSentence($userAnswer),
            'alphabetic_sorter' => $this->checkAlphabeticSorter($userAnswer),
            'category_matcher' => $this->checkCategoryMatcher($userAnswer),
            'colorize_words' => $this->checkColorizeWords($userAnswer),
            'conclusion' => $this->checkConclusion($userAnswer),
            'delete_extra_letter' => $this->checkDeleteExtraLetter($userAnswer),
            'drop_word_to_image' => $this->checkDropWordToImage($userAnswer),
            'drop_word_to_text' => $this->checkDropWordToText($userAnswer),
            'multi_quiz' => $this->checkMultiQuiz($userAnswer),
            'reorder_items' => $this->checkReorderItems($userAnswer),
            'phrase_image_matcher' => $this->checkPhraseImageMatcher($userAnswer),
            'sequence_builder' => $this->checkSequenceBuilder($userAnswer),
            'single_quiz' => $this->checkSingleQuiz($userAnswer),
            'word_by_image' => $this->checkWordByImage($userAnswer),
            'word_picker' => $this->checkWordPicker($userAnswer),
            'drag_word_to_pocket' => $this->checkDragWordToPocket($userAnswer),
            default => false,
        };
    }

    // ============================================================
    // 🔍 МЕТОДЫ ПРОВЕРКИ ОТВЕТОВ
    // ============================================================
    private function checkAccentTrainer(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($correct)) {
            return false;
        }

        if (count($userAnswer) !== count($correct)) {
            return false;
        }

        $userAnswer = array_map('strval', $userAnswer);
        $correct = array_map('strval', $correct);

        sort($userAnswer);
        sort($correct);

        return $userAnswer === $correct;
    }
    private function checkSingleSelectImageQuiz(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if ($correct === null || $userAnswer === null) {
            return false;
        }
        return (string) $userAnswer === (string) $correct;
    }
    private function checkFixSentence(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if ($correct === null) {
            return false;
        }

        return trim((string) $userAnswer) === trim((string) $correct);
    }
    private function checkAlphabeticSorter(mixed $userAnswer): bool
    {
        $slots = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($slots) || count($userAnswer) !== count($slots)) {
            return false;
        }

        $userAnswersFlattened = collect($userAnswer)
            ->collapse()
            ->map(fn($val) => trim((string) $val))
            ->toArray();

        return collect($slots)->every(function ($slot) use ($userAnswersFlattened) {
            $slotId = isset($slot['id']) ? (string) $slot['id'] : '';
            if (!isset($userAnswersFlattened[$slotId])) {
                return false;
            }
            $userVal = $userAnswersFlattened[$slotId];
            $correctVal = trim((string) ($slot['correctValue'] ?? ''));

            return mb_strtolower($userVal, 'UTF-8') === mb_strtolower($correctVal, 'UTF-8');
        });
    }
    private function checkCategoryMatcher(mixed $userAnswer): bool
    {
        $items = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($items) || count($userAnswer) !== count($items)) {
            return false;
        }

        $userAnswersFlattened = collect($userAnswer)
            ->collapse()
            ->map(fn($val) => trim((string) $val))
            ->toArray();

        return collect($items)->every(function ($item) use ($userAnswersFlattened) {
            $itemId = isset($item['id']) ? (string) $item['id'] : '';

            if (!isset($userAnswersFlattened[$itemId])) {
                return false;
            }

            $userVal = $userAnswersFlattened[$itemId];
            $correctVal = trim((string) ($item['correct'] ?? ''));

            return $userVal === $correctVal;
        });
    }

    private function checkColorizeWords(mixed $userAnswer): bool
    {
        $variants = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($variants) || count($userAnswer) !== count($variants)) {
            return false;
        }
        $userAnswersFlattened = collect($userAnswer)
            ->collapse()
            ->map(fn($val) => trim((string) $val))
            ->toArray();

        return collect($variants)->every(function ($variant) use ($userAnswersFlattened) {
            $variantId = isset($variant['id']) ? (string) $variant['id'] : '';

            if (!isset($userAnswersFlattened[$variantId])) {
                return false;
            }

            $userColor = $userAnswersFlattened[$variantId];
            $correctColor = trim((string) ($variant['correctColor'] ?? ''));

            return mb_strtolower($userColor, 'UTF-8') === mb_strtolower($correctColor, 'UTF-8');
        });
    }
    private function checkConclusion(mixed $userAnswer): bool
    {
        $data = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($data)) {
            return false;
        }

        $expectedSlots = collect($data)
            ->pluck('slots')
            ->collapse()
            ->associateBy(
                fn($slot) => isset($slot['id']) ? (string) $slot['id'] : '',
                fn($slot) => trim((string) ($slot['correct'] ?? ''))
            )
            ->filter(fn($val, $key) => $key !== '')
            ->toArray();

        if (count($userAnswer) !== count($expectedSlots)) {
            return false;
        }

        $userAnswersFlattened = collect($userAnswer)
            ->collapse()
            ->map(fn($val) => trim((string) $val))
            ->toArray();

        return collect($expectedSlots)->every(function ($correctValue, $slotId) use ($userAnswersFlattened) {
            $slotId = (string) $slotId;

            if (!isset($userAnswersFlattened[$slotId])) {
                return false;
            }

            return mb_strtolower($userAnswersFlattened[$slotId], 'UTF-8') === mb_strtolower($correctValue, 'UTF-8');
        });
    }
    private function checkDeleteExtraLetter(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($correct)) {
            return false;
        }

        if (count($userAnswer) !== count($correct)) {
            return false;
        }

        $userAnswerCleaned = array_map(fn($id) => trim((string) $id), $userAnswer);
        $correctCleaned = array_map(fn($id) => trim((string) $id), $correct);

        sort($userAnswerCleaned);
        sort($correctCleaned);

        return $userAnswerCleaned === $correctCleaned;
    }
    private function checkDropWordToImage(mixed $userAnswer): bool
    {
        $items = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($items) || count($userAnswer) !== count($items)) {
            return false;
        }

        $stringUserAnswer = [];
        foreach ($userAnswer as $key => $val) {
            $stringUserAnswer[(string) $key] = trim((string) $val);
        }
        return collect($items)->every(function ($item) use ($stringUserAnswer) {
            $itemId = isset($item['id']) ? (string) $item['id'] : '';
            if (!isset($stringUserAnswer[$itemId])) {
                return false;
            }
            $correctVariantId = $item['correctVariantId'] ?? $item['correct_variant_id'] ?? '';
            $correctVariantId = trim((string) $correctVariantId);

            return $stringUserAnswer[$itemId] === $correctVariantId;
        });
    }

    private function checkDropWordToText(mixed $userAnswer): bool
    {
        $items = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($items) || count($userAnswer) !== count($items)) {
            return false;
        }

        // Нормализуем пользовательский ответ: приводим ключи и значения к строкам
        $stringUserAnswer = [];
        foreach ($userAnswer as $key => $val) {
            $stringUserAnswer[(string) $key] = trim((string) $val);
        }

        foreach ($items as $item) {
            $itemId = isset($item['id']) ? (string) $item['id'] : '';

            // Поддерживаем camelCase и snake_case для гибкости
            $correctVariantId = $item['correctVariantId'] ?? $item['correct_variant_id'] ?? '';
            $correctVariantId = trim((string) $correctVariantId);

            if (!isset($stringUserAnswer[$itemId]) || $stringUserAnswer[$itemId] !== $correctVariantId) {
                return false;
            }
        }

        return true;
    }

    private function checkReorderItems(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($correct)) {
            return false;
        }

        if (count($userAnswer) !== count($correct)) {
            return false;
        }

        $userAnswerCleaned = array_map(fn($id) => trim((string) $id), $userAnswer);
        $correctCleaned = array_map(fn($id) => trim((string) $id), $correct);

        return array_values($userAnswerCleaned) === array_values($correctCleaned);
    }
    private function checkMultiQuiz(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($correct)) {
            return false;
        }

        if (count($userAnswer) !== count($correct)) {
            return false;
        }

        $userAnswer = array_map(fn($id) => trim((string) $id), $userAnswer);
        $correct = array_map(fn($id) => trim((string) $id), $correct);

        sort($userAnswer);
        sort($correct);

        return $userAnswer === $correct;
    }
    private function checkPhraseImageMatcher(mixed $userAnswer): bool
    {
        $items = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($items) || count($userAnswer) !== count($items)) {
            return false;
        }

        $stringUserAnswer = [];
        foreach ($userAnswer as $key => $val) {
            $stringUserAnswer[(string) $key] = trim((string) $val);
        }

        foreach ($items as $item) {
            if (!isset($item['id'])) {
                return false;
            }

            $itemId = (string) $item['id'];

            $correctVariantId = $item['correctVariantId'] ?? $item['correct_variant_id'] ?? '';
            $correctVariantId = trim((string) $correctVariantId);

            if (!isset($stringUserAnswer[$itemId]) || $stringUserAnswer[$itemId] !== $correctVariantId) {
                return false;
            }
        }

        return true;
    }
    private function checkSequenceBuilder(mixed $userAnswer): bool
    {
        $slots = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($slots)) {
            return false;
        }

        if (count($userAnswer) !== count($slots)) {
            return false;
        }
        $stringUserAnswer = [];
        foreach ($userAnswer as $key => $val) {
            $stringUserAnswer[(string) $key] = trim((string) $val);
        }

        foreach ($slots as $slot) {
            $slotId = isset($slot['slotId']) ? (string) $slot['slotId'] : '';
            $correctValue = trim((string) ($slot['correctValue'] ?? ''));

            if (!isset($stringUserAnswer[$slotId]) || $stringUserAnswer[$slotId] !== $correctValue) {
                return false;
            }
        }

        return true;
    }
    private function checkSingleQuiz(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if ($correct === null || $userAnswer === null) {
            return false;
        }
        return trim((string) $userAnswer) === trim((string) $correct);
    }


    private function checkWordByImage(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if ($correct === null) {
            return false;
        }

        $userVal = mb_strtolower(trim((string) $userAnswer), 'UTF-8');
        $correctVal = mb_strtolower(trim((string) $correct), 'UTF-8');

        return $userVal === $correctVal;
    }

    private function checkWordPicker(mixed $userAnswer): bool
    {
        $correct = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($correct)) {
            return false;
        }

        if (count($userAnswer) !== count($correct)) {
            return false;
        }

        $userAnswerCleaned = array_map(fn($val) => mb_strtolower(trim((string) $val), 'UTF-8'), $userAnswer);
        $correctCleaned = array_map(fn($val) => mb_strtolower(trim((string) $val), 'UTF-8'), $correct);

        sort($userAnswerCleaned);
        sort($correctCleaned);

        return $userAnswerCleaned === $correctCleaned;
    }
    private function checkDragWordToPocket(mixed $userAnswer): bool
    {
        $items = $this->getCorrectAnswer();

        if (!is_array($userAnswer) || !is_array($items) || count($userAnswer) !== count($items)) {
            return false;
        }

        $stringUserAnswer = [];
        foreach ($userAnswer as $key => $val) {
            $stringUserAnswer[(string) $key] = trim((string) $val);
        }

        foreach ($items as $item) {
            $itemId = isset($item['id']) ? (string) $item['id'] : '';

            $correctVariantId = $item['correctVariantId'] ?? $item['correct_variant_id'] ?? '';
            $correctVariantId = trim((string) $correctVariantId);

            if (!isset($stringUserAnswer[$itemId]) || $stringUserAnswer[$itemId] !== $correctVariantId) {
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
