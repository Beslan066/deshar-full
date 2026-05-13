<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Models\Region;
use App\Models\District;
use App\Models\City;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // INDEX - список пользователей
    public function index(Request $request)
    {
        $query = User::with(['country', 'region', 'district', 'city', 'school', 'schoolClass', 'role']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);
        $roles = Role::all();

        return view('admin.user.index', compact('users', 'roles'));
    }

    // CREATE - форма создания
    public function create()
    {
        $countries = Country::all();
        $regions = Region::all();
        $districts = District::all();
        $cities = City::all();
        $schools = School::all();
        $schoolClasses = SchoolClass::all();
        $roles = Role::all();

        return view('admin.user.create', compact('countries', 'regions', 'districts', 'cities', 'schools', 'schoolClasses', 'roles'));
    }

    public function store(Request $request)
    {
        $rules = $this->validationRules();
        // Убираем nullable у password для создания
        $rules['password'] = 'required|string|min:8|confirmed';

        $validated = $request->validate($rules, $this->validationMessages());

        try {
            $validated['password'] = Hash::make($request->password);

            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            User::create($validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'Пользователь успешно создан!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Произошла ошибка при создании пользователя: ' . $e->getMessage())
                ->withInput();
        }
    }


    // EDIT - форма редактирования
    public function edit(User $user)
    {
        $countries = Country::all();
        $regions = Region::all();
        $districts = District::all();
        $cities = City::all();
        $schools = School::all();
        $schoolClasses = SchoolClass::all();
        $roles = Role::all();

        return view('admin.user.edit', compact('user', 'countries', 'regions', 'districts', 'cities', 'schools', 'schoolClasses', 'roles'));
    }

    /**
     * Правила валидации
     */
    protected function validationRules($userId = null)
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id',
            'district_id' => 'nullable|exists:districts,id',
            'city_id' => 'nullable|exists:cities,id',
            'school_id' => 'nullable|exists:schools,id',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'role_id' => 'nullable|exists:roles,id',
            'points' => 'nullable|integer',
            'birth_date' => 'nullable|date',
            'user_type' => 'nullable|in:student,teacher,parent,admin',
        ];
    }

    /**
     * Кастомные сообщения об ошибках
     */
    protected function validationMessages()
    {
        return [
            'name.required' => 'Поле "Имя" обязательно для заполнения.',
            'name.string' => 'Поле "Имя" должно быть строкой.',
            'name.max' => 'Поле "Имя" не может превышать 255 символов.',

            'email.required' => 'Поле "Email" обязательно для заполнения.',
            'email.email' => 'Введите корректный email адрес.',
            'email.unique' => 'Пользователь с таким email уже существует.',

            'password.required' => 'Поле "Пароль" обязательно для заполнения.',
            'password.min' => 'Пароль должен содержать минимум :min символов.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',

            'avatar.image' => 'Файл должен быть изображением.',
            'avatar.mimes' => 'Поддерживаемые форматы: jpg, jpeg, png, gif.',
            'avatar.max' => 'Размер изображения не должен превышать 2MB.',

            'country_id.exists' => 'Выбранная страна не существует.',
            'region_id.exists' => 'Выбранный регион не существует.',
            'district_id.exists' => 'Выбранный район не существует.',
            'city_id.exists' => 'Выбранный город не существует.',
            'school_id.exists' => 'Выбранная школа не существует.',
            'school_class_id.exists' => 'Выбранный класс не существует.',
            'role_id.exists' => 'Выбранная роль не существует.',

            'points.integer' => 'Баллы должны быть целым числом.',

            'birth_date.date' => 'Введите корректную дату рождения.',

            'user_type.required' => 'Тип пользователя обязателен для выбора.',
            'user_type.in' => 'Выбран неверный тип пользователя.',
        ];
    }

    public function update(Request $request, User $user)
    {
        // Валидация с кастомными сообщениями
        $validated = $request->validate(
            $this->validationRules($user->id),
            $this->validationMessages()
        );

        try {
            // Обновляем пароль только если он был указан
            if ($request->filled('password')) {
                $validated['password'] = Hash::make($request->password);
            } else {
                unset($validated['password']);
            }

            // Обновляем аватар
            if ($request->hasFile('avatar')) {
                // Удаляем старый аватар
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $path = $request->file('avatar')->store('avatars', 'public');
                $validated['avatar'] = $path;
            }

            $user->update($validated);

            return redirect()->route('admin.users.index')
                ->with('success', 'Пользователь успешно обновлен!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Произошла ошибка при обновлении пользователя: ' . $e->getMessage())
                ->withInput();
        }
    }

    // DESTROY - удаление пользователя
    public function destroy(User $user)
    {
        // Удаляем аватар если есть
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Пользователь успешно удален');
    }

    // AJAX методы
    public function getRegions($countryId)
    {
        $regions = Region::where('country_id', $countryId)->get();
        return response()->json($regions);
    }

    public function getDistricts($regionId)
    {
        $districts = District::where('region_id', $regionId)->get();
        return response()->json($districts);
    }

    public function getCities($districtId)
    {
        $cities = City::where('district_id', $districtId)->get();
        return response()->json($cities);
    }
}
