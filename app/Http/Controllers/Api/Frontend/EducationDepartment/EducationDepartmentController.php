<?php

namespace App\Http\Controllers\Api\Frontend\EducationDepartment;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EducationDepartmentController extends Controller
{
    /**
     * Проверка доступа для Пр. Управления образования
     */
    private function checkAccess(Request $request): bool
    {
        $user = $request->user();
        return $user && $user->role_id === 5 && !is_null($user->district_id);
    }

    /**
     * Получить информацию о своем районе
     */
    public function myDistrict(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для Пр. Управления образования'], 403);
        }

        $user = $request->user();
        $district = $user->district()->with(['region'])->first();

        if (!$district) {
            return response()->json(['message' => 'Район не найден'], 404);
        }

        $schoolIds = $district->schools()->pluck('id')->toArray();
        $totalStudents = User::whereIn('school_id', $schoolIds)->students()->count();
        $totalPoints = User::whereIn('school_id', $schoolIds)->students()->sum('points');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $district->id,
                'name' => $district->name,
                'region' => $district->region?->name,
                'total_schools' => $district->schools()->count(),
                'total_students' => $totalStudents,
                'total_points' => $totalPoints,
                'average_points' => $totalStudents > 0 ? round($totalPoints / $totalStudents, 2) : 0,
            ]
        ]);
    }

    /**
     * Получить все школы своего района
     */
    public function schools(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для Пр. Управления образования'], 403);
        }

        $user = $request->user();

        $schools = School::where('district_id', $user->district_id)
            ->with(['district', 'region'])
            ->withCount(['students', 'teachers', 'classes'])
            ->get()
            ->map(function ($school) {
                $totalPoints = $school->students()->sum('points');
                $studentsCount = $school->students()->count();

                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'address' => $school->address,
                    'phone' => $school->phone,
                    'email' => $school->email,
                    'district' => $school->district?->name,
                    'region' => $school->region?->name,
                    'total_students' => $school->students_count,
                    'total_teachers' => $school->teachers_count,
                    'total_classes' => $school->classes_count,
                    'total_points' => $totalPoints,
                    'average_points' => $studentsCount > 0 ? round($totalPoints / $studentsCount, 2) : 0,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $schools,
            'meta' => [
                'district_id' => $user->district_id,
                'total_schools' => $schools->count(),
            ]
        ]);
    }

    /**
     * Получить детальную статистику по школе своего района
     */
    public function schoolStats(Request $request, int $schoolId): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для Пр. Управления образования'], 403);
        }

        $user = $request->user();

        $school = School::where('district_id', $user->district_id)
            ->with(['district', 'region', 'classes'])
            ->find($schoolId);

        if (!$school) {
            return response()->json(['message' => 'Школа не найдена или не принадлежит вашему району'], 404);
        }

        $totalStudents = $school->students()->count();
        $totalTeachers = $school->teachers()->count();
        $totalClasses = $school->classes()->count();
        $totalPoints = $school->students()->sum('points');
        $avgPoints = $totalStudents > 0 ? round($totalPoints / $totalStudents, 2) : 0;

        // Топ учеников школы
        $topStudents = $school->students()
            ->with(['schoolClass'])
            ->orderBy('points', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'points' => $student->points,
                    'level' => $student->level,
                    'class' => $student->schoolClass?->name,
                ];
            });

        // Статистика по классам
        $classesStats = $school->classes->map(function ($class) {
            $students = $class->students;
            $totalPoints = $students->sum('points');
            $count = $students->count();

            return [
                'id' => $class->id,
                'name' => $class->name,
                'students_count' => $count,
                'total_points' => $totalPoints,
                'average_points' => $count > 0 ? round($totalPoints / $count, 2) : 0,
            ];
        })->sortByDesc('average_points')->values();

        // Топ учителей
        $topTeachers = $school->teachers()
            ->with(['students'])
            ->get()
            ->map(function ($teacher) {
                $studentsPoints = $teacher->students->sum('points');
                $studentsCount = $teacher->students->count();

                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'students_count' => $studentsCount,
                    'students_total_points' => $studentsPoints,
                    'average_points' => $studentsCount > 0 ? round($studentsPoints / $studentsCount, 2) : 0,
                ];
            })
            ->sortByDesc('average_points')
            ->values()
            ->take(10);

        return response()->json([
            'success' => true,
            'data' => [
                'school' => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'district' => $school->district?->name,
                    'region' => $school->region?->name,
                ],
                'statistics' => [
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_classes' => $totalClasses,
                    'total_points' => $totalPoints,
                    'average_points' => $avgPoints,
                ],
                'top_students' => $topStudents,
                'classes_statistics' => $classesStats,
                'top_teachers' => $topTeachers,
            ],
        ]);
    }

    /**
     * Получить статистику по своему району
     */
    public function districtStats(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для Пр. Управления образования'], 403);
        }

        $user = $request->user();
        $district = $user->district()->with(['region'])->first();

        if (!$district) {
            return response()->json(['message' => 'Район не найден'], 404);
        }

        $schoolIds = $district->schools()->pluck('id')->toArray();

        $totalSchools = count($schoolIds);
        $totalStudents = User::whereIn('school_id', $schoolIds)->students()->count();
        $totalTeachers = User::whereIn('school_id', $schoolIds)->teachers()->count();
        $totalPoints = User::whereIn('school_id', $schoolIds)->students()->sum('points');
        $avgPoints = $totalStudents > 0 ? round($totalPoints / $totalStudents, 2) : 0;

        // Топ школ района
        $topSchools = $district->schools()->withCount(['students'])->get()->map(function ($school) {
            $totalPoints = $school->students()->sum('points');
            $studentsCount = $school->students()->count();

            return [
                'id' => $school->id,
                'name' => $school->name,
                'students_count' => $studentsCount,
                'total_points' => $totalPoints,
                'average_points' => $studentsCount > 0 ? round($totalPoints / $studentsCount, 2) : 0,
            ];
        })->sortByDesc('average_points')->values();

        // Топ учеников района
        $topStudents = User::whereIn('school_id', $schoolIds)
            ->students()
            ->with(['school', 'schoolClass'])
            ->orderBy('points', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'points' => $student->points,
                    'level' => $student->level,
                    'school' => $student->school?->name,
                    'class' => $student->schoolClass?->name,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'district' => [
                    'id' => $district->id,
                    'name' => $district->name,
                    'region' => $district->region?->name,
                ],
                'statistics' => [
                    'total_schools' => $totalSchools,
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_points' => $totalPoints,
                    'average_points' => $avgPoints,
                ],
                'top_schools' => $topSchools,
                'top_students' => $topStudents,
            ],
        ]);
    }

    /**
     * Получить всех учеников района (role_id = 9)
     */
    public function students(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для Пр. Управления образования'], 403);
        }

        $user = $request->user();
        $schoolIds = School::where('district_id', $user->district_id)->pluck('id')->toArray();

        // Фильтруем по role_id = 9 (Ученик)
        $students = User::whereIn('school_id', $schoolIds)
            ->where('role_id', 9) // Только ученики!
            ->with(['school', 'schoolClass'])
            ->orderBy('points', 'desc')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'points' => $student->points,
                    'level' => $student->level,
                    'school' => $student->school?->name,
                    'class' => $student->schoolClass?->name,
                    'tasks_completed' => $student->taskProgress()->where('status', 'completed')->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $students,
            'meta' => [
                'district_id' => $user->district_id,
                'total_students' => $students->count(),
            ]
        ]);
    }

    /**
     * Получить всех учителей района (role_id = 8)
     */
    public function teachers(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для Пр. Управления образования'], 403);
        }

        $user = $request->user();
        $schoolIds = School::where('district_id', $user->district_id)->pluck('id')->toArray();

        // Фильтруем по role_id = 8 (Учитель)
        $teachers = User::whereIn('school_id', $schoolIds)
            ->where('role_id', 8) // Только учителя!
            ->with(['school'])
            ->get()
            ->map(function ($teacher) {
                // Получаем учеников учителя (role_id = 9)
                $students = User::where('teacher_id', $teacher->id)
                    ->where('role_id', 9) // Только ученики!
                    ->get();

                $studentsPoints = $students->sum('points');
                $studentsCount = $students->count();

                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'email' => $teacher->email,
                    'school' => $teacher->school?->name,
                    'students_count' => $studentsCount,
                    'students_total_points' => $studentsPoints,
                    'average_points' => $studentsCount > 0 ? round($studentsPoints / $studentsCount, 2) : 0,
                ];
            })
            ->sortByDesc('average_points')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $teachers,
            'meta' => [
                'district_id' => $user->district_id,
                'total_teachers' => $teachers->count(),
            ]
        ]);
    }
}
