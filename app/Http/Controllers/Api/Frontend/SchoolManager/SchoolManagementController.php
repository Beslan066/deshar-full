<?php

namespace App\Http\Controllers\Api\Frontend\SchoolManager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\EducationModule;
use App\Models\UserModuleProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SchoolManagementController extends Controller
{
    /**
     * Проверка прав доступа (только role_id = 6)
     */
    private function checkAccess(Request $request): bool
    {
        $user = $request->user();

        if (!$user) {
            return false;
        }

        // Разрешенные роли: Представитель школы (role_id: 6) и Директор школы (role_id: 7)
        $allowedRoleIds = [6, 7];

        return in_array($user->role_id, $allowedRoleIds);
    }

    /**
     * Получить все классы в школе
     */
    public function getClasses(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для ответственного по школе'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        if (!$schoolId) {
            return response()->json(['message' => 'У вас не привязана школа'], 404);
        }

        $classes = SchoolClass::where('school_id', $schoolId)
            ->withCount(['students' => function ($query) {
                $query->where('user_type', 'student');
            }])
            ->with(['schoolClassType', 'teacher'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'students_count' => $class->students_count ?? 0,
                    'class_type_id' => $class->school_class_type_id,
                    'class_type_name' => $class->schoolClassType?->name ?? 'Не указан',
                    'teacher_id' => $class->teacher_id,
                    'teacher_name' => $class->teacher?->name ?? 'Не назначен',
                    'created_at' => $class->created_at?->toISOString(),
                ];
            }),
            'meta' => [
                'total' => $classes->count(),
                'school_id' => $schoolId,
            ]
        ]);
    }

    /**
     * Получить учеников конкретного класса
     */
    public function getClassStudents(Request $request, int $classId): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        // Проверяем, что класс принадлежит школе ответственного
        $class = SchoolClass::where('id', $classId)
            ->where('school_id', $schoolId)
            ->first();

        if (!$class) {
            return response()->json(['message' => 'Класс не найден или не принадлежит вашей школе'], 404);
        }

        $students = User::where('school_class_id', $classId)
            ->where('user_type', 'student')
            ->select('id', 'name', 'email', 'avatar', 'points', 'level', 'current_streak', 'last_activity_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
            ],
            'students' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'avatar' => $student->avatar,
                    'xp' => $student->points,
                    'level' => $student->level ?? 0,
                    'level_name' => $student->level_name,
                    'current_streak' => $student->current_streak ?? 0,
                    'is_online' => $student->is_online,
                    'last_activity' => $student->last_activity_at?->diffForHumans() ?? 'Никогда',
                ];
            }),
            'meta' => [
                'total_students' => $students->count(),
            ]
        ]);
    }

    /**
     * Получить всех учителей школы
     */
    public function getTeachers(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        if (!$schoolId) {
            return response()->json(['message' => 'У вас не привязана школа'], 404);
        }

        $teachers = User::where('school_id', $schoolId)
            ->where('user_type', 'teacher')
            ->select('id', 'name', 'email', 'avatar', 'last_activity_at')
            ->orderBy('name')
            ->get();

        // Для каждого учителя считаем количество учеников
        $teachersWithStats = $teachers->map(function ($teacher) {
            // Считаем учеников, которые привязаны к классам учителя
            $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
            $studentsCount = User::whereIn('school_class_id', $classIds)
                ->where('user_type', 'student')
                ->count();

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'avatar' => $teacher->avatar,
                'is_online' => $teacher->is_online,
                'last_activity' => $teacher->last_activity_at?->diffForHumans() ?? 'Никогда',
                'students_count' => $studentsCount,
                'classes_count' => $classIds->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $teachersWithStats,
            'meta' => [
                'total' => $teachersWithStats->count(),
                'school_id' => $schoolId,
            ]
        ]);
    }

    /**
     * Получить общую статистику по школе с детализацией по классам
     */
    public function getStatistics(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для ответственного по школе'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        if (!$schoolId) {
            return response()->json(['message' => 'У вас не привязана школа'], 404);
        }

        // Получаем все классы школы
        $classes = SchoolClass::where('school_id', $schoolId)
            ->with(['schoolClassType', 'teacher'])
            ->get();

        // Общая статистика по школе
        $totalStudents = User::where('school_id', $schoolId)
            ->where('user_type', 'student')
            ->count();

        $totalTeachers = User::where('school_id', $schoolId)
            ->where('user_type', 'teacher')
            ->count();

        $totalClasses = $classes->count();

        // Все ученики школы
        $allStudents = User::where('school_id', $schoolId)
            ->where('user_type', 'student')
            ->get();

        $totalXp = $allStudents->sum('points');
        $avgXp = $allStudents->count() > 0 ? round($totalXp / $allStudents->count(), 2) : 0;
        $avgLevel = $allStudents->count() > 0 ? round($allStudents->avg('level'), 2) : 0;

        // Активность за последние 7 дней
        $activeStudents = $allStudents->filter(function ($student) {
            return $student->last_activity_at && $student->last_activity_at->diffInDays(now()) <= 7;
        })->count();

        // Статистика по модулям
        $totalModules = EducationModule::where('is_published', true)->count();
        $completedModules = UserModuleProgress::where('status', UserModuleProgress::STATUS_COMPLETED)
            ->whereIn('user_id', $allStudents->pluck('id'))
            ->count();

        // Детальная статистика по каждому классу
        $classesStats = $classes->map(function ($class) use ($totalModules) {
            // Ученики класса
            $students = User::where('school_class_id', $class->id)
                ->where('user_type', 'student')
                ->get();

            $studentsCount = $students->count();
            $totalXpClass = $students->sum('points');
            $avgXpClass = $studentsCount > 0 ? round($totalXpClass / $studentsCount, 2) : 0;
            $avgLevelClass = $studentsCount > 0 ? round($students->avg('level'), 2) : 0;

            // Активные ученики класса (за 7 дней)
            $activeStudentsClass = $students->filter(function ($student) {
                return $student->last_activity_at && $student->last_activity_at->diffInDays(now()) <= 7;
            })->count();

            // Прогресс по модулям для класса
            $completedModulesClass = UserModuleProgress::where('status', UserModuleProgress::STATUS_COMPLETED)
                ->whereIn('user_id', $students->pluck('id'))
                ->count();

            // Топ-3 ученика класса по XP
            $topStudents = $students->sortByDesc('points')
                ->take(3)
                ->values()
                ->map(function ($student, $index) {
                    return [
                        'rank' => $index + 1,
                        'id' => $student->id,
                        'name' => $student->name,
                        'xp' => $student->points,
                        'level' => $student->level ?? 0,
                        'level_name' => $student->level_name,
                    ];
                });

            // Прогресс класса по модулям (в процентах)
            $totalPossibleCompletions = $studentsCount * $totalModules;
            $classProgressPercentage = $totalPossibleCompletions > 0
                ? round(($completedModulesClass / $totalPossibleCompletions) * 100, 2)
                : 0;

            return [
                'class' => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'class_type_id' => $class->school_class_type_id,
                    'class_type_name' => $class->schoolClassType?->name ?? 'Не указан',
                    'teacher_id' => $class->teacher_id,
                    'teacher_name' => $class->teacher?->name ?? 'Не назначен',
                ],
                'statistics' => [
                    'students_count' => $studentsCount,
                    'total_xp' => $totalXpClass,
                    'average_xp' => $avgXpClass,
                    'average_level' => $avgLevelClass,
                    'active_students' => $activeStudentsClass,
                    'active_percentage' => $studentsCount > 0
                        ? round(($activeStudentsClass / $studentsCount) * 100, 2)
                        : 0,
                    'completed_modules' => $completedModulesClass,
                    'class_progress_percentage' => $classProgressPercentage,
                ],
                'top_students' => $topStudents,
            ];
        });

        // Рейтинг классов по успеваемости
        $classRanking = $classesStats->sortByDesc(function ($class) {
            return $class['statistics']['average_xp'];
        })->values()->map(function ($class, $index) {
            return [
                'rank' => $index + 1,
                'class_name' => $class['class']['name'],
                'average_xp' => $class['statistics']['average_xp'],
                'students_count' => $class['statistics']['students_count'],
                'progress_percentage' => $class['statistics']['class_progress_percentage'],
            ];
        });

        return response()->json([
            'success' => true,
            'statistics' => [
                'overview' => [
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_classes' => $totalClasses,
                    'total_modules' => $totalModules,
                ],
                'school_progress' => [
                    'total_xp' => $totalXp,
                    'average_xp' => $avgXp,
                    'average_level' => $avgLevel,
                    'completed_modules_total' => $completedModules,
                    'active_students_last_7_days' => $activeStudents,
                    'active_percentage' => $totalStudents > 0
                        ? round(($activeStudents / $totalStudents) * 100, 2)
                        : 0,
                ],
                'classes' => $classesStats,
                'class_ranking' => $classRanking,
            ],
            'meta' => [
                'school_id' => $schoolId,
                'school_name' => $user->school?->name ?? 'Не указана',
                'generated_at' => now()->toISOString(),
            ]
        ]);
    }

    /**
     * Получить статистику по конкретному классу
     */
    public function getClassStatistics(Request $request, int $classId): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        $class = SchoolClass::where('id', $classId)
            ->where('school_id', $schoolId)
            ->first();

        if (!$class) {
            return response()->json(['message' => 'Класс не найден'], 404);
        }

        $students = User::where('school_class_id', $classId)
            ->where('user_type', 'student')
            ->get();

        $totalStudents = $students->count();
        $totalXp = $students->sum('points');
        $avgXp = $totalStudents > 0 ? round($totalXp / $totalStudents, 2) : 0;
        $avgLevel = $totalStudents > 0 ? round($students->avg('level'), 2) : 0;

        $activeStudents = $students->filter(function ($student) {
            return $student->last_activity_at && $student->last_activity_at->diffInDays(now()) <= 7;
        })->count();

        // Топ-5 учеников по XP
        $topStudents = $students->sortByDesc('points')
            ->take(5)
            ->values()
            ->map(function ($student, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $student->id,
                    'name' => $student->name,
                    'xp' => $student->points,
                    'level' => $student->level ?? 0,
                    'level_name' => $student->level_name,
                ];
            });

        return response()->json([
            'success' => true,
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
            ],
            'statistics' => [
                'total_students' => $totalStudents,
                'total_xp' => $totalXp,
                'average_xp' => $avgXp,
                'average_level' => $avgLevel,
                'active_students' => $activeStudents,
                'active_percentage' => $totalStudents > 0
                    ? round(($activeStudents / $totalStudents) * 100, 2)
                    : 0,
            ],
            'top_students' => $topStudents,
        ]);
    }

    /**
     * Получить прогресс конкретного ученика (для ответственного)
     */
    public function getStudentProgress(Request $request, int $userId): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $admin = $request->user();
        $student = User::where('id', $userId)
            ->where('school_id', $admin->school_id)
            ->where('user_type', 'student')
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Ученик не найден'], 404);
        }

        // Получаем прогресс по модулям
        $modulesProgress = [];
        $modules = EducationModule::where('is_published', true)->get();

        foreach ($modules as $module) {
            $progress = $student->moduleProgress()
                ->where('module_id', $module->id)
                ->first();

            $modulesProgress[] = [
                'module_id' => $module->id,
                'module_name' => $module->name,
                'status' => $progress ? $progress->status : 'not_started',
                'progress_percentage' => $progress ? $progress->progress_percentage : 0,
                'is_completed' => $progress ? $progress->is_completed : false,
                'started_at' => $progress?->started_at?->toISOString(),
                'completed_at' => $progress?->completed_at?->toISOString(),
            ];
        }

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'avatar' => $student->avatar,
                'level' => $student->level ?? 0,
                'level_name' => $student->level_name,
                'xp' => $student->points,
                'current_streak' => $student->current_streak ?? 0,
                'max_streak' => $student->max_streak ?? 0,
                'school_class' => $student->schoolClass?->name ?? 'Не указан',
                'last_activity' => $student->last_activity_at?->diffForHumans() ?? 'Никогда',
                'is_online' => $student->is_online,
            ],
            'modules_progress' => $modulesProgress,
            'summary' => [
                'total_modules' => $modules->count(),
                'completed_modules' => collect($modulesProgress)->filter(function ($item) {
                    return $item['is_completed'];
                })->count(),
                'total_xp' => $student->points,
            ]
        ]);
    }

    /**
     * Получить всех учеников школы с их прогрессом
     */
    public function getAllStudents(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        if (!$schoolId) {
            return response()->json(['message' => 'У вас не привязана школа'], 404);
        }

        // Только ученики! Исключаем пользователей с role_id = 6 (ответственный)
        $students = User::where('school_id', $schoolId)
            ->where('user_type', 'student')
            ->where('role_id', '!=', 6) // Исключаем ответственного
            ->with(['schoolClass', 'moduleProgress'])
            ->orderBy('name')
            ->get();

        $totalModules = EducationModule::where('is_published', true)->count();

        $studentsData = $students->map(function ($student) use ($totalModules) {
            $completedModules = $student->moduleProgress
                ->where('status', UserModuleProgress::STATUS_COMPLETED)
                ->count();

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'avatar' => $student->avatar,
                'level' => $student->level ?? 0,
                'xp' => $student->points,
                'class' => $student->schoolClass?->name ?? 'Не указан',
                'class_id' => $student->school_class_id,
                'current_streak' => $student->current_streak ?? 0,
                'completed_modules' => $completedModules,
                'total_modules' => $totalModules,
                'progress_percentage' => $totalModules > 0
                    ? round(($completedModules / $totalModules) * 100, 2)
                    : 0,
                'is_online' => $student->is_online,
                'last_activity' => $student->last_activity_at?->diffForHumans() ?? 'Никогда',
                'last_activity_at' => $student->last_activity_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $studentsData,
            'meta' => [
                'total' => $studentsData->count(),
                'school_id' => $schoolId,
                'school_name' => $user->school?->name ?? 'Не указана',
            ]
        ]);
    }

    /**
     * Получить учителей школы с детальной статистикой
     */
    public function getTeachersWithStats(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        if (!$schoolId) {
            return response()->json(['message' => 'У вас не привязана школа'], 404);
        }

        // Получаем всех учителей школы
        $teachers = User::where('school_id', $schoolId)
            ->where('user_type', 'teacher')
            ->with(['schoolClass' => function ($query) {
                $query->withCount(['students' => function ($q) {
                    $q->where('user_type', 'student');
                }]);
            }])
            ->orderBy('name')
            ->get();

        $totalModules = EducationModule::where('is_published', true)->count();

        $teachersData = $teachers->map(function ($teacher) use ($totalModules) {
            // Классы, которые ведет учитель
            $classes = SchoolClass::where('teacher_id', $teacher->id)
                ->withCount(['students' => function ($q) {
                    $q->where('user_type', 'student');
                }])
                ->get();

            // Все ученики учителя (из всех его классов)
            $classIds = $classes->pluck('id');
            $students = User::whereIn('school_class_id', $classIds)
                ->where('user_type', 'student')
                ->get();

            $studentsCount = $students->count();
            $totalXp = $students->sum('points');
            $avgXp = $studentsCount > 0 ? round($totalXp / $studentsCount, 2) : 0;
            $avgLevel = $studentsCount > 0 ? round($students->avg('level'), 2) : 0;

            // Активные ученики (за 7 дней)
            $activeStudents = $students->filter(function ($student) {
                return $student->last_activity_at && $student->last_activity_at->diffInDays(now()) <= 7;
            })->count();

            // Прогресс по модулям
            $completedModules = UserModuleProgress::where('status', UserModuleProgress::STATUS_COMPLETED)
                ->whereIn('user_id', $students->pluck('id'))
                ->count();

            $totalPossibleCompletions = $studentsCount * $totalModules;
            $classProgressPercentage = $totalPossibleCompletions > 0
                ? round(($completedModules / $totalPossibleCompletions) * 100, 2)
                : 0;

            // Общее время, проведенное учениками (если есть поле time_spent)
            $totalTimeSpent = $students->sum(function ($student) {
                return $student->moduleProgress->sum('time_spent_seconds') ?? 0;
            });

            // Статистика по каждому классу учителя
            $classesStats = $classes->map(function ($class) {
                $classStudents = User::where('school_class_id', $class->id)
                    ->where('user_type', 'student')
                    ->get();

                $classXp = $classStudents->sum('points');
                $classAvgXp = $classStudents->count() > 0
                    ? round($classXp / $classStudents->count(), 2)
                    : 0;

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'students_count' => $class->students_count ?? 0,
                    'total_xp' => $classXp,
                    'average_xp' => $classAvgXp,
                ];
            });

            return [
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'avatar' => $teacher->avatar,
                    'is_online' => $teacher->is_online,
                    'last_activity' => $teacher->last_activity_at?->diffForHumans() ?? 'Никогда',
                ],
                'statistics' => [
                    'total_students' => $studentsCount,
                    'total_xp' => $totalXp,
                    'average_xp' => $avgXp,
                    'average_level' => $avgLevel,
                    'active_students' => $activeStudents,
                    'active_percentage' => $studentsCount > 0
                        ? round(($activeStudents / $studentsCount) * 100, 2)
                        : 0,
                    'completed_modules' => $completedModules,
                    'class_progress_percentage' => $classProgressPercentage,
                    'total_time_spent_hours' => $totalTimeSpent > 0
                        ? round($totalTimeSpent / 3600, 2)
                        : 0,
                ],
                'classes' => $classesStats,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $teachersData,
            'meta' => [
                'total' => $teachersData->count(),
                'school_id' => $schoolId,
                'school_name' => $user->school?->name ?? 'Не указана',
                'total_modules' => $totalModules,
            ]
        ]);
    }

    /**
     * Экспорт данных в CSV
     */
    public function exportData(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен'], 403);
        }

        $user = $request->user();
        $schoolId = $user->school_id;

        if (!$schoolId) {
            return response()->json(['message' => 'У вас не привязана школа'], 404);
        }

        $students = User::where('school_id', $schoolId)
            ->where('user_type', 'student')
            ->with(['schoolClass', 'moduleProgress'])
            ->get();

        $totalModules = EducationModule::where('is_published', true)->count();

        // Формируем CSV данные
        $csvData = [];
        $csvData[] = [
            'ID',
            'Имя',
            'Email',
            'Класс',
            'Уровень',
            'XP',
            'Стрик',
            'Выполнено модулей',
            'Всего модулей',
            'Прогресс %',
            'Последняя активность'
        ];

        foreach ($students as $student) {
            $completedModules = $student->moduleProgress
                ->where('status', UserModuleProgress::STATUS_COMPLETED)
                ->count();

            $csvData[] = [
                $student->id,
                $student->name,
                $student->email,
                $student->schoolClass?->name ?? 'Не указан',
                $student->level ?? 0,
                $student->points,
                $student->current_streak ?? 0,
                $completedModules,
                $totalModules,
                $totalModules > 0 ? round(($completedModules / $totalModules) * 100, 2) : 0,
                $student->last_activity_at?->toDateTimeString() ?? 'Никогда',
            ];
        }

        // Генерируем CSV строку
        $csv = implode("\n", array_map(function ($row) {
            return implode(',', $row);
        }, $csvData));

        return response()->json([
            'success' => true,
            'csv' => $csv,
            'filename' => 'students_export_' . date('Y-m-d') . '.csv',
            'meta' => [
                'total_students' => $students->count(),
                'school_name' => $user->school?->name ?? 'Не указана',
                'generated_at' => now()->toISOString(),
            ]
        ]);
    }
}
