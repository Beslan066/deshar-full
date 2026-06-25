@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <h5 class="card-header">Редактирование модуля</h5>

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

                <form class="card-body" action="{{ route('admin.educationModules.update', $educationModule) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="row g-6">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Например: Ингушский язык" name="name" value="{{ old('name', $educationModule->name) }}" required>
                                <label for="name">Название модуля <span class="text-danger">*</span></label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select id="school_class_type_id" class="form-select @error('school_class_type_id') is-invalid @enderror" name="school_class_type_id" required>
                                    <option value="">Выберите класс</option>
                                    @foreach($schoolClassTypes as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_type_id', $educationModule->school_class_type_id) == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="school_class_type_id">Для какого класса <span class="text-danger">*</span></label>
                                @error('school_class_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="complexity" class="form-control @error('complexity') is-invalid @enderror"
                                       placeholder="1-5" name="complexity" value="{{ old('complexity', $educationModule->complexity ?? 1) }}" min="1" max="5">
                                <label for="complexity">Сложность (1-5)</label>
                                @error('complexity')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       placeholder="0" name="sort_order" value="{{ old('sort_order', $educationModule->sort_order ?? 0) }}">
                                <label for="sort_order">Порядок сортировки</label>
                                @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline mb-4">
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Описание модуля" name="description" rows="3">{{ old('description', $educationModule->description) }}</textarea>
                                <label for="description">Описание</label>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="image" class="form-label">Обложка модуля</label>
                                @if($educationModule->image)
                                    <div class="mb-2">
                                        <img src="{{ $educationModule->image }}" alt="Текущая обложка" width="100" class="img-thumbnail">
                                        <br>
                                        <small class="text-muted">Текущая обложка</small>
                                    </div>
                                @endif
                                <input type="file" id="image" class="form-control @error('image') is-invalid @enderror" name="image" accept="image/*">
                                @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Оставьте пустым, чтобы не менять. Максимальный размер: 2MB.</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mb-4 mt-2">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', $educationModule->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Опубликовать</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <a href="{{ route('admin.educationModules.index') }}" class="btn btn-outline-secondary waves-effect">Отмена</a>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Обновить модуль</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
