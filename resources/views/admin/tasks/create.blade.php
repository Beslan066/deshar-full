@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <h5 class="card-header">Создание задания</h5>

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

                <form class="card-body" action="{{ route('admin.tasks.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="config" value="">

                    <div class="row g-6">
                        {{-- ОСНОВНЫЕ ПОЛЯ --}}
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select id="lesson_id" class="form-select @error('lesson_id') is-invalid @enderror" name="lesson_id" required>
                                    <option value="">Выберите урок</option>
                                    @foreach($lessons as $lesson)
                                        <option value="{{ $lesson->id }}" {{ (old('lesson_id', $selectedLessonId ?? '')) == $lesson->id ? 'selected' : '' }}>
                                            {{ $lesson->piece?->name ?? '' }} → {{ $lesson->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="lesson_id">Урок <span class="text-danger">*</span></label>
                                @error('lesson_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select id="task_type_id" class="form-select @error('task_type_id') is-invalid @enderror" name="task_type_id" required>
                                    <option value="">Выберите тип</option>
                                    @foreach($taskTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('task_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="task_type_id">Тип задания <span class="text-danger">*</span></label>
                                @error('task_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" id="title" class="form-control @error('title') is-invalid @enderror"
                                       placeholder="Заголовок" name="title" value="{{ old('title') }}">
                                <label for="title">Заголовок</label>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       placeholder="0" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                <label for="sort_order">Порядок</label>
                                @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="xp_reward" class="form-control @error('xp_reward') is-invalid @enderror"
                                       placeholder="10" name="xp_reward" value="{{ old('xp_reward', 10) }}" min="0">
                                <label for="xp_reward">XP</label>
                                @error('xp_reward')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="max_attempts" class="form-control @error('max_attempts') is-invalid @enderror"
                                       placeholder="3" name="max_attempts" value="{{ old('max_attempts', 3) }}" min="1">
                                <label for="max_attempts">Попыток</label>
                                @error('max_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="time_limit_seconds" class="form-control @error('time_limit_seconds') is-invalid @enderror"
                                       placeholder="0" name="time_limit_seconds" value="{{ old('time_limit_seconds', 0) }}" min="0">
                                <label for="time_limit_seconds">Лимит времени (сек)</label>
                                @error('time_limit_seconds')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">0 - без лимита</small>
                            </div>
                        </div>

                        {{-- ОПИСАНИЕ --}}
                        <div class="col-12">
                            <div class="form-floating form-floating-outline mb-4">
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Описание" name="description" rows="3">{{ old('description') }}</textarea>
                                <label for="description">Описание</label>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- МЕДИАФАЙЛЫ (АУДИО, ФОТО, ВИДЕО) --}}
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Медиафайлы для задания</h6>
                                    <small class="text-muted">Добавьте аудио, изображение или видео для задания</small>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        {{-- АУДИО --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="audio_file" class="form-label">
                                                    <i class="bx bx-music me-1"></i> Аудио
                                                </label>
                                                <input type="file" id="audio_file" class="form-control @error('audio_file') is-invalid @enderror"
                                                       name="audio_file" accept="audio/*">
                                                @error('audio_file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">MP3, WAV, OGG. Макс. 10MB</small>
                                            </div>
                                            <div id="audio-preview" class="mt-2 d-none">
                                                <audio controls class="w-100">
                                                    <source src="" type="audio/mpeg">
                                                </audio>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="removeFile('audio')">
                                                    <i class="bx bx-trash"></i> Удалить
                                                </button>
                                            </div>
                                        </div>

                                        {{-- ФОТО --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="image_file" class="form-label">
                                                    <i class="bx bx-image me-1"></i> Изображение
                                                </label>
                                                <input type="file" id="image_file" class="form-control @error('image_file') is-invalid @enderror"
                                                       name="image_file" accept="image/*">
                                                @error('image_file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">JPG, PNG, GIF, WebP. Макс. 5MB</small>
                                            </div>
                                            <div id="image-preview" class="mt-2 d-none">
                                                <img src="" alt="Превью" class="img-thumbnail" style="max-height: 150px;">
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="removeFile('image')">
                                                    <i class="bx bx-trash"></i> Удалить
                                                </button>
                                            </div>
                                        </div>

                                        {{-- ВИДЕО --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="video_file" class="form-label">
                                                    <i class="bx bx-video me-1"></i> Видео
                                                </label>
                                                <input type="file" id="video_file" class="form-control @error('video_file') is-invalid @enderror"
                                                       name="video_file" accept="video/*">
                                                @error('video_file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">MP4, WebM, OGG. Макс. 50MB</small>
                                            </div>
                                            <div id="video-preview" class="mt-2 d-none">
                                                <video controls class="w-100" style="max-height: 150px;">
                                                    <source src="" type="video/mp4">
                                                </video>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="removeFile('video')">
                                                    <i class="bx bx-trash"></i> Удалить
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ПОДСКАЗКИ --}}
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Подсказки</h6>
                                    <button type="button" id="add-hint" class="btn btn-sm btn-primary">
                                        <i class="bx bx-plus"></i> Добавить
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="hints-container">
                                        <div class="input-group mb-2 hint-group">
                                            <input type="text" class="form-control" name="hints[]" placeholder="Подсказка 1">
                                            <button type="button" class="btn btn-outline-danger remove-hint">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- КОНФИГ ЗАДАНИЯ --}}
                        <div class="col-12" id="config-container">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-1"></i>
                                Выберите тип задания, чтобы настроить его конфигурацию
                            </div>
                        </div>

                        {{-- СТАТУСЫ --}}
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="is_published"
                                       name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Опубликовать</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="is_required"
                                       name="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_required">Обязательное</label>
                            </div>
                        </div>

                        {{-- КНОПКИ --}}
                        <div class="col-12">
                            <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary waves-effect">Отмена</a>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Создать задание</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .config-card {
            border: 1px solid #e7e7e7;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .config-card:hover {
            border-color: #696cff;
            box-shadow: 0 2px 8px rgba(105, 108, 255, 0.1);
        }
        .config-card .card-header {
            background: #f8f9fa;
            border-bottom: 1px solid #e7e7e7;
            padding: 0.75rem 1.25rem;
        }
        .config-field-group {
            margin-bottom: 1rem;
        }
        .config-field-group label {
            font-weight: 500;
            font-size: 0.85rem;
            color: #566a7f;
            margin-bottom: 0.25rem;
            display: block;
        }
        .config-field-group .form-control,
        .config-field-group .form-select {
            background: #fff;
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.9rem;
            transition: border-color 0.15s ease-in-out;
        }
        .config-field-group .form-control:focus,
        .config-field-group .form-select:focus {
            border-color: #696cff;
            box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.1);
        }
        .option-card {
            background: #f8f9fa;
            border: 1px solid #e7e7e7;
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .option-card:hover {
            border-color: #696cff;
        }
        .option-card .option-letter {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #696cff;
            color: white;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        .option-card .option-text {
            flex: 1;
        }
        .option-card .option-text input {
            background: white;
            border: 1px solid #d9dee3;
            border-radius: 0.25rem;
            padding: 0.4rem 0.6rem;
            width: 100%;
            font-size: 0.9rem;
        }
        .option-card .option-text input:focus {
            border-color: #696cff;
            outline: none;
        }
        .option-card .option-correct {
            flex-shrink: 0;
        }
        .option-card .option-correct label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
            color: #566a7f;
        }
        .option-card .option-correct input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #696cff;
        }
        .pair-card {
            background: #f8f9fa;
            border: 1px solid #e7e7e7;
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 0.75rem;
            align-items: center;
        }
        .pair-card .pair-label {
            font-weight: 500;
            font-size: 0.8rem;
            color: #566a7f;
            display: block;
            margin-bottom: 0.2rem;
        }
        .pair-card input,
        .pair-card select {
            background: white;
            border: 1px solid #d9dee3;
            border-radius: 0.25rem;
            padding: 0.4rem 0.6rem;
            width: 100%;
            font-size: 0.9rem;
        }
        .pair-card input:focus,
        .pair-card select:focus {
            border-color: #696cff;
            outline: none;
        }
        .btn-add-item {
            border: 2px dashed #d9dee3;
            background: transparent;
            border-radius: 0.375rem;
            padding: 0.75rem;
            width: 100%;
            color: #566a7f;
            transition: all 0.2s;
            cursor: pointer;
        }
        .btn-add-item:hover {
            border-color: #696cff;
            color: #696cff;
            background: rgba(105, 108, 255, 0.05);
        }
        .btn-remove-item {
            background: transparent;
            border: none;
            color: #ff4d4f;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        .btn-remove-item:hover {
            background: rgba(255, 77, 79, 0.1);
        }
        .json-preview {
            background: #1e1e1e;
            color: #d4d4d4;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            padding: 0.75rem;
            border-radius: 0.375rem;
            max-height: 200px;
            overflow: auto;
            margin-top: 0.5rem;
            display: none;
        }
        .json-preview.active {
            display: block;
        }
    </style>

    @push('scripts')
        <script src="{{asset('assets/js/admin/tasks-render.js')}}"></script>
    @endpush
@endsection
