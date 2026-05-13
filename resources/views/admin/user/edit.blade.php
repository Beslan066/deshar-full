@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Вывод flash сообщений -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Вывод ошибок валидации -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card mb-6">
                <h5 class="card-header">Редактирование пользователя: {{ $user->name }}</h5>
                <form class="card-body" action="{{ route('admin.users.update', $user->id) }}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-6">
                        <!-- Имя -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Иван Иванов"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       required>
                                <label>Имя</label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="email@example.com"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       required>
                                <label>Email</label>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Пароль -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password">
                                <label>Новый пароль (оставьте пустым, если не меняете)</label>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Минимум 8 символов</small>
                        </div>

                        <!-- Подтверждение пароля -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="password"
                                       class="form-control"
                                       name="password_confirmation">
                                <label>Подтверждение пароля</label>
                            </div>
                        </div>

                        <!-- Роль -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('role_id') is-invalid @enderror" name="role_id">
                                    <option value="">Выберите роль</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Роль</label>
                                @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Страна -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('country_id') is-invalid @enderror" name="country_id" id="country_id">
                                    <option value="">Выберите страну</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id', $user->country_id) == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Страна</label>
                                @error('country_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Регион -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="region_id" id="region_id">
                                    <option value="">Выберите регион</option>
                                </select>
                                <label>Регион</label>
                            </div>
                        </div>

                        <!-- Район -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="district_id" id="district_id">
                                    <option value="">Выберите район</option>
                                </select>
                                <label>Район</label>
                            </div>
                        </div>

                        <!-- Город -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" name="city_id" id="city_id">
                                    <option value="">Выберите город</option>
                                </select>
                                <label>Город</label>
                            </div>
                        </div>

                        <!-- Школа -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('school_id') is-invalid @enderror" name="school_id">
                                    <option value="">Выберите школу</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Школа</label>
                                @error('school_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Класс -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select @error('school_class_id') is-invalid @enderror" name="school_class_id">
                                    <option value="">Выберите класс</option>
                                    @foreach($schoolClasses as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_id', $user->school_class_id) == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label>Класс</label>
                                @error('school_class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Баллы -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="number"
                                       class="form-control @error('points') is-invalid @enderror"
                                       name="points"
                                       value="{{ old('points', $user->points) }}">
                                <label>Баллы</label>
                                @error('points')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Дата рождения -->
                        <div class="col-md-6 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="date"
                                       class="form-control @error('birth_date') is-invalid @enderror"
                                       name="birth_date"
                                       value="{{ old('birth_date', $user->birth_date ? date('Y-m-d', strtotime($user->birth_date)) : '') }}">
                                <label>Дата рождения</label>
                                @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Текущий аватар -->
                        @if($user->avatar)
                            <div class="col-12 mb-6 mt-4">
                                <label class="form-label">Текущий аватар</label>
                                <div>
                                    <img src="{{ asset('storage/' . $user->avatar) }}"
                                         alt="avatar" class="rounded-circle"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            </div>
                        @endif

                        <!-- Новый аватар -->
                        <div class="col-12 mb-6 mt-4">
                            <div class="form-floating form-floating-outline">
                                <input type="file"
                                       class="form-control @error('avatar') is-invalid @enderror"
                                       name="avatar" accept="image/*">
                                <label>Новый аватар (оставьте пустым, если не меняете)</label>
                                @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Рекомендуемый размер: 512x512px. Макс. 2MB</small>
                        </div>

                        <!-- Кнопки -->
                        <div class="mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary waves-effect">
                                Отмена
                            </a>
                            <button type="submit" class="btn btn-primary me-4 waves-effect waves-light">
                                Обновить
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
                const districtSelect = document.getElementById('district_id');
                const citySelect = document.getElementById('city_id');

                districtSelect.innerHTML = '<option value="">Выберите район</option>';
                citySelect.innerHTML = '<option value="">Выберите город</option>';

                if (countryId) {
                    fetch(`/admin/users/regions/${countryId}`)
                        .then(response => response.json())
                        .then(data => {
                            regionSelect.innerHTML = '<option value="">Выберите регион</option>';
                            data.forEach(region => {
                                regionSelect.innerHTML += `<option value="${region.id}">${region.name}</option>`;
                            });
                            regionSelect.disabled = false;
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    regionSelect.innerHTML = '<option value="">Выберите регион</option>';
                    regionSelect.disabled = true;
                }
            });

            // Динамическая загрузка районов
            document.getElementById('region_id').addEventListener('change', function() {
                const regionId = this.value;
                const districtSelect = document.getElementById('district_id');
                const citySelect = document.getElementById('city_id');

                citySelect.innerHTML = '<option value="">Выберите город</option>';

                if (regionId) {
                    fetch(`/admin/users/districts/${regionId}`)
                        .then(response => response.json())
                        .then(data => {
                            districtSelect.innerHTML = '<option value="">Выберите район</option>';
                            data.forEach(district => {
                                districtSelect.innerHTML += `<option value="${district.id}">${district.name}</option>`;
                            });
                            districtSelect.disabled = false;
                        })
                        .catch(error => console.error('Error:', error));
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
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    citySelect.innerHTML = '<option value="">Выберите город</option>';
                    citySelect.disabled = true;
                }
            });

            // Инициализация: если страна выбрана, загружаем регионы
            @if(old('country_id', $user->country_id))
            document.getElementById('country_id').dispatchEvent(new Event('change'));

            @if(old('region_id', $user->region_id))
            setTimeout(() => {
                document.getElementById('region_id').dispatchEvent(new Event('change'));
            }, 100);

            @if(old('district_id', $user->district_id))
            setTimeout(() => {
                document.getElementById('district_id').dispatchEvent(new Event('change'));
            }, 200);
            @endif
            @endif
            @endif
        </script>
    @endpush
@endsection
