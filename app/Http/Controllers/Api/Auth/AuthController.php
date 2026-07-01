<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Zа-яА-Я\s\-]+$/u',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed'
            ],
            'role_id' => 'required|integer|exists:roles,id',
            'country_id' => 'required|integer|exists:countries,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'region_id' => 'nullable|integer|exists:regions,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'birth_date' => 'nullable|date|before:today',
            'user_type' => 'nullable|string',
            'confirmed' => 'nullable|boolean',
            'school_id' => 'required|integer|exists:schools,id',
            'school_class_id' => 'required|integer|exists:school_classes,id',
        ]);

        // Подготовка данных
        $userData = [
            'name' => strip_tags($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'country_id' => $request->country_id,
            'school_id' => $request->school_id,
            'school_class_id' => $request->school_class_id,
            'user_type' => $request->user_type ?? 'student',
            'level' => 0,
            'points' => 0,
            'is_active' => true,
            'is_banned' => false,
            'confirmed' => $request->has('confirmed') ? (bool)$request->confirmed : false,
            'current_streak' => 0,
            'max_streak' => 0,
        ];

        // Добавляем необязательные поля только если они есть в запросе
        $optionalFields = ['city_id', 'region_id', 'district_id', 'birth_date'];
        foreach ($optionalFields as $field) {
            if ($request->has($field) && !is_null($request->$field)) {
                $userData[$field] = $request->$field;
            }
        }

        // Логирование для отладки
        \Log::info('Creating user with data:', $userData);

        $user = User::create($userData);

        // Проверка сохраненных данных
        \Log::info('User created:', $user->toArray());

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Регистрация успешно завершена',
            'user' => $user->only(['id', 'name', 'email', 'user_type', 'level', 'points', 'city_id', 'region_id', 'district_id', 'birth_date', 'confirmed']),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Failed login attempt', [
                'email' => $request->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль'],
            ]);
        }

        // Проверка на бан
        if ($user->is_banned) {
            Log::warning('Banned user attempted login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['Ваш аккаунт заблокирован' . ($user->banned_until ? ' до ' . $user->banned_until->format('d.m.Y') : '')],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Ваш аккаунт деактивирован. Обратитесь к администратору.'],
            ]);
        }

        // Удаляем старые токены
        $user->tokens()->delete();

        // Записываем вход
        $user->last_login_at = now();
        $user->last_activity_at = now();
        $user->save();

        // Создаем токен с ограниченными правами
        $token = $user->createToken('auth_token', [
            'user:read',
            'profile:update',
        ], now()->addDays(7))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Вход выполнен успешно',
            'user' => $user->only(['id', 'name', 'email', 'avatar', 'user_type', 'level', 'points']),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Выход выполнен успешно',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $user->only([
                'id', 'name', 'email', 'avatar', 'user_type',
                'level', 'points', 'current_streak', 'max_streak',
                'is_active', 'is_banned', 'last_activity_at'
            ]),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255|regex:/^[a-zA-Zа-яА-Я\s\-]+$/u',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|string|url|max:255',
            'birth_date' => 'nullable|date|before:today',
        ]);

        $user->update($request->only(['name', 'email', 'avatar', 'birth_date']));

        return response()->json([
            'success' => true,
            'message' => 'Профиль обновлен',
            'user' => $user->fresh()->only([
                'id', 'name', 'email', 'avatar', 'user_type',
                'level', 'points', 'current_streak', 'max_streak'
            ]),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => [
                'required',
                'string',
                PasswordRule::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                'confirmed'
            ],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Текущий пароль неверен'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Удаляем все токены кроме текущего
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Пароль успешно обновлен',
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'stats' => [
                'level' => $user->level ?? 0,
                'xp' => $user->points ?? 0,
                'current_streak' => $user->current_streak ?? 0,
                'max_streak' => $user->max_streak ?? 0,
                'total_tasks_completed' => $user->total_tasks_completed ?? 0,
                'total_lessons_completed' => $user->total_lessons_completed ?? 0,
            ],
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Мягкое удаление
        $user->is_active = false;
        $user->deleted_at = now();
        $user->save();

        // Удаляем все токены
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Аккаунт успешно деактивирован',
        ]);
    }
}
