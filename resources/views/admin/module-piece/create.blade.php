@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <h5 class="card-header">Создание раздела</h5>

                @if($errors->any())
                    <div class="card-body">
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <h6 class="alert-heading mb-1">Пожалуйста, исправьте следующие ошибки:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <form class="card-body" action="{{ route('admin.educationModulesPieces.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-6">
                        {{-- НАЗВАНИЕ --}}
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Например: Алфавит" name="name" value="{{ old('name') }}" required>
                                <label for="name">Название раздела <span class="text-danger">*</span></label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- МОДУЛЬ --}}
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select id="education_module_id" class="form-select @error('education_module_id') is-invalid @enderror" name="education_module_id" required>
                                    <option value="">Выберите модуль</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module->id }}" {{ old('education_module_id') == $module->id ? 'selected' : '' }}>
                                            {{ $module->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="education_module_id">Модуль <span class="text-danger">*</span></label>
                                @error('education_module_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ПОРЯДОК --}}
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       placeholder="0" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                <label for="sort_order">Порядок сортировки</label>
                                @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Чем меньше число, тем выше в списке</small>
                            </div>
                        </div>

                        {{-- XP НАГРАДА --}}
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="xp_reward" class="form-control @error('xp_reward') is-invalid @enderror"
                                       placeholder="10" name="xp_reward" value="{{ old('xp_reward', 10) }}" min="0">
                                <label for="xp_reward">XP награда</label>
                                @error('xp_reward')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ВРЕМЯ --}}
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="estimated_time" class="form-control @error('estimated_time') is-invalid @enderror"
                                       placeholder="30" name="estimated_time" value="{{ old('estimated_time', 30) }}" min="1">
                                <label for="estimated_time">Время (минут)</label>
                                @error('estimated_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ОПИСАНИЕ --}}
                        <div class="col-12">
                            <div class="form-floating form-floating-outline mb-4">
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Описание раздела" name="description" rows="3">{{ old('description') }}</textarea>
                                <label for="description">Описание</label>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ФОНОВОЕ ИЗОБРАЖЕНИЕ --}}
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="fon" class="form-label">Фоновое изображение</label>
                                <input type="file" id="fon" class="form-control @error('fon') is-invalid @enderror"
                                       name="fon" accept="image/*">
                                @error('fon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Максимальный размер: 2MB. Форматы: jpg, jpeg, png, gif, webp</small>
                            </div>
                        </div>

                        {{-- СТАТУСЫ --}}
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-4 mt-2">
                                <input class="form-check-input" type="checkbox" id="is_published"
                                       name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Опубликовать</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="is_required"
                                       name="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_required">Обязательный для прохождения</label>
                            </div>
                            <small class="text-muted text-warning">
                                ⚠️ Если не поставить галочку "Опубликовать", раздел будет сохранен как черновик
                            </small>
                        </div>

                        {{-- КНОПКИ --}}
                        <div class="col-12">
                            <a href="{{ route('admin.educationModulesPieces.index') }}" class="btn btn-outline-secondary waves-effect">Отмена</a>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Создать раздел</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
