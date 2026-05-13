@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card mb-6">
                <h5 class="card-header">Создание пользователя</h5>
                <form class="card-body" action="{{ route('admin.users.store') }}" method="post"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="row g-6">
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Иван Иванов" name="name" value="{{ old('name') }}" required>
                                <label>Имя</label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       placeholder="email@example.com" name="email" value="{{ old('email') }}" required>
                                <label>Email</label>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       name="password" required>
                                <label>Пароль</label>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="password" class="form-control"
                                       name="password_confirmation" required>
                                <label>Подтверждение пароля</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('user_type') is-invalid @enderror"
                                        name="user_type" required>
                                    <option value="student" {{ old('user_type') == 'student' ? 'selected' : '' }}>Ученик</option>
                                    <option value="teacher" {{ old('user_type') == 'teacher' ? 'selected' : '' }}>Учитель</option>
                                    <option value="parent" {{ old('user_type') == 'parent' ? 'selected' : '' }}>Родитель</option>
                                    <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Администратор</option>
                                </select>
                                <label>Тип пользователя</label>
                                @error('user_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="role_id">
                                    <option value="">Выберите роль</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Роль</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="country_id" id="country_id">
                                    <option value="">Выберите страну</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Страна</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="region_id" id="region_id">
                                    <option value="">Выберите регион</option>
                                </select>
                                <label>Регион</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="district_id" id="district_id">
                                    <option value="">Выберите район</option>
                                </select>
                                <label>Район</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="city_id" id="city_id">
                                    <option value="">Выберите город</option>
                                </select>
                                <label>Город</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="school_id">
                                    <option value="">Выберите школу</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Школа</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="school_class_id">
                                    <option value="">Выберите класс</option>
                                    @foreach($schoolClasses as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Класс</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" name="points"
                                       value="{{ old('points', 0) }}">
                                <label>Баллы</label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="date" class="form-control" name="birth_date"
                                       value="{{ old('birth_date') }}">
                                <label>Дата рождения</label>
                            </div>
                        </div>

                        <div class="col-12 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                       name="avatar" accept="image/*">
                                <label>Аватар</label>
                                @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Рекомендуемый размер: 512x512px. Макс. 2MB</small>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary waves-effect">
                                Отмена
                            </a>
                            <button type="submit" class="btn btn-primary me-4 waves-effect waves-light">
                                Создать
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Динамическая загрузка регионов
            document.getElementById('country_id').addEventListener('change', function() {
                const countryId = this.value;
                const regionSelect = document.getElementById('region_id');

                if (countryId) {
                    fetch(`/admin/users/regions/${countryId}`)
                        .then(response => response.json())
                        .then(data => {
                            regionSelect.innerHTML = '<option value="">Выберите регион</option>';
                            data.forEach(region => {
                                regionSelect.innerHTML += `<option value="${region.id}">${region.name}</option>`;
                            });
                            regionSelect.disabled = false;
                        });
                } else {
                    regionSelect.innerHTML = '<option value="">Выберите регион</option>';
                    regionSelect.disabled = true;
                }
            });

            // Динамическая загрузка районов
            document.getElementById('region_id').addEventListener('change', function() {
                const regionId = this.value;
                const districtSelect = document.getElementById('district_id');

                if (regionId) {
                    fetch(`/admin/users/districts/${regionId}`)
                        .then(response => response.json())
                        .then(data => {
                            districtSelect.innerHTML = '<option value="">Выберите район</option>';
                            data.forEach(district => {
                                districtSelect.innerHTML += `<option value="${district.id}">${district.name}</option>`;
                            });
                            districtSelect.disabled = false;
                        });
                } else {
                    districtSelect.innerHTML = '<option value="">Выберите район</option>';
                    districtSelect.disabled = true;
                }
            });

            // Динамическая загрузка городов
            document.getElementById('district_id').addEventListener('change', function() {
                const districtId = this.value;
                const citySelect = document.getElementById('city_id');

                if (districtId) {
                    fetch(`/admin/users/cities/${districtId}`)
                        .then(response => response.json())
                        .then(data => {
                            citySelect.innerHTML = '<option value="">Выберите город</option>';
                            data.forEach(city => {
                                citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                            });
                            citySelect.disabled = false;
                        });
                } else {
                    citySelect.innerHTML = '<option value="">Выберите город</option>';
                    citySelect.disabled = true;
                }
            });
        </script>
    @endpush
@endsection
