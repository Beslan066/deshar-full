<?php

namespace App\Http\Controllers\Api\Frontend\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationModule;
use App\Models\EducationModulePiece;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PieceController extends Controller
{
    /**
     * Получить все разделы модуля
     */
    public function index(Request $request, EducationModule $module): JsonResponse
    {
        $user = $request->user();

        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $pieces = $module->pieces()
            ->orderBy('sort_order')
            ->get();

        $result = $pieces->map(function ($piece) use ($user) {
            $progress = $user->pieceProgress()
                ->where('piece_id', $piece->id)
                ->first();

            return [
                'id' => $piece->id,
                'name' => $piece->name,
                'fon' => $piece->fon,
                'image' => $piece->image,
                'sort_order' => $piece->sort_order,
                'total_lessons' => $piece->lessons()->count(),
                'progress' => $progress ? [
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'is_completed' => $progress->is_completed,
                ] : [
                    'status' => 'not_started',
                    'progress_percentage' => 0,
                    'is_completed' => false,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
            'meta' => [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'total' => $result->count(),
            ]
        ]);
    }

    /**
     * Получить детальную информацию о разделе с уроками
     */
    public function show(Request $request, EducationModule $module, EducationModulePiece $piece): JsonResponse
    {
        $user = $request->user();

        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        // Проверяем, что раздел принадлежит модулю
        if ($piece->education_module_id !== $module->id) {
            return response()->json(['message' => 'Раздел не принадлежит этому модулю'], 404);
        }

        $piece->load(['lessons' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        $pieceProgress = $user->pieceProgress()
            ->where('piece_id', $piece->id)
            ->first();

        $lessonsData = $piece->lessons->map(function ($lesson) use ($user) {
            $lessonProgress = $user->lessonProgress()
                ->where('lesson_id', $lesson->id)
                ->first();

            return [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'sort_order' => $lesson->sort_order,
                'total_tasks' => $lesson->tasks()->count(),
                'progress' => $lessonProgress ? [
                    'status' => $lessonProgress->status,
                    'progress_percentage' => $lessonProgress->progress_percentage,
                    'is_completed' => $lessonProgress->is_completed,
                ] : [
                    'status' => 'not_started',
                    'progress_percentage' => 0,
                    'is_completed' => false,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'piece' => [
                'id' => $piece->id,
                'name' => $piece->name,
                'fon' => $piece->fon,
                'image' => $piece->image,
                'description' => $piece->description,
                'sort_order' => $piece->sort_order,
                'total_lessons' => $piece->lessons->count(),
            ],
            'progress' => $pieceProgress ? [
                'status' => $pieceProgress->status,
                'progress_percentage' => $pieceProgress->progress_percentage,
                'is_completed' => $pieceProgress->is_completed,
                'started_at' => $pieceProgress->started_at?->toISOString(),
                'completed_at' => $pieceProgress->completed_at?->toISOString(),
            ] : [
                'status' => 'not_started',
                'progress_percentage' => 0,
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
            ],
            'lessons' => $lessonsData,
        ]);
    }

    /**
     * Получить прогресс по разделу
     */
    public function progress(Request $request, EducationModule $module, EducationModulePiece $piece): JsonResponse
    {
        $user = $request->user();

        if ($module->school_class_type_id !== $user->school_class_type_id) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $progress = $user->pieceProgress()
            ->where('piece_id', $piece->id)
            ->first();

        // Прогресс по урокам в разделе
        $lessonsProgress = [];
        $lessons = $piece->lessons()->orderBy('sort_order')->get();

        foreach ($lessons as $lesson) {
            $lessonProgress = $user->lessonProgress()
                ->where('lesson_id', $lesson->id)
                ->first();

            $lessonsProgress[] = [
                'lesson_id' => $lesson->id,
                'lesson_name' => $lesson->name,
                'status' => $lessonProgress ? $lessonProgress->status : 'not_started',
                'progress_percentage' => $lessonProgress ? $lessonProgress->progress_percentage : 0,
                'is_completed' => $lessonProgress ? $lessonProgress->is_completed : false,
            ];
        }

        return response()->json([
            'piece' => [
                'id' => $piece->id,
                'name' => $piece->name,
            ],
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
            ],
            'progress' => $progress ? [
                'status' => $progress->status,
                'progress_percentage' => $progress->progress_percentage,
                'is_completed' => $progress->is_completed,
                'started_at' => $progress->started_at?->toISOString(),
                'completed_at' => $progress->completed_at?->toISOString(),
            ] : [
                'status' => 'not_started',
                'progress_percentage' => 0,
                'is_completed' => false,
                'started_at' => null,
                'completed_at' => null,
            ],
            'lessons_progress' => $lessonsProgress,
        ]);
    }
}
