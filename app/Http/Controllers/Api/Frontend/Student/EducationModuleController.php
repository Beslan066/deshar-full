<?php

namespace App\Http\Controllers\Api\Frontend\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\UserModuleProgress;
use App\Models\UserLessonProgress;
use App\Models\UserTaskProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EducationModuleController extends Controller
{
    /**
     * Получить все модули для класса ученика
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Доступ только для учеников'], 403);
        }

        $schoolClassTypeId = $user->school_class_type_id;

        if (!$schoolClassTypeId) {
            return response()->json([
                'message' => 'У ученика не указан тип класса',
                'data' => [],
                'meta' => ['total' => 0]
            ]);
        }

        $modules = EducationModule::published()
            ->where('school_class_type_id', $schoolClassTypeId)
            ->ordered()
            ->get();

        $result = $modules->map(function ($module) use ($user) {
            $progress = $user->moduleProgress()
                ->where('module_id', $module->id)
                ->first();

            $totalLessons = 0;
            $totalTasks = 0;
            foreach ($module->pieces as $piece) {
                $totalLessons += $piece->lessons()->count();
                foreach ($piece->lessons as $lesson) {
                    $totalTasks += $lesson->tasks()->count();
                }
            }

            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'image' => $module->image,
                'description' => $module->description,
                'complexity' => $module->complexity,
                'total_xp_reward' => $module->total_xp_reward,
                'total_pieces' => $module->pieces()->count(),
                'total_lessons' => $totalLessons,
                'total_tasks' => $totalTasks,
                'progress' => $progress ? [
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'progress_formatted' => $progress->progress_formatted,
                    'is_completed' => $progress->is_completed,
                    'started_at' => $progress->started_at?->toISOString(),
                    'completed_at' => $progress->completed_at?->toISOString(),
                ] : [
                    'status' => UserModuleProgress::STATUS_NOT_STARTED,
                    'progress_percentage' => 0,
                    'progress_formatted' => '0%',
                    'is_completed' => false,
                    'started_at' => null,
                    'completed_at' => null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'meta' => [
                'total' => $result->count(),
                'school_class_type_id' => $schoolClassTypeId,
                'user_class_type' => $user->schoolClassType?->name ?? 'Не указан',
            ]
        ]);
    }

    /**
     * Получить детальную информацию о модуле с уроками и заданиями
     */
    public function show(Request $request, EducationModule $module): JsonResponse
    {
        $user = $request->user();

        if (!$module->is_published) {
            return response()->json(['message' => 'Модуль не опубликован'], 404);
        }

        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Этот модуль не соответствует вашему классу'], 403);
        }

        // Загружаем разделы с уроками и заданиями
        $module->load(['pieces' => function ($query) {
            $query->orderBy('sort_order');
        }, 'pieces.lessons' => function ($query) {
            $query->orderBy('sort_order');
        }, 'pieces.lessons.tasks' => function ($query) {
            $query->orderBy('sort_order');
        }, 'pieces.lessons.tasks.taskType']);

        $moduleProgress = $user->moduleProgress()
            ->where('module_id', $module->id)
            ->first();

        $totalLessons = 0;
        $totalTasks = 0;
        $piecesWithProgress = [];

        foreach ($module->pieces as $piece) {
            $pieceProgress = $user->pieceProgress()
                ->where('piece_id', $piece->id)
                ->first();

            $lessons = $piece->lessons;
            $lessonsWithProgress = [];
            $totalLessons += count($lessons);

            foreach ($lessons as $lesson) {
                $lessonProgress = $user->lessonProgress()
                    ->where('lesson_id', $lesson->id)
                    ->first();

                $tasks = $lesson->tasks;
                $totalTasks += count($tasks);

                $tasksWithProgress = $tasks->map(function ($task) use ($user) {
                    $taskProgress = $user->taskProgress()
                        ->where('task_id', $task->id)
                        ->first();

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'sort_order' => $task->sort_order,
                        'is_required' => $task->is_required,
                        'xp_reward' => $task->xp_reward,
                        'max_attempts' => $task->max_attempts,
                        'time_limit_seconds' => $task->time_limit_seconds,
                        'audio' => $task->audio,
                        'video' => $task->video,
                        'image' => $task->image,
                        'type' => [
                            'id' => $task->taskType?->id,
                            'slug' => $task->taskType?->slug,
                            'name' => $task->taskType?->name,
                            'icon' => $task->taskType?->icon,
                        ],
                        'config' => $task->config,
                        'hints' => $task->hints,
                        'has_hints' => $task->has_hints,
                        'hints_count' => $task->hints_count,
                        'metadata' => $task->metadata,
                        'progress' => $taskProgress ? [
                            'status' => $taskProgress->status,
                            'attempts_count' => $taskProgress->attempts_count,
                            'score' => $taskProgress->score,
                            'max_score' => $taskProgress->max_score,
                            'is_completed' => $taskProgress->is_completed,
                            'is_failed' => $taskProgress->status === UserTaskProgress::STATUS_FAILED,
                            'attempts_left' => max(0, ($task->max_attempts ?? 3) - $taskProgress->attempts_count),
                            'started_at' => $taskProgress->started_at?->toISOString(),
                            'completed_at' => $taskProgress->completed_at?->toISOString(),
                            'time_spent_seconds' => $taskProgress->time_spent_seconds,
                        ] : [
                            'status' => 'not_started',
                            'attempts_count' => 0,
                            'score' => 0,
                            'max_score' => 0,
                            'is_completed' => false,
                            'is_failed' => false,
                            'attempts_left' => $task->max_attempts ?? 3,
                            'started_at' => null,
                            'completed_at' => null,
                            'time_spent_seconds' => 0,
                        ],
                    ];
                });

                $lessonsWithProgress[] = [
                    'id' => $lesson->id,
                    'name' => $lesson->name,
                    'slug' => $lesson->slug,
                    'description' => $lesson->description,
                    'sort_order' => $lesson->sort_order,
                    'is_required' => $lesson->is_required,
                    'is_published' => $lesson->is_published,
                    'xp_reward' => $lesson->xp_reward,
                    'estimated_time' => $lesson->estimated_time,
                    'audio' => $lesson->audio,
                    'video' => $lesson->video,
                    'image' => $lesson->image,
                    'metadata' => $lesson->metadata,
                    'total_tasks' => count($tasks),
                    'progress' => $lessonProgress ? [
                        'status' => $lessonProgress->status,
                        'progress_percentage' => $lessonProgress->progress_percentage,
                        'is_completed' => $lessonProgress->is_completed,
                        'started_at' => $lessonProgress->started_at?->toISOString(),
                        'completed_at' => $lessonProgress->completed_at?->toISOString(),
                        'time_spent_seconds' => $lessonProgress->time_spent_seconds,
                    ] : [
                        'status' => 'not_started',
                        'progress_percentage' => 0,
                        'is_completed' => false,
                        'started_at' => null,
                        'completed_at' => null,
                        'time_spent_seconds' => 0,
                    ],
                    'tasks' => $tasksWithProgress,
                ];
            }

            $piecesWithProgress[] = [
                'id' => $piece->id,
                'name' => $piece->name,
                'slug' => $piece->slug,
                'fon' => $piece->fon,
                'description' => $piece->description,
                'sort_order' => $piece->sort_order,
                'is_required' => $piece->is_required,
                'is_published' => $piece->is_published,
                'xp_reward' => $piece->xp_reward,
                'estimated_time' => $piece->estimated_time,
                'metadata' => $piece->metadata,
                'total_lessons' => count($lessons),
                'progress' => $pieceProgress ? [
                    'status' => $pieceProgress->status,
                    'progress_percentage' => $pieceProgress->progress_percentage,
                    'is_completed' => $pieceProgress->is_completed,
                    'started_at' => $pieceProgress->started_at?->toISOString(),
                    'completed_at' => $pieceProgress->completed_at?->toISOString(),
                    'time_spent_seconds' => $pieceProgress->time_spent_seconds,
                ] : [
                    'status' => 'not_started',
                    'progress_percentage' => 0,
                    'is_completed' => false,
                    'started_at' => null,
                    'completed_at' => null,
                    'time_spent_seconds' => 0,
                ],
                'lessons' => $lessonsWithProgress,
            ];
        }

        return response()->json([
            'success' => true,
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'image' => $module->image,
                'description' => $module->description,
                'complexity' => $module->complexity,
                'total_xp_reward' => $module->total_xp_reward,
                'total_pieces' => $module->pieces()->count(),
                'total_lessons' => $totalLessons,
                'total_tasks' => $totalTasks,
                'metadata' => $module->metadata,
            ],
            'progress' => $moduleProgress ? [
                'status' => $moduleProgress->status,
                'progress_percentage' => $moduleProgress->progress_percentage,
                'progress_formatted' => $moduleProgress->progress_formatted,
                'is_completed' => $moduleProgress->is_completed,
                'started_at' => $moduleProgress->started_at?->toISOString(),
                'completed_at' => $moduleProgress->completed_at?->toISOString(),
                'time_spent_seconds' => $moduleProgress->time_spent_seconds,
            ] : [
                'status' => UserModuleProgress::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'progress_formatted' => '0%',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'time_spent_seconds' => 0,
            ],
            'pieces' => $piecesWithProgress,
        ]);
    }

    /**
     * Получить прогресс по всем модулям
     */
    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Доступ только для учеников'], 403);
        }

        $schoolClassTypeId = $user->school_class_type_id;

        if (!$schoolClassTypeId) {
            return response()->json([
                'message' => 'У ученика не указан тип класса',
                'progress' => [],
                'summary' => [
                    'total_modules' => 0,
                    'completed_modules' => 0,
                    'in_progress_modules' => 0,
                    'not_started_modules' => 0,
                    'overall_progress' => 0,
                ]
            ]);
        }

        $modules = EducationModule::published()
            ->where('school_class_type_id', $schoolClassTypeId)
            ->ordered()
            ->get();

        $progressData = [];
        $completedCount = 0;
        $inProgressCount = 0;
        $notStartedCount = 0;
        $totalProgress = 0;

        foreach ($modules as $module) {
            $progress = $user->moduleProgress()
                ->where('module_id', $module->id)
                ->first();

            $status = $progress ? $progress->status : UserModuleProgress::STATUS_NOT_STARTED;
            $percentage = $progress ? $progress->progress_percentage : 0;

            if ($status === UserModuleProgress::STATUS_COMPLETED) {
                $completedCount++;
            } elseif ($status === UserModuleProgress::STATUS_IN_PROGRESS) {
                $inProgressCount++;
            } else {
                $notStartedCount++;
            }

            $totalProgress += $percentage;

            $progressData[] = [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'module_slug' => $module->slug,
                'module_image' => $module->image,
                'complexity' => $module->complexity,
                'status' => $status,
                'progress_percentage' => $percentage,
                'progress_formatted' => round($percentage, 1) . '%',
                'is_completed' => $status === UserModuleProgress::STATUS_COMPLETED,
                'started_at' => $progress?->started_at?->toISOString(),
                'completed_at' => $progress?->completed_at?->toISOString(),
                'time_spent_seconds' => $progress?->time_spent_seconds ?? 0,
            ];
        }

        $totalModules = $modules->count();
        $overallProgress = $totalModules > 0 ? round($totalProgress / $totalModules, 2) : 0;

        return response()->json([
            'success' => true,
            'progress' => $progressData,
            'summary' => [
                'total_modules' => $totalModules,
                'completed_modules' => $completedCount,
                'in_progress_modules' => $inProgressCount,
                'not_started_modules' => $notStartedCount,
                'overall_progress' => $overallProgress,
                'overall_progress_formatted' => round($overallProgress, 1) . '%',
            ],
            'meta' => [
                'school_class_type_id' => $schoolClassTypeId,
                'user_class_type' => $user->schoolClassType?->name ?? 'Не указан',
            ]
        ]);
    }

    /**
     * Получить прогресс по конкретному модулю
     */
    public function moduleProgress(Request $request, EducationModule $module): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Доступ только для учеников'], 403);
        }

        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Этот модуль не соответствует вашему классу'], 403);
        }

        $progress = $user->moduleProgress()
            ->where('module_id', $module->id)
            ->first();

        $piecesProgress = [];
        $pieces = $module->pieces()->orderBy('sort_order')->get();

        foreach ($pieces as $piece) {
            $pieceProgress = $user->pieceProgress()
                ->where('piece_id', $piece->id)
                ->first();

            $lessonsProgress = [];
            $lessons = $piece->lessons()->orderBy('sort_order')->get();

            foreach ($lessons as $lesson) {
                $lessonProgress = $user->lessonProgress()
                    ->where('lesson_id', $lesson->id)
                    ->first();

                $tasksProgress = [];
                $tasks = $lesson->tasks()->orderBy('sort_order')->get();

                foreach ($tasks as $task) {
                    $taskProgress = $user->taskProgress()
                        ->where('task_id', $task->id)
                        ->first();

                    $tasksProgress[] = [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'status' => $taskProgress ? $taskProgress->status : 'not_started',
                        'is_completed' => $taskProgress ? $taskProgress->is_completed : false,
                        'attempts_count' => $taskProgress ? $taskProgress->attempts_count : 0,
                        'score' => $taskProgress ? $taskProgress->score : 0,
                    ];
                }

                $lessonsProgress[] = [
                    'lesson_id' => $lesson->id,
                    'lesson_name' => $lesson->name,
                    'status' => $lessonProgress ? $lessonProgress->status : 'not_started',
                    'progress_percentage' => $lessonProgress ? $lessonProgress->progress_percentage : 0,
                    'is_completed' => $lessonProgress ? $lessonProgress->is_completed : false,
                    'tasks' => $tasksProgress,
                ];
            }

            $piecesProgress[] = [
                'piece_id' => $piece->id,
                'piece_name' => $piece->name,
                'piece_fon' => $piece->fon,
                'sort_order' => $piece->sort_order,
                'status' => $pieceProgress ? $pieceProgress->status : 'not_started',
                'progress_percentage' => $pieceProgress ? $pieceProgress->progress_percentage : 0,
                'is_completed' => $pieceProgress ? $pieceProgress->is_completed : false,
                'started_at' => $pieceProgress?->started_at?->toISOString(),
                'completed_at' => $pieceProgress?->completed_at?->toISOString(),
                'lessons' => $lessonsProgress,
            ];
        }

        return response()->json([
            'success' => true,
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
            ],
            'progress' => $progress ? [
                'status' => $progress->status,
                'progress_percentage' => $progress->progress_percentage,
                'progress_formatted' => $progress->progress_formatted,
                'is_completed' => $progress->is_completed,
                'started_at' => $progress->started_at?->toISOString(),
                'completed_at' => $progress->completed_at?->toISOString(),
                'time_spent_seconds' => $progress->time_spent_seconds,
            ] : [
                'status' => UserModuleProgress::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'progress_formatted' => '0%',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'time_spent_seconds' => 0,
            ],
            'pieces_progress' => $piecesProgress,
        ]);
    }

    /**
     * Получить рекомендованные модули
     */
    public function recommended(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isStudent()) {
            return response()->json(['message' => 'Доступ только для учеников'], 403);
        }

        $schoolClassTypeId = $user->school_class_type_id;

        if (!$schoolClassTypeId) {
            return response()->json([
                'message' => 'У ученика не указан тип класса',
                'recommended' => [],
            ]);
        }

        $completedModuleIds = $user->moduleProgress()
            ->where('status', UserModuleProgress::STATUS_COMPLETED)
            ->pluck('module_id')
            ->toArray();

        $recommended = EducationModule::published()
            ->where('school_class_type_id', $schoolClassTypeId)
            ->whereNotIn('id', $completedModuleIds)
            ->ordered()
            ->limit(3)
            ->get();

        $recommendedWithProgress = $recommended->map(function ($module) use ($user) {
            $progress = $user->moduleProgress()
                ->where('module_id', $module->id)
                ->first();

            $totalLessons = 0;
            $totalTasks = 0;
            foreach ($module->pieces as $piece) {
                $totalLessons += $piece->lessons()->count();
                foreach ($piece->lessons as $lesson) {
                    $totalTasks += $lesson->tasks()->count();
                }
            }

            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'image' => $module->image,
                'description' => $module->description,
                'complexity' => $module->complexity,
                'total_xp_reward' => $module->total_xp_reward,
                'total_pieces' => $module->pieces()->count(),
                'total_lessons' => $totalLessons,
                'total_tasks' => $totalTasks,
                'progress' => $progress ? [
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'is_completed' => $progress->is_completed,
                ] : [
                    'status' => UserModuleProgress::STATUS_NOT_STARTED,
                    'progress_percentage' => 0,
                    'is_completed' => false,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'recommended' => $recommendedWithProgress,
            'meta' => [
                'total' => $recommendedWithProgress->count(),
                'school_class_type_id' => $schoolClassTypeId,
            ]
        ]);
    }

    /**
     * Получить детальную информацию о конкретном уроке с заданиями
     */
    public function lesson(Request $request, EducationModule $module, int $lessonId): JsonResponse
    {
        $user = $request->user();

        if (!$module->is_published) {
            return response()->json(['message' => 'Модуль не опубликован'], 404);
        }

        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Этот модуль не соответствует вашему классу'], 403);
        }

        // Находим урок в модуле
        $lesson = null;
        foreach ($module->pieces as $piece) {
            $found = $piece->lessons()->where('id', $lessonId)->first();
            if ($found) {
                $lesson = $found;
                break;
            }
        }

        if (!$lesson) {
            return response()->json(['message' => 'Урок не найден в этом модуле'], 404);
        }

        // Загружаем задания с типами
        $lesson->load(['tasks' => function ($query) {
            $query->orderBy('sort_order');
        }, 'tasks.taskType']);

        $lessonProgress = $user->lessonProgress()
            ->where('lesson_id', $lesson->id)
            ->first();

        $tasksWithProgress = $lesson->tasks->map(function ($task) use ($user) {
            $taskProgress = $user->taskProgress()
                ->where('task_id', $task->id)
                ->first();

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'sort_order' => $task->sort_order,
                'is_required' => $task->is_required,
                'xp_reward' => $task->xp_reward,
                'max_attempts' => $task->max_attempts,
                'time_limit_seconds' => $task->time_limit_seconds,
                'audio' => $task->audio,
                'video' => $task->video,
                'image' => $task->image,
                'type' => [
                    'id' => $task->taskType?->id,
                    'slug' => $task->taskType?->slug,
                    'name' => $task->taskType?->name,
                    'icon' => $task->taskType?->icon,
                ],
                'config' => $task->config,
                'hints' => $task->hints,
                'has_hints' => $task->has_hints,
                'hints_count' => $task->hints_count,
                'metadata' => $task->metadata,
                'progress' => $taskProgress ? [
                    'status' => $taskProgress->status,
                    'attempts_count' => $taskProgress->attempts_count,
                    'score' => $taskProgress->score,
                    'max_score' => $taskProgress->max_score,
                    'is_completed' => $taskProgress->is_completed,
                    'is_failed' => $taskProgress->status === UserTaskProgress::STATUS_FAILED,
                    'attempts_left' => max(0, ($task->max_attempts ?? 3) - $taskProgress->attempts_count),
                    'started_at' => $taskProgress->started_at?->toISOString(),
                    'completed_at' => $taskProgress->completed_at?->toISOString(),
                    'time_spent_seconds' => $taskProgress->time_spent_seconds,
                ] : [
                    'status' => 'not_started',
                    'attempts_count' => 0,
                    'score' => 0,
                    'max_score' => 0,
                    'is_completed' => false,
                    'is_failed' => false,
                    'attempts_left' => $task->max_attempts ?? 3,
                    'started_at' => null,
                    'completed_at' => null,
                    'time_spent_seconds' => 0,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'lesson' => [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'slug' => $lesson->slug,
                'description' => $lesson->description,
                'sort_order' => $lesson->sort_order,
                'is_required' => $lesson->is_required,
                'is_published' => $lesson->is_published,
                'xp_reward' => $lesson->xp_reward,
                'estimated_time' => $lesson->estimated_time,
                'audio' => $lesson->audio,
                'video' => $lesson->video,
                'image' => $lesson->image,
                'metadata' => $lesson->metadata,
                'total_tasks' => $lesson->tasks->count(),
            ],
            'progress' => $lessonProgress ? [
                'status' => $lessonProgress->status,
                'progress_percentage' => $lessonProgress->progress_percentage,
                'is_completed' => $lessonProgress->is_completed,
                'started_at' => $lessonProgress->started_at?->toISOString(),
                'completed_at' => $lessonProgress->completed_at?->toISOString(),
                'time_spent_seconds' => $lessonProgress->time_spent_seconds,
            ] : [
                'status' => 'not_started',
                'progress_percentage' => 0,
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
                'time_spent_seconds' => 0,
            ],
            'tasks' => $tasksWithProgress,
        ]);
    }

    /**
     * Получить детальную информацию о задании
     */
    public function task(Request $request, EducationModule $module, int $lessonId, int $taskId): JsonResponse
    {
        $user = $request->user();

        if (!$module->is_published) {
            return response()->json(['message' => 'Модуль не опубликован'], 404);
        }

        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Этот модуль не соответствует вашему классу'], 403);
        }

        // Находим задание
        $task = null;
        foreach ($module->pieces as $piece) {
            $lesson = $piece->lessons()->where('id', $lessonId)->first();
            if ($lesson) {
                $task = $lesson->tasks()->where('id', $taskId)->with('taskType')->first();
                if ($task) {
                    break;
                }
            }
        }

        if (!$task) {
            return response()->json(['message' => 'Задание не найдено'], 404);
        }

        $taskProgress = $user->taskProgress()
            ->where('task_id', $task->id)
            ->first();

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'sort_order' => $task->sort_order,
                'is_required' => $task->is_required,
                'xp_reward' => $task->xp_reward,
                'max_attempts' => $task->max_attempts,
                'time_limit_seconds' => $task->time_limit_seconds,
                'audio' => $task->audio,
                'video' => $task->video,
                'image' => $task->image,
                'type' => [
                    'id' => $task->taskType?->id,
                    'slug' => $task->taskType?->slug,
                    'name' => $task->taskType?->name,
                    'icon' => $task->taskType?->icon,
                ],
                'config' => $task->config,
                'hints' => $task->hints,
                'has_hints' => $task->has_hints,
                'hints_count' => $task->hints_count,
                'metadata' => $task->metadata,
            ],
            'progress' => $taskProgress ? [
                'status' => $taskProgress->status,
                'attempts_count' => $taskProgress->attempts_count,
                'score' => $taskProgress->score,
                'max_score' => $taskProgress->max_score,
                'is_completed' => $taskProgress->is_completed,
                'is_failed' => $taskProgress->status === UserTaskProgress::STATUS_FAILED,
                'attempts_left' => max(0, ($task->max_attempts ?? 3) - $taskProgress->attempts_count),
                'started_at' => $taskProgress->started_at?->toISOString(),
                'completed_at' => $taskProgress->completed_at?->toISOString(),
                'time_spent_seconds' => $taskProgress->time_spent_seconds,
            ] : [
                'status' => 'not_started',
                'attempts_count' => 0,
                'score' => 0,
                'max_score' => 0,
                'is_completed' => false,
                'is_failed' => false,
                'attempts_left' => $task->max_attempts ?? 3,
                'started_at' => null,
                'completed_at' => null,
                'time_spent_seconds' => 0,
            ],
        ]);
    }
}
