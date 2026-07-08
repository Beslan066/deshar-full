<?php

namespace App\Http\Controllers\Api\Frontend\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\UserModuleProgress;
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
            ->with(['pieces' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->get();

        $result = $modules->map(function ($module) use ($user) {
            $progress = $user->moduleProgress()
                ->where('module_id', $module->id)
                ->first();

            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'image' => $module->image,
                'description' => $module->description,
                'complexity' => $module->complexity,
                'total_xp_reward' => $module->total_xp_reward,
                'total_pieces' => $module->pieces->count(),
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
     * Получить детальную информацию о модуле (с разделами)
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

        // Загружаем только разделы (без уроков)
        $module->load(['pieces' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        $moduleProgress = $user->moduleProgress()
            ->where('module_id', $module->id)
            ->first();

        // Собираем информацию по разделам
        $piecesData = $module->pieces->map(function ($piece) use ($user) {
            $pieceProgress = $user->pieceProgress()
                ->where('piece_id', $piece->id)
                ->first();

            return [
                'id' => $piece->id,
                'name' => $piece->name,
                'fon' => $piece->fon,
                'image' => $piece->image,
                'sort_order' => $piece->sort_order,
                'total_lessons' => $piece->lessons()->count(),
                'progress' => $pieceProgress ? [
                    'status' => $pieceProgress->status,
                    'progress_percentage' => $pieceProgress->progress_percentage,
                    'is_completed' => $pieceProgress->is_completed,
                ] : [
                    'status' => 'not_started',
                    'progress_percentage' => 0,
                    'is_completed' => false,
                ],
            ];
        });

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
                'total_pieces' => $module->pieces->count(),
            ],
            'progress' => $moduleProgress ? [
                'status' => $moduleProgress->status,
                'progress_percentage' => $moduleProgress->progress_percentage,
                'progress_formatted' => $moduleProgress->progress_formatted,
                'is_completed' => $moduleProgress->is_completed,
                'started_at' => $moduleProgress->started_at?->toISOString(),
                'completed_at' => $moduleProgress->completed_at?->toISOString(),
            ] : [
                'status' => UserModuleProgress::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'progress_formatted' => '0%',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
            ],
            'pieces' => $piecesData,
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
            ];
        }

        $totalModules = $modules->count();
        $overallProgress = $totalModules > 0 ? round($totalProgress / $totalModules, 2) : 0;

        return response()->json([
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

        // Получаем прогресс по разделам
        $piecesProgress = [];
        $pieces = $module->pieces()->orderBy('sort_order')->get();

        foreach ($pieces as $piece) {
            $pieceProgress = $user->pieceProgress()
                ->where('piece_id', $piece->id)
                ->first();

            $piecesProgress[] = [
                'piece_id' => $piece->id,
                'piece_name' => $piece->name,
                'piece_fon' => $piece->fon,
                'sort_order' => $piece->sort_order,
                'status' => $pieceProgress ? $pieceProgress->status : 'not_started',
                'progress_percentage' => $pieceProgress ? $pieceProgress->progress_percentage : 0,
                'is_completed' => $pieceProgress ? $pieceProgress->is_completed : false,
            ];
        }

        return response()->json([
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
            ] : [
                'status' => UserModuleProgress::STATUS_NOT_STARTED,
                'progress_percentage' => 0,
                'progress_formatted' => '0%',
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
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

            return [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'image' => $module->image,
                'description' => $module->description,
                'complexity' => $module->complexity,
                'total_xp_reward' => $module->total_xp_reward,
                'total_pieces' => $module->pieces()->count(),
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
            'recommended' => $recommendedWithProgress,
            'meta' => [
                'total' => $recommendedWithProgress->count(),
                'school_class_type_id' => $schoolClassTypeId,
            ]
        ]);
    }
}
