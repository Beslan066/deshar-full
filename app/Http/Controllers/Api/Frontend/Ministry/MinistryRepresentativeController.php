<?php

namespace App\Http\Controllers\Api\Frontend\Ministry;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MinistryRepresentativeController extends Controller
{
    /**
     * Проверка доступа для представителя министерства
     */
    private function checkAccess(Request $request): bool
    {
        $user = $request->user();
        return $user && $user->role_id === 4;
    }

    /**
     * Получить все районы
     */
    public function districts(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для представителя министерства'], 403);
        }

        $districts = District::with(['region'])
            ->withCount(['schools'])
            ->get()
            ->map(function ($district) {
                $schoolIds = $district->schools()->pluck('id')->toArray();
                $totalStudents = User::whereIn('school_id', $schoolIds)->students()->count();
                $totalPoints = User::whereIn('school_id', $schoolIds)->students()->sum('points');

                return [
                    'id' => $district->id,
                    'name' => $district->name,
                    'region' => $district->region?->name,
                    'total_schools' => $district->schools_count,
                    'total_students' => $totalStudents,
                    'total_points' => $totalPoints,
                    'average_points' => $totalStudents > 0 ? round($totalPoints / $totalStudents, 2) : 0,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $districts,
            'meta' => [
                'total_districts' => $districts->count(),
            ]
        ]);
    }

    /**
     * Получить все школы по всем районам
     */
    public function schools(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для представителя министерства'], 403);
        }

        // Фильтр по району (опционально)
        $districtId = $request->query('district_id');

        $query = School::with(['district', 'region'])
            ->withCount(['students', 'teachers', 'classes']);

        if ($districtId) {
            $query->where('district_id', $districtId);
        }

        $schools = $query->get()->map(function ($school) {
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
                'total_schools' => $schools->count(),
                'filter_district_id' => $districtId,
            ]
        ]);
    }

    /**
     * Получить детальную статистику по школе
     */
    public function schoolStats(Request $request, int $schoolId): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для представителя министерства'], 403);
        }

        $school = School::with(['district', 'region', 'classes'])
            ->find($schoolId);

        if (!$school) {
            return response()->json(['message' => 'Школа не найдена'], 404);
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
                    'tasks_completed' => $student->taskProgress()->where('status', 'completed')->count(),
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

        // Топ учителей по баллам учеников
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
     * Получить общую статистику по району
     */
    public function districtStats(Request $request, int $districtId): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для представителя министерства'], 403);
        }

        $district = District::with(['region'])->find($districtId);

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
     * Получить общую статистику по всей республике
     */
    public function republicStats(Request $request): JsonResponse
    {
        if (!$this->checkAccess($request)) {
            return response()->json(['message' => 'Доступ запрещен. Только для представителя министерства'], 403);
        }

        $totalSchools = School::count();
        $totalStudents = User::students()->count();
        $totalTeachers = User::teachers()->count();
        $totalPoints = User::students()->sum('points');
        $avgPoints = $totalStudents > 0 ? round($totalPoints / $totalStudents, 2) : 0;

        // Топ районов по баллам
        $topDistricts = District::withCount(['schools'])->get()->map(function ($district) {
            $schoolIds = $district->schools()->pluck('id')->toArray();
            $totalPoints = User::whereIn('school_id', $schoolIds)->students()->sum('points');
            $studentsCount = User::whereIn('school_id', $schoolIds)->students()->count();

            return [
                'id' => $district->id,
                'name' => $district->name,
                'total_schools' => $district->schools_count,
                'total_students' => $studentsCount,
                'total_points' => $totalPoints,
                'average_points' => $studentsCount > 0 ? round($totalPoints / $studentsCount, 2) : 0,
            ];
        })->sortByDesc('average_points')->values();

        // Топ школ республики
        $topSchools = School::withCount(['students'])->get()->map(function ($school) {
            $totalPoints = $school->students()->sum('points');
            $studentsCount = $school->students()->count();

            return [
                'id' => $school->id,
                'name' => $school->name,
                'district' => $school->district?->name,
                'students_count' => $studentsCount,
                'total_points' => $totalPoints,
                'average_points' => $studentsCount > 0 ? round($totalPoints / $studentsCount, 2) : 0,
            ];
        })->sortByDesc('average_points')->values()->take(10);

        // Топ учеников республики
        $topStudents = User::students()
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
                'statistics' => [
                    'total_schools' => $totalSchools,
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_points' => $totalPoints,
                    'average_points' => $avgPoints,
                ],
                'top_districts' => $topDistricts,
                'top_schools' => $topSchools,
                'top_students' => $topStudents,
            ],
        ]);
    }
}
