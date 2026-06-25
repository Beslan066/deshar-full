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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // ============================================================
                // ПРЕДПРОСМОТР МЕДИАФАЙЛОВ
                // ============================================================

                // Аудио
                document.getElementById('audio_file')?.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        const preview = document.getElementById('audio-preview');
                        preview.querySelector('audio source').src = url;
                        preview.querySelector('audio').load();
                        preview.classList.remove('d-none');
                    }
                });

                // Изображение
                document.getElementById('image_file')?.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        const preview = document.getElementById('image-preview');
                        preview.querySelector('img').src = url;
                        preview.classList.remove('d-none');
                    }
                });

                // Видео
                document.getElementById('video_file')?.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const url = URL.createObjectURL(file);
                        const preview = document.getElementById('video-preview');
                        preview.querySelector('video source').src = url;
                        preview.querySelector('video').load();
                        preview.classList.remove('d-none');
                    }
                });

                // ============================================================
                // УДАЛЕНИЕ МЕДИАФАЙЛОВ
                // ============================================================
                window.removeFile = function(type) {
                    const preview = document.getElementById(type + '-preview');
                    const input = document.getElementById(type + '_file');
                    if (preview) {
                        preview.classList.add('d-none');
                    }
                    if (input) {
                        input.value = '';
                    }
                    if (type === 'audio' && preview) {
                        preview.querySelector('audio source').src = '';
                    } else if (type === 'image' && preview) {
                        preview.querySelector('img').src = '';
                    } else if (type === 'video' && preview) {
                        preview.querySelector('video source').src = '';
                    }
                };

                // ============================================================
                // ПОДСКАЗКИ
                // ============================================================
                document.getElementById('add-hint')?.addEventListener('click', function() {
                    const container = document.getElementById('hints-container');
                    if (!container) return;
                    const count = container.querySelectorAll('.hint-group').length + 1;
                    const div = document.createElement('div');
                    div.className = 'input-group mb-2 hint-group';
                    div.innerHTML = `
            <input type="text" class="form-control" name="hints[]" placeholder="Подсказка ${count}">
            <button type="button" class="btn btn-outline-danger remove-hint">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                });

                document.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-hint')) {
                        const group = e.target.closest('.hint-group');
                        if (document.querySelectorAll('.hint-group').length > 1) {
                            group.remove();
                        }
                    }
                });

                // ============================================================
                // ДИНАМИЧЕСКИЙ КОНФИГ С ВИЗУАЛЬНЫМ РЕДАКТОРОМ
                // ============================================================
                document.getElementById('task_type_id')?.addEventListener('change', function() {
                    const typeId = this.value;
                    const container = document.getElementById('config-container');
                    const typeName = this.selectedOptions[0]?.text || '';

                    if (!typeId) {
                        container.innerHTML = `
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Выберите тип задания, чтобы настроить его конфигурацию
                </div>
            `;
                        return;
                    }

                    fetch(`{{ route('admin.tasks.defaultConfig') }}?task_type_id=${typeId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                                return;
                            }

                            const config = data.config || {};
                            renderConfigByType(typeId, typeName, config, container);
                        })
                        .catch(error => {
                            container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bx bx-error me-1"></i>
                        Ошибка загрузки конфига
                    </div>
                `;
                            console.error('Error:', error);
                        });
                });

                // ============================================================
                // РЕНДЕРИНГ КОНФИГА ПО ТИПУ
                // ============================================================
                function renderConfigByType(typeId, typeName, config, container) {
                    // Определяем тип по ID (можно заменить на получение slug из data)
                    const typeSlug = getTypeSlug(typeId);

                    let html = `
            <div class="config-card card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Конфигурация задания</h6>
                        <small class="text-muted">Заполните данные для типа "${typeName}"</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleJsonPreview()">
                        <i class="bx bx-code-alt"></i> Показать JSON
                    </button>
                </div>
                <div class="card-body">
                    <div class="json-preview" id="jsonPreview"></div>
                    <div id="configEditor"></div>
                </div>
            </div>
        `;

                    container.innerHTML = html;

                    // Рендерим редактор в зависимости от типа
                    const slug = getTypeSlug(typeId);
                    switch(slug) {
                        case 'choose_one':
                            renderChooseOne(config);
                            break;
                        case 'choose_three':
                            renderChooseThree(config);
                            break;
                        case 'match_pairs':
                            renderMatchPairs(config);
                            break;
                        case 'match_images':
                            renderMatchImages(config);
                            break;
                        case 'build_word':
                            renderBuildWord(config);
                            break;
                        case 'drag_drop_text':
                            renderDragDropText(config);
                            break;
                        case 'story_order':
                            renderStoryOrder(config);
                            break;
                        case 'alphabet_words':
                            renderAlphabetWords(config);
                            break;
                        case 'color_categories':
                            renderColorCategories(config);
                            break;
                        case 'stress_mark':
                            renderStressMark(config);
                            break;
                        case 'fix_word':
                            renderFixWord(config);
                            break;
                        case 'find_extra_letter':
                            renderFindExtraLetter(config);
                            break;
                        case 'connect_category':
                            renderConnectCategory(config);
                            break;
                        case 'drag_to_image':
                            renderDragToImage(config);
                            break;
                        case 'find_by_condition':
                            renderFindByCondition(config);
                            break;
                        case 'match_behavior':
                            renderMatchBehavior(config);
                            break;
                        case 'build_dialogue':
                            renderBuildDialogue(config);
                            break;
                        case 'alphabet_letters':
                            renderAlphabetLetters(config);
                            break;
                        case 'alphabet_images':
                            renderAlphabetImages(config);
                            break;
                        case 'connect_letters':
                            renderConnectLetters(config);
                            break;
                        case 'word_from_image':
                            renderWordFromImage(config);
                            break;
                        case 'find_by_rule':
                            renderFindByRule(config);
                            break;
                        default:
                            renderGeneric(config);
                    }

                    // Обновляем JSON preview
                    updateJsonPreview();
                }

                // ============================================================
                // ПОЛУЧЕНИЕ SLUG ПО ID
                // ============================================================
                function getTypeSlug(typeId) {
                    const select = document.getElementById('task_type_id');
                    const options = select?.options || [];
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value == typeId) {
                            // Получаем slug из data-атрибута или из текста
                            return options[i].dataset.slug || options[i].text.toLowerCase().replace(/\s+/g, '_');
                        }
                    }
                    return '';
                }

                // ============================================================
                // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
                // ============================================================
                function escapeHtml(str) {
                    if (!str) return '';
                    const div = document.createElement('div');
                    div.textContent = str;
                    return div.innerHTML;
                }

                function toggleJsonPreview() {
                    const preview = document.getElementById('jsonPreview');
                    if (preview) {
                        preview.classList.toggle('active');
                        updateJsonPreview();
                    }
                }

                function updateJsonPreview() {
                    const preview = document.getElementById('jsonPreview');
                    if (!preview) return;

                    try {
                        // Собираем данные из формы
                        const form = document.querySelector('form');
                        const formData = new FormData(form);
                        const config = {};
                        for (const [key, value] of formData.entries()) {
                            if (key.startsWith('config[') && !key.includes('[')) {
                                const match = key.match(/config\[([^\]]+)\]/);
                                if (match) {
                                    config[match[1]] = value;
                                }
                            }
                        }
                        preview.textContent = JSON.stringify(config, null, 2);
                    } catch (e) {
                        // Игнорируем ошибки
                    }
                }

                // ============================================================
                // РЕНДЕРИНГ "Выбери один из 4"
                // ============================================================
                function renderChooseOne(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const question = config.question || '';
                    const options = config.options || [];
                    const shuffle = config.shuffle_options ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Вопрос <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[question]"
                       placeholder="Введите вопрос" value="${escapeHtml(question)}">
                <small class="text-muted">Например: "Как будет 'Солнце' на ингушском?"</small>
            </div>

            <div class="config-field-group">
                <label>Варианты ответов <span class="text-danger">*</span></label>
                <div id="optionsContainer">
        `;

                    const letters = ['a', 'b', 'c', 'd'];
                    const defaultOptions = options.length > 0 ? options : letters.map(l => ({ id: l, text: '', is_correct: false }));

                    defaultOptions.forEach((opt, index) => {
                        const letter = letters[index] || index;
                        const isCorrect = opt.is_correct || false;
                        html += `
                <div class="option-card">
                    <div class="option-letter">${letter.toUpperCase()}</div>
                    <div class="option-text">
                        <input type="text" name="config[options][${index}][text]"
                               placeholder="Вариант ${index + 1}" value="${escapeHtml(opt.text || '')}">
                        <input type="hidden" name="config[options][${index}][id]" value="${letter}">
                    </div>
                    <div class="option-correct">
                        <label>
                            <input type="checkbox" name="config[options][${index}][is_correct]"
                                   value="1" ${isCorrect ? 'checked' : ''}>
                            Верный
                        </label>
                    </div>
                    <button type="button" class="btn-remove-item" onclick="removeOption(this)" title="Удалить вариант">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addOption()">
                    <i class="bx bx-plus"></i> Добавить вариант
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_options]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать варианты</label>
                </div>
            </div>

            <div class="config-field-group">
                <label>Объяснение (опционально)</label>
                <textarea class="form-control" name="config[explanation]"
                          placeholder="Объяснение правильного ответа" rows="2">${escapeHtml(config.explanation || '')}</textarea>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Выбери 3 из 6"
                // ============================================================
                function renderChooseThree(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const question = config.question || '';
                    const options = config.options || [];
                    const minSelect = config.min_select || 3;
                    const maxSelect = config.max_select || 3;
                    const shuffle = config.shuffle_options ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Вопрос <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[question]"
                       placeholder="Введите вопрос" value="${escapeHtml(question)}">
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="config-field-group">
                        <label>Минимум выборов</label>
                        <input type="number" class="form-control" name="config[min_select]"
                               value="${minSelect}" min="1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-field-group">
                        <label>Максимум выборов</label>
                        <input type="number" class="form-control" name="config[max_select]"
                               value="${maxSelect}" min="1">
                    </div>
                </div>
            </div>

            <div class="config-field-group">
                <label>Варианты ответов (6) <span class="text-danger">*</span></label>
                <div id="optionsContainer">
        `;

                    const letters = ['a', 'b', 'c', 'd', 'e', 'f'];
                    const defaultOptions = options.length > 0 ? options : letters.map(l => ({ id: l, text: '', is_correct: false }));

                    defaultOptions.forEach((opt, index) => {
                        const letter = letters[index] || index;
                        const isCorrect = opt.is_correct || false;
                        html += `
                <div class="option-card">
                    <div class="option-letter">${letter.toUpperCase()}</div>
                    <div class="option-text">
                        <input type="text" name="config[options][${index}][text]"
                               placeholder="Вариант ${index + 1}" value="${escapeHtml(opt.text || '')}">
                        <input type="hidden" name="config[options][${index}][id]" value="${letter}">
                    </div>
                    <div class="option-correct">
                        <label>
                            <input type="checkbox" name="config[options][${index}][is_correct]"
                                   value="1" ${isCorrect ? 'checked' : ''}>
                            Верный
                        </label>
                    </div>
                    <button type="button" class="btn-remove-item" onclick="removeOption(this)" title="Удалить вариант">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addOption()">
                    <i class="bx bx-plus"></i> Добавить вариант
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_options]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать варианты</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Сопоставь с изображениями"
                // ============================================================
                function renderMatchImages(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const pairs = config.pairs || [];
                    const shuffle = config.shuffle_pairs ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Пары для сопоставления <span class="text-danger">*</span></label>
                <div id="pairsContainer">
        `;

                    const defaultPairs = pairs.length > 0 ? pairs : [{ text: '', image: '', correct_match: '' }];

                    defaultPairs.forEach((pair, index) => {
                        html += `
                <div class="pair-card">
                    <div>
                        <label class="pair-label">Текст</label>
                        <input type="text" name="config[pairs][${index}][text]"
                               placeholder="Текст" value="${escapeHtml(pair.text || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Изображение</label>
                        <input type="text" name="config[pairs][${index}][image]"
                               placeholder="/images/example.jpg" value="${escapeHtml(pair.image || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Правильное совпадение</label>
                        <input type="text" name="config[pairs][${index}][correct_match]"
                               placeholder="Совпадение" value="${escapeHtml(pair.correct_match || '')}">
                    </div>
                    <button type="button" class="btn-remove-item" onclick="removePair(this)">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addPair()">
                    <i class="bx bx-plus"></i> Добавить пару
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_pairs]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать пары</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Собери слово из букв"
                // ============================================================
                function renderBuildWord(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const correctWord = config.correct_word || '';
                    const letters = config.letters || [];
                    const extraLetters = config.extra_letters || [];
                    const hint = config.hint || '';
                    const shuffle = config.shuffle_letters ?? true;

                    let html = `
            <div class="row">
                <div class="col-md-6">
                    <div class="config-field-group">
                        <label>Правильное слово <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="config[correct_word]"
                               placeholder="Например: ГОРОД" value="${escapeHtml(correctWord)}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-field-group">
                        <label>Изображение (опционально)</label>
                        <input type="text" class="form-control" name="config[image]"
                               placeholder="/images/city.jpg" value="${escapeHtml(config.image || '')}">
                    </div>
                </div>
            </div>

            <div class="config-field-group">
                <label>Буквы для конструктора <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[letters]"
                       placeholder='["Г", "О", "Р", "О", "Д"]'
                       value="${escapeHtml(JSON.stringify(letters))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив букв в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Лишние буквы (опционально)</label>
                <input type="text" class="form-control" name="config[extra_letters]"
                       placeholder='["А", "О"]'
                       value="${escapeHtml(JSON.stringify(extraLetters))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив лишних букв в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Подсказка</label>
                <input type="text" class="form-control" name="config[hint]"
                       placeholder="Например: На картинке изображен большой ..."
                       value="${escapeHtml(hint)}">
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_letters]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать буквы</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Поставь ударение"
                // ============================================================
                function renderStressMark(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const word = config.word || '';
                    const letters = config.letters || [];
                    const correctIndex = config.correct_index ?? 0;

                    let html = `
            <div class="config-field-group">
                <label>Слово <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[word]"
                       placeholder="Например: молоко" value="${escapeHtml(word)}">
                <small class="text-muted">Введите слово, в котором нужно поставить ударение</small>
            </div>

            <div class="config-field-group">
                <label>Буквы с ударением <span class="text-danger">*</span></label>
                <div id="lettersContainer">
        `;

                    const defaultLetters = letters.length > 0 ? letters : word.split('').map((l, i) => ({ letter: l, is_stressed: false, position: i }));

                    defaultLetters.forEach((letter, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: auto 1fr auto auto;">
                    <span style="font-weight:600;color:#696cff;">${index + 1}</span>
                    <div>
                        <label class="pair-label">Буква</label>
                        <input type="text" name="config[letters][${index}][letter]"
                               placeholder="Буква" value="${escapeHtml(letter.letter || '')}" maxlength="1">
                    </div>
                    <div>
                        <label class="pair-label">Позиция</label>
                        <input type="number" name="config[letters][${index}][position]"
                               value="${letter.position ?? index}" min="0">
                    </div>
                    <div>
                        <label class="pair-label">Ударная</label>
                        <input type="checkbox" name="config[letters][${index}][is_stressed]"
                               value="1" ${letter.is_stressed ? 'checked' : ''}>
                    </div>
                </div>
            `;
                    });

                    html += `
                </div>
            </div>

            <div class="config-field-group">
                <label>Индекс правильного ответа <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="config[correct_index]"
                       value="${correctIndex}" min="0">
                <small class="text-muted">Индекс буквы, на которую нужно нажать (начиная с 0)</small>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Исправь слово"
                // ============================================================
                function renderFixWord(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const sentence = config.sentence || '';
                    const wrongWord = config.wrong_word || '';
                    const correctForm = config.correct_form || '';
                    const correctForms = config.correct_forms || [];
                    const hint = config.hint || '';

                    let html = `
            <div class="config-field-group">
                <label>Предложение <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[sentence]"
                       placeholder='Белые облакИ плывут по небу.' value="${escapeHtml(sentence)}">
            </div>

            <div class="config-field-group">
                <label>Неправильное слово <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[wrong_word]"
                       placeholder="облакИ" value="${escapeHtml(wrongWord)}">
            </div>

            <div class="config-field-group">
                <label>Правильная форма <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[correct_form]"
                       placeholder="облака" value="${escapeHtml(correctForm)}">
            </div>

            <div class="config-field-group">
                <label>Варианты правильных форм</label>
                <input type="text" class="form-control" name="config[correct_forms]"
                       placeholder='["облака", "облаков", "облаками"]'
                       value="${escapeHtml(JSON.stringify(correctForms))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Подсказка</label>
                <input type="text" class="form-control" name="config[hint]"
                       placeholder="Какое окончание правильное?" value="${escapeHtml(hint)}">
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Найди лишнюю букву"
                // ============================================================
                function renderFindExtraLetter(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const image = config.image || '';
                    const word = config.word || '';
                    const extraLetter = config.extra_letter || '';
                    const correctIndex = config.correct_index ?? 0;
                    const hint = config.hint || '';

                    let html = `
            <div class="config-field-group">
                <label>Изображение</label>
                <input type="text" class="form-control" name="config[image]"
                       placeholder="/images/cat.jpg" value="${escapeHtml(image)}">
            </div>

            <div class="config-field-group">
                <label>Слово <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[word]"
                       placeholder="КОШКА" value="${escapeHtml(word)}">
            </div>

            <div class="config-field-group">
                <label>Лишняя буква <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[extra_letter]"
                       placeholder="К" value="${escapeHtml(extraLetter)}" maxlength="1">
            </div>

            <div class="config-field-group">
                <label>Индекс лишней буквы <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="config[correct_index]"
                       value="${correctIndex}" min="0">
                <small class="text-muted">Индекс буквы в слове (начиная с 0)</small>
            </div>

            <div class="config-field-group">
                <label>Подсказка</label>
                <input type="text" class="form-control" name="config[hint]"
                       placeholder="Какая буква лишняя?" value="${escapeHtml(hint)}">
            </div>

            <div class="config-field-group">
                <label>Буквы с флагами (опционально)</label>
                <textarea class="form-control" name="config[letters]"
                          placeholder='[{"id": 1, "letter": "К", "is_extra": false}]'
                          rows="3" style="font-family: monospace; font-size: 14px;">${escapeHtml(JSON.stringify(config.letters || [], null, 2))}</textarea>
                <small class="text-muted">Введите массив букв в формате JSON</small>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Сопоставь с категорией"
                // ============================================================
                function renderConnectCategory(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const items = config.items || [];
                    const categories = config.categories || [];
                    const shuffle = config.shuffle_items ?? true;
                    const lineColors = config.line_colors || ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'];

                    let html = `
            <div class="config-field-group">
                <label>Элементы <span class="text-danger">*</span></label>
                <div id="connectItemsContainer">
        `;

                    const defaultItems = items.length > 0 ? items : [
                        { id: 1, word: '', category: '' },
                        { id: 2, word: '', category: '' }
                    ];

                    defaultItems.forEach((item, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Слово</label>
                        <input type="text" name="config[items][${index}][word]"
                               placeholder="Слово" value="${escapeHtml(item.word || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Категория</label>
                        <input type="text" name="config[items][${index}][category]"
                               placeholder="category_id" value="${escapeHtml(item.category || '')}">
                    </div>
                    <input type="hidden" name="config[items][${index}][id]" value="${item.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove()">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addConnectItem()">
                    <i class="bx bx-plus"></i> Добавить элемент
                </button>
            </div>

            <div class="config-field-group">
                <label>Категории <span class="text-danger">*</span></label>
                <div id="connectCategoriesContainer">
        `;

                    const defaultCategories = categories.length > 0 ? categories : [
                        { id: 'birds', name: 'Птицы', color: '#FF6B6B' },
                        { id: 'fish', name: 'Рыбы', color: '#4ECDC4' }
                    ];

                    defaultCategories.forEach((cat, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">ID</label>
                        <input type="text" name="config[categories][${index}][id]"
                               placeholder="birds" value="${escapeHtml(cat.id || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Название</label>
                        <input type="text" name="config[categories][${index}][name]"
                               placeholder="Птицы" value="${escapeHtml(cat.name || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Цвет</label>
                        <input type="color" name="config[categories][${index}][color]"
                               value="${cat.color || '#FF6B6B'}" style="height: 38px;">
                    </div>
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove()">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addConnectCategory()">
                    <i class="bx bx-plus"></i> Добавить категорию
                </button>
            </div>

            <div class="config-field-group">
                <label>Цвета линий</label>
                <input type="text" class="form-control" name="config[line_colors]"
                       placeholder='["#FF6B6B", "#4ECDC4", "#45B7D1"]'
                       value="${escapeHtml(JSON.stringify(lineColors))}"
                       style="font-family: monospace;">
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_items]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать элементы</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ ДЛЯ "Расставь слова в алфавитном порядке"
                // ============================================================
                function renderAlphabetWords(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const words = config.words || [];
                    const alphabet = config.alphabet || 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
                    const shuffled = config.shuffled_words || [];

                    let html = `
            <div class="config-field-group">
                <label>Слова <span class="text-danger">*</span></label>
                <div id="alphabetWordsContainer">
        `;

                    const defaultWords = words.length > 0 ? words : [
                        { id: 1, text: '', correct_position: 0 },
                        { id: 2, text: '', correct_position: 1 }
                    ];

                    defaultWords.forEach((word, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: auto 1fr 1fr auto;">
                    <span style="font-weight:600;color:#696cff;">#${index + 1}</span>
                    <div>
                        <label class="pair-label">Слово</label>
                        <input type="text" name="config[words][${index}][text]"
                               placeholder="Слово" value="${escapeHtml(word.text || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Позиция</label>
                        <input type="number" name="config[words][${index}][correct_position]"
                               value="${word.correct_position ?? index}" min="0">
                    </div>
                    <input type="hidden" name="config[words][${index}][id]" value="${word.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove()">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addAlphabetWord()">
                    <i class="bx bx-plus"></i> Добавить слово
                </button>
            </div>

            <div class="config-field-group">
                <label>Перемешанный порядок</label>
                <input type="text" class="form-control" name="config[shuffled_words]"
                       placeholder='[5, 1, 7, 3, 0, 6, 4, 2]'
                       value="${escapeHtml(JSON.stringify(shuffled))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив индексов в перемешанном порядке</small>
            </div>

            <div class="config-field-group">
                <label>Алфавит</label>
                <input type="text" class="form-control" name="config[alphabet]"
                       placeholder="АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ"
                       value="${escapeHtml(alphabet)}">
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // ДОБАВЛЕНИЕ/УДАЛЕНИЕ ВАРИАНТОВ
                // ============================================================
                window.addOption = function() {
                    const container = document.getElementById('optionsContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.option-card').length;
                    const maxOptions = 6;
                    if (count >= maxOptions) {
                        alert(`Максимум ${maxOptions} вариантов`);
                        return;
                    }
                    const letters = 'abcdefghijklmnopqrstuvwxyz';
                    const letter = letters[count] || count;
                    const div = document.createElement('div');
                    div.className = 'option-card';
                    div.innerHTML = `
            <div class="option-letter">${letter.toUpperCase()}</div>
            <div class="option-text">
                <input type="text" name="config[options][${count}][text]" placeholder="Вариант ${count + 1}" value="">
                <input type="hidden" name="config[options][${count}][id]" value="${letter}">
            </div>
            <div class="option-correct">
                <label>
                    <input type="checkbox" name="config[options][${count}][is_correct]" value="1">
                    Верный
                </label>
            </div>
            <button type="button" class="btn-remove-item" onclick="removeOption(this)" title="Удалить вариант">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                window.removeOption = function(btn) {
                    const card = btn.closest('.option-card');
                    const container = document.getElementById('optionsContainer');
                    if (!container) return;
                    if (container.querySelectorAll('.option-card').length <= 2) {
                        alert('Минимум 2 варианта');
                        return;
                    }
                    card.remove();
                    updateJsonPreview();
                };

                // ============================================================
                // ДОБАВЛЕНИЕ/УДАЛЕНИЕ ПАР
                // ============================================================
                window.addPair = function() {
                    const container = document.getElementById('pairsContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Текст</label>
                <input type="text" name="config[pairs][${count}][text]" placeholder="Текст" value="">
            </div>
            <div>
                <label class="pair-label">Изображение</label>
                <input type="text" name="config[pairs][${count}][image]" placeholder="/images/example.jpg" value="">
            </div>
            <div>
                <label class="pair-label">Правильное совпадение</label>
                <input type="text" name="config[pairs][${count}][correct_match]" placeholder="Совпадение" value="">
            </div>
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                window.removePair = function(btn) {
                    const card = btn.closest('.pair-card');
                    const container = document.getElementById('pairsContainer');
                    if (!container) return;
                    if (container.querySelectorAll('.pair-card').length <= 1) {
                        alert('Минимум 1 пара');
                        return;
                    }
                    card.remove();
                    updateJsonPreview();
                };

                // ============================================================
                // ДОБАВЛЕНИЕ ДЛЯ CONNECT CATEGORY
                // ============================================================
                window.addConnectItem = function() {
                    const container = document.getElementById('connectItemsContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Слово</label>
                <input type="text" name="config[items][${count}][word]" placeholder="Слово" value="">
            </div>
            <div>
                <label class="pair-label">Категория</label>
                <input type="text" name="config[items][${count}][category]" placeholder="category_id" value="">
            </div>
            <input type="hidden" name="config[items][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                window.addConnectCategory = function() {
                    const container = document.getElementById('connectCategoriesContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD'];
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">ID</label>
                <input type="text" name="config[categories][${count}][id]" placeholder="birds" value="">
            </div>
            <div>
                <label class="pair-label">Название</label>
                <input type="text" name="config[categories][${count}][name]" placeholder="Птицы" value="">
            </div>
            <div>
                <label class="pair-label">Цвет</label>
                <input type="color" name="config[categories][${count}][color]" value="${colors[count % colors.length]}" style="height: 38px;">
            </div>
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // ДОБАВЛЕНИЕ ДЛЯ ALPHABET WORDS
                // ============================================================
                window.addAlphabetWord = function() {
                    const container = document.getElementById('alphabetWordsContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = 'auto 1fr 1fr auto';
                    div.innerHTML = `
            <span style="font-weight:600;color:#696cff;">#${count + 1}</span>
            <div>
                <label class="pair-label">Слово</label>
                <input type="text" name="config[words][${count}][text]" placeholder="Слово" value="">
            </div>
            <div>
                <label class="pair-label">Позиция</label>
                <input type="number" name="config[words][${count}][correct_position]" placeholder="0" value="${count}" min="0">
            </div>
            <input type="hidden" name="config[words][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // УНИВЕРСАЛЬНЫЙ РЕНДЕРИНГ
                // ============================================================
                function renderGeneric(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    let html = `
            <div class="config-field-group">
                <label>Конфигурация</label>
                <textarea class="form-control" name="config" rows="10"
                          style="font-family: monospace; font-size: 14px;">${escapeHtml(JSON.stringify(config, null, 2))}</textarea>
                <small class="text-muted">Введите конфигурацию в формате JSON</small>
            </div>
        `;
                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Расставь буквы в алфавитном порядке"
                // ============================================================
                function renderAlphabetLetters(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const letters = config.letters || [];
                    const shuffled = config.shuffled_letters || [];
                    const alphabet = config.alphabet || 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';

                    let html = `
            <div class="config-field-group">
                <label>Буквы <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[letters]"
                       placeholder='[{"letter": "К", "correct_position": 3}, {"letter": "А", "correct_position": 0}]'
                       value="${escapeHtml(JSON.stringify(letters))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив букв с позициями в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Перемешанные буквы</label>
                <input type="text" class="form-control" name="config[shuffled_letters]"
                       placeholder='["К", "В", "А", "М", "Б"]'
                       value="${escapeHtml(JSON.stringify(shuffled))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив букв в перемешанном порядке</small>
            </div>

            <div class="config-field-group">
                <label>Алфавит</label>
                <input type="text" class="form-control" name="config[alphabet]"
                       placeholder="АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ"
                       value="${escapeHtml(alphabet)}">
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Расставь изображения в алфавитном порядке"
                // ============================================================
                function renderAlphabetImages(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const items = config.items || [];
                    const shuffled = config.shuffled_items || [];
                    const showNames = config.show_names ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Изображения <span class="text-danger">*</span></label>
                <div id="alphabetImagesContainer">
        `;

                    const defaultItems = items.length > 0 ? items : [
                        { id: 1, name: '', image: '', correct_order: 0 },
                        { id: 2, name: '', image: '', correct_order: 1 }
                    ];

                    defaultItems.forEach((item, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: auto 1fr 1fr auto;">
                    <span style="font-weight:600;color:#696cff;">#${index + 1}</span>
                    <div>
                        <label class="pair-label">Название</label>
                        <input type="text" name="config[items][${index}][name]"
                               placeholder="Аист" value="${escapeHtml(item.name || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Изображение</label>
                        <input type="text" name="config[items][${index}][image]"
                               placeholder="/images/aist.jpg" value="${escapeHtml(item.image || '')}">
                    </div>
                    <input type="hidden" name="config[items][${index}][id]" value="${item.id || index + 1}">
                    <input type="hidden" name="config[items][${index}][correct_order]" value="${item.correct_order ?? index}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addAlphabetImage()">
                    <i class="bx bx-plus"></i> Добавить изображение
                </button>
            </div>

            <div class="config-field-group">
                <label>Перемешанный порядок</label>
                <input type="text" class="form-control" name="config[shuffled_items]"
                       placeholder='[2, 0, 1]'
                       value="${escapeHtml(JSON.stringify(shuffled))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив индексов в перемешанном порядке</small>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[show_names]"
                           value="1" ${showNames ? 'checked' : ''}>
                    <label class="form-check-label">Показывать названия</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addAlphabetImage = function() {
                    const container = document.getElementById('alphabetImagesContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = 'auto 1fr 1fr auto';
                    div.innerHTML = `
            <span style="font-weight:600;color:#696cff;">#${count + 1}</span>
            <div>
                <label class="pair-label">Название</label>
                <input type="text" name="config[items][${count}][name]" placeholder="Название" value="">
            </div>
            <div>
                <label class="pair-label">Изображение</label>
                <input type="text" name="config[items][${count}][image]" placeholder="/images/example.jpg" value="">
            </div>
            <input type="hidden" name="config[items][${count}][id]" value="${count + 1}">
            <input type="hidden" name="config[items][${count}][correct_order]" value="${count}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Соедини буквы в алфавитном порядке"
                // ============================================================
                function renderConnectLetters(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const letters = config.letters || [];
                    const correctOrder = config.correct_order || [];
                    const alphabet = config.alphabet || 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';

                    let html = `
            <div class="config-field-group">
                <label>Буквы с позициями <span class="text-danger">*</span></label>
                <textarea class="form-control" name="config[letters]"
                          placeholder='[{"id": 1, "letter": "А", "position": {"x": 100, "y": 200}}, {"id": 2, "letter": "Б", "position": {"x": 300, "y": 100}}]'
                          rows="5" style="font-family: monospace; font-size: 14px;">${escapeHtml(JSON.stringify(letters, null, 2))}</textarea>
                <small class="text-muted">Введите массив букв с координатами в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Правильный порядок <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[correct_order]"
                       placeholder='["А", "Б", "В", "Г", "Д"]'
                       value="${escapeHtml(JSON.stringify(correctOrder))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив букв в правильном порядке</small>
            </div>

            <div class="config-field-group">
                <label>Алфавит</label>
                <input type="text" class="form-control" name="config[alphabet]"
                       placeholder="АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ"
                       value="${escapeHtml(alphabet)}">
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Что изображено на изображении" (составь слово по картинке)
                // ============================================================
                function renderWordFromImage(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const image = config.image || '';
                    const correctWord = config.correct_word || '';
                    const letters = config.letters || [];
                    const extraLetters = config.extra_letters || [];
                    const hint = config.hint || '';
                    const shuffle = config.shuffle_letters ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Изображение <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[image]"
                       placeholder="/images/sun.jpg" value="${escapeHtml(image)}">
            </div>

            <div class="config-field-group">
                <label>Правильное слово <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[correct_word]"
                       placeholder="СОЛНЦЕ" value="${escapeHtml(correctWord)}">
            </div>

            <div class="config-field-group">
                <label>Буквы <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[letters]"
                       placeholder='["С", "О", "Л", "Н", "Ц", "Е", "Т", "М"]'
                       value="${escapeHtml(JSON.stringify(letters))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив букв в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Лишние буквы</label>
                <input type="text" class="form-control" name="config[extra_letters]"
                       placeholder='["Т", "М"]'
                       value="${escapeHtml(JSON.stringify(extraLetters))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив лишних букв в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Подсказка</label>
                <input type="text" class="form-control" name="config[hint]"
                       placeholder="Светит ярко" value="${escapeHtml(hint)}">
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_letters]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать буквы</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                // ============================================================
                // РЕНДЕРИНГ "Найди по признаку"
                // ============================================================
                function renderFindByRule(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const words = config.words || [];
                    const rule = config.rule || { type: '', description: '', example: '' };
                    const minSelect = config.min_select || 1;
                    const shuffle = config.shuffle_words ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Слова <span class="text-danger">*</span></label>
                <div id="findByRuleContainer">
        `;

                    const defaultWords = words.length > 0 ? words : [
                        { id: 1, text: '', is_correct: false },
                        { id: 2, text: '', is_correct: false }
                    ];

                    defaultWords.forEach((word, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr auto auto;">
                    <div>
                        <label class="pair-label">Слово</label>
                        <input type="text" name="config[words][${index}][text]"
                               placeholder="Слово" value="${escapeHtml(word.text || '')}">
                        <input type="hidden" name="config[words][${index}][id]" value="${word.id || index + 1}">
                    </div>
                    <div>
                        <label class="pair-label">Правильное</label>
                        <input type="checkbox" name="config[words][${index}][is_correct]"
                               value="1" ${word.is_correct ? 'checked' : ''}>
                    </div>
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addFindByRuleWord()">
                    <i class="bx bx-plus"></i> Добавить слово
                </button>
            </div>

            <div class="config-field-group">
                <label>Правило <span class="text-danger">*</span></label>
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="config[rule][type]"
                               placeholder="Тип правила" value="${escapeHtml(rule.type || '')}">
                        <small class="text-muted">Например: consonants_all_hard</small>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="config[rule][description]"
                               placeholder="Описание" value="${escapeHtml(rule.description || '')}">
                        <small class="text-muted">Все согласные твердые</small>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="config[rule][example]"
                               placeholder="Пример" value="${escapeHtml(rule.example || '')}">
                        <small class="text-muted">Дом - д(тв), м(тв)</small>
                    </div>
                </div>
            </div>

            <div class="config-field-group">
                <label>Минимум выборов</label>
                <input type="number" class="form-control" name="config[min_select]"
                       value="${minSelect}" min="1">
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_words]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать слова</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addFindByRuleWord = function() {
                    const container = document.getElementById('findByRuleContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr auto auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Слово</label>
                <input type="text" name="config[words][${count}][text]" placeholder="Слово" value="">
                <input type="hidden" name="config[words][${count}][id]" value="${count + 1}">
            </div>
            <div>
                <label class="pair-label">Правильное</label>
                <input type="checkbox" name="config[words][${count}][is_correct]" value="1">
            </div>
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Перетащи слово к картинке"
                // ============================================================
                function renderDragToImage(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const pairs = config.pairs || [];
                    const shuffleWords = config.shuffle_words ?? true;
                    const shuffleImages = config.shuffle_images ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Пары (слово → изображение) <span class="text-danger">*</span></label>
                <div id="dragToImageContainer">
        `;

                    const defaultPairs = pairs.length > 0 ? pairs : [
                        { id: 1, word: '', image: '' },
                        { id: 2, word: '', image: '' }
                    ];

                    defaultPairs.forEach((pair, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Слово</label>
                        <input type="text" name="config[pairs][${index}][word]"
                               placeholder="Кошка" value="${escapeHtml(pair.word || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Изображение</label>
                        <input type="text" name="config[pairs][${index}][image]"
                               placeholder="/images/cat.jpg" value="${escapeHtml(pair.image || '')}">
                    </div>
                    <input type="hidden" name="config[pairs][${index}][id]" value="${pair.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addDragToImagePair()">
                    <i class="bx bx-plus"></i> Добавить пару
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_words]"
                           value="1" ${shuffleWords ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать слова</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_images]"
                           value="1" ${shuffleImages ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать изображения</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addDragToImagePair = function() {
                    const container = document.getElementById('dragToImageContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Слово</label>
                <input type="text" name="config[pairs][${count}][word]" placeholder="Слово" value="">
            </div>
            <div>
                <label class="pair-label">Изображение</label>
                <input type="text" name="config[pairs][${count}][image]" placeholder="/images/example.jpg" value="">
            </div>
            <input type="hidden" name="config[pairs][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Найди по условию"
                // ============================================================
                function renderFindByCondition(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const images = config.images || [];
                    const condition = config.condition || { text: '', type: '', correct_indices: [] };
                    const minSelect = config.min_select || 1;
                    const maxSelect = config.max_select || 1;

                    let html = `
            <div class="config-field-group">
                <label>Условие <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[condition][text]"
                       placeholder="Найди картинку, где люди правильно общаются" value="${escapeHtml(condition.text || '')}">
                <div class="row mt-2">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="config[condition][type]"
                               placeholder="Тип условия" value="${escapeHtml(condition.type || '')}">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="config[condition][correct_indices]"
                               placeholder='[0, 2]'
                               value="${escapeHtml(JSON.stringify(condition.correct_indices || []))}"
                               style="font-family: monospace;">
                        <small class="text-muted">Индексы правильных изображений</small>
                    </div>
                </div>
            </div>

            <div class="config-field-group">
                <label>Изображения <span class="text-danger">*</span></label>
                <div id="findByConditionContainer">
        `;

                    const defaultImages = images.length > 0 ? images : [
                        { id: 1, url: '', alt: '' },
                        { id: 2, url: '', alt: '' }
                    ];

                    defaultImages.forEach((img, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">URL изображения</label>
                        <input type="text" name="config[images][${index}][url]"
                               placeholder="/images/people1.jpg" value="${escapeHtml(img.url || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Alt текст</label>
                        <input type="text" name="config[images][${index}][alt]"
                               placeholder="Люди разговаривают" value="${escapeHtml(img.alt || '')}">
                    </div>
                    <input type="hidden" name="config[images][${index}][id]" value="${img.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addFindByConditionImage()">
                    <i class="bx bx-plus"></i> Добавить изображение
                </button>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="config-field-group">
                        <label>Минимум выборов</label>
                        <input type="number" class="form-control" name="config[min_select]"
                               value="${minSelect}" min="1">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-field-group">
                        <label>Максимум выборов</label>
                        <input type="number" class="form-control" name="config[max_select]"
                               value="${maxSelect}" min="1">
                    </div>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addFindByConditionImage = function() {
                    const container = document.getElementById('findByConditionContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">URL изображения</label>
                <input type="text" name="config[images][${count}][url]" placeholder="/images/example.jpg" value="">
            </div>
            <div>
                <label class="pair-label">Alt текст</label>
                <input type="text" name="config[images][${count}][alt]" placeholder="Описание" value="">
            </div>
            <input type="hidden" name="config[images][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Сопоставь с поведением"
                // ============================================================
                function renderMatchBehavior(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const items = config.items || [];
                    const behaviors = config.behaviors || [];
                    const shuffle = config.shuffle_items ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Поведения <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[behaviors]"
                       placeholder='["Правильное общение", "Неправильное общение"]'
                       value="${escapeHtml(JSON.stringify(behaviors))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив поведений в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Элементы <span class="text-danger">*</span></label>
                <div id="matchBehaviorContainer">
        `;

                    const defaultItems = items.length > 0 ? items : [
                        { id: 1, situation: '', behavior: '', image: '' },
                        { id: 2, situation: '', behavior: '', image: '' }
                    ];

                    defaultItems.forEach((item, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Ситуация</label>
                        <input type="text" name="config[items][${index}][situation]"
                               placeholder="Собеседник отвлекается" value="${escapeHtml(item.situation || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Поведение</label>
                        <input type="text" name="config[items][${index}][behavior]"
                               placeholder="Неправильное общение" value="${escapeHtml(item.behavior || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Изображение</label>
                        <input type="text" name="config[items][${index}][image]"
                               placeholder="/images/bad_comm.jpg" value="${escapeHtml(item.image || '')}">
                    </div>
                    <input type="hidden" name="config[items][${index}][id]" value="${item.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addMatchBehaviorItem()">
                    <i class="bx bx-plus"></i> Добавить элемент
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_items]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать элементы</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addMatchBehaviorItem = function() {
                    const container = document.getElementById('matchBehaviorContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Ситуация</label>
                <input type="text" name="config[items][${count}][situation]" placeholder="Ситуация" value="">
            </div>
            <div>
                <label class="pair-label">Поведение</label>
                <input type="text" name="config[items][${count}][behavior]" placeholder="Поведение" value="">
            </div>
            <div>
                <label class="pair-label">Изображение</label>
                <input type="text" name="config[items][${count}][image]" placeholder="/images/example.jpg" value="">
            </div>
            <input type="hidden" name="config[items][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Составь диалог"
                // ============================================================
                function renderBuildDialogue(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const dialogues = config.dialogues || [];
                    const options = config.options || [];
                    const shuffleOptions = config.shuffle_options ?? true;
                    const showSpeakers = config.show_speakers ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Диалоги <span class="text-danger">*</span></label>
                <div id="buildDialogueContainer">
        `;

                    const defaultDialogues = dialogues.length > 0 ? dialogues : [
                        { id: 1, speaker: '', text: '', correct_order: 1 },
                        { id: 2, speaker: '', text: '', correct_order: 2 }
                    ];

                    defaultDialogues.forEach((dialogue, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Говорящий</label>
                        <input type="text" name="config[dialogues][${index}][speaker]"
                               placeholder="Маша" value="${escapeHtml(dialogue.speaker || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Текст</label>
                        <input type="text" name="config[dialogues][${index}][text]"
                               placeholder="Привет, Маша!" value="${escapeHtml(dialogue.text || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Порядок</label>
                        <input type="number" name="config[dialogues][${index}][correct_order]"
                               value="${dialogue.correct_order || index + 1}" min="1">
                    </div>
                    <input type="hidden" name="config[dialogues][${index}][id]" value="${dialogue.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addBuildDialogue()">
                    <i class="bx bx-plus"></i> Добавить реплику
                </button>
            </div>

            <div class="config-field-group">
                <label>Варианты ответов <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[options]"
                       placeholder='[{"id": 1, "text": "Привет, Маша!"}, {"id": 2, "text": "Ты куда спешишь?"}]'
                       value="${escapeHtml(JSON.stringify(options))}"
                       style="font-family: monospace; min-height: 80px;">
                <small class="text-muted">Введите массив вариантов в формате JSON</small>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_options]"
                           value="1" ${shuffleOptions ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать варианты</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[show_speakers]"
                           value="1" ${showSpeakers ? 'checked' : ''}>
                    <label class="form-check-label">Показывать говорящих</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addBuildDialogue = function() {
                    const container = document.getElementById('buildDialogueContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Говорящий</label>
                <input type="text" name="config[dialogues][${count}][speaker]" placeholder="Говорящий" value="">
            </div>
            <div>
                <label class="pair-label">Текст</label>
                <input type="text" name="config[dialogues][${count}][text]" placeholder="Текст реплики" value="">
            </div>
            <div>
                <label class="pair-label">Порядок</label>
                <input type="number" name="config[dialogues][${count}][correct_order]" value="${count + 1}" min="1">
            </div>
            <input type="hidden" name="config[dialogues][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Перемести слова в текст" (drag_drop_text)
                // ============================================================
                function renderDragDropText(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const sentences = config.sentences || [];
                    const words = config.words || [];
                    const extraWords = config.extra_words || [];
                    const shuffle = config.shuffle_words ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Предложения с пропусками <span class="text-danger">*</span></label>
                <div id="dragDropSentencesContainer">
        `;

                    const defaultSentences = sentences.length > 0 ? sentences : [
                        { id: 1, text: 'Аьрзий ___ дукха веза.', blank_position: 1, correct_word: '' },
                        { id: 2, text: 'Маша ___ кхаькха.', blank_position: 1, correct_word: '' }
                    ];

                    defaultSentences.forEach((sentence, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Предложение</label>
                        <input type="text" name="config[sentences][${index}][text]"
                               placeholder="Текст с ___" value="${escapeHtml(sentence.text || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Правильное слово</label>
                        <input type="text" name="config[sentences][${index}][correct_word]"
                               placeholder="гӏаькх" value="${escapeHtml(sentence.correct_word || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Позиция пропуска</label>
                        <input type="number" name="config[sentences][${index}][blank_position]"
                               value="${sentence.blank_position ?? 1}" min="1">
                    </div>
                    <input type="hidden" name="config[sentences][${index}][id]" value="${sentence.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addDragDropSentence()">
                    <i class="bx bx-plus"></i> Добавить предложение
                </button>
            </div>

            <div class="config-field-group">
                <label>Слова для вставки <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config[words]"
                       placeholder='["гӏаькх", "воал", "саг"]'
                       value="${escapeHtml(JSON.stringify(words))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив слов в формате JSON</small>
            </div>

            <div class="config-field-group">
                <label>Лишние слова (опционально)</label>
                <input type="text" class="form-control" name="config[extra_words]"
                       placeholder='["кхы"]'
                       value="${escapeHtml(JSON.stringify(extraWords))}"
                       style="font-family: monospace;">
                <small class="text-muted">Введите массив лишних слов в формате JSON</small>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_words]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать слова</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addDragDropSentence = function() {
                    const container = document.getElementById('dragDropSentencesContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Предложение</label>
                <input type="text" name="config[sentences][${count}][text]" placeholder="Текст с ___" value="">
            </div>
            <div>
                <label class="pair-label">Правильное слово</label>
                <input type="text" name="config[sentences][${count}][correct_word]" placeholder="Слово" value="">
            </div>
            <div>
                <label class="pair-label">Позиция пропуска</label>
                <input type="number" name="config[sentences][${count}][blank_position]" value="1" min="1">
            </div>
            <input type="hidden" name="config[sentences][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Расставь части истории" (story_order)
                // ============================================================
                function renderStoryOrder(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const parts = config.parts || [];
                    const shuffle = config.shuffle_parts ?? true;
                    const showNumbers = config.show_numbers ?? false;

                    let html = `
            <div class="config-field-group">
                <label>Части истории <span class="text-danger">*</span></label>
                <div id="storyPartsContainer">
        `;

                    const defaultParts = parts.length > 0 ? parts : [
                        { id: 1, text: '', correct_order: 1 },
                        { id: 2, text: '', correct_order: 2 },
                        { id: 3, text: '', correct_order: 3 }
                    ];

                    defaultParts.forEach((part, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Текст части</label>
                        <input type="text" name="config[parts][${index}][text]"
                               placeholder="Однажды я увидел..." value="${escapeHtml(part.text || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Правильный порядок</label>
                        <input type="number" name="config[parts][${index}][correct_order]"
                               value="${part.correct_order || index + 1}" min="1">
                    </div>
                    <input type="hidden" name="config[parts][${index}][id]" value="${part.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addStoryPart()">
                    <i class="bx bx-plus"></i> Добавить часть
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_parts]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать части</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[show_numbers]"
                           value="1" ${showNumbers ? 'checked' : ''}>
                    <label class="form-check-label">Показывать номера</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addStoryPart = function() {
                    const container = document.getElementById('storyPartsContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Текст части</label>
                <input type="text" name="config[parts][${count}][text]" placeholder="Текст части" value="">
            </div>
            <div>
                <label class="pair-label">Правильный порядок</label>
                <input type="number" name="config[parts][${count}][correct_order]" value="${count + 1}" min="1">
            </div>
            <input type="hidden" name="config[parts][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Сопоставь с категориями" (color_categories) - упрощенный вариант
                // ============================================================
                function renderColorCategories(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const items = config.items || [];
                    const categories = config.categories || [];
                    const shuffle = config.shuffle_items ?? true;

                    let html = `
            <div class="config-field-group">
                <label>Элементы <span class="text-danger">*</span></label>
                <div id="colorItemsContainer">
        `;

                    const defaultItems = items.length > 0 ? items : [
                        { id: 1, text: '', category: '' },
                        { id: 2, text: '', category: '' }
                    ];

                    defaultItems.forEach((item, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Текст</label>
                        <input type="text" name="config[items][${index}][text]"
                               placeholder="Кошка" value="${escapeHtml(item.text || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Категория</label>
                        <input type="text" name="config[items][${index}][category]"
                               placeholder="animal" value="${escapeHtml(item.category || '')}">
                    </div>
                    <input type="hidden" name="config[items][${index}][id]" value="${item.id || index + 1}">
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addColorItem()">
                    <i class="bx bx-plus"></i> Добавить элемент
                </button>
            </div>

            <div class="config-field-group">
                <label>Категории <span class="text-danger">*</span></label>
                <div id="colorCategoriesContainer">
        `;

                    const defaultCategories = categories.length > 0 ? categories : [
                        { id: 'animal', name: 'Животные', color: '#4CAF50' },
                        { id: 'object', name: 'Предметы', color: '#F44336' }
                    ];

                    defaultCategories.forEach((cat, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">ID</label>
                        <input type="text" name="config[categories][${index}][id]"
                               placeholder="animal" value="${escapeHtml(cat.id || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Название</label>
                        <input type="text" name="config[categories][${index}][name]"
                               placeholder="Животные" value="${escapeHtml(cat.name || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Цвет</label>
                        <input type="color" name="config[categories][${index}][color]"
                               value="${cat.color || '#4CAF50'}" style="height: 38px;">
                    </div>
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addColorCategory()">
                    <i class="bx bx-plus"></i> Добавить категорию
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_items]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать элементы</label>
                </div>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addColorItem = function() {
                    const container = document.getElementById('colorItemsContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Текст</label>
                <input type="text" name="config[items][${count}][text]" placeholder="Текст" value="">
            </div>
            <div>
                <label class="pair-label">Категория</label>
                <input type="text" name="config[items][${count}][category]" placeholder="category" value="">
            </div>
            <input type="hidden" name="config[items][${count}][id]" value="${count + 1}">
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                window.addColorCategory = function() {
                    const container = document.getElementById('colorCategoriesContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const colors = ['#4CAF50', '#F44336', '#2196F3', '#FF9800', '#9C27B0', '#00BCD4'];
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">ID</label>
                <input type="text" name="config[categories][${count}][id]" placeholder="id" value="">
            </div>
            <div>
                <label class="pair-label">Название</label>
                <input type="text" name="config[categories][${count}][name]" placeholder="Название" value="">
            </div>
            <div>
                <label class="pair-label">Цвет</label>
                <input type="color" name="config[categories][${count}][color]" value="${colors[count % colors.length]}" style="height: 38px;">
            </div>
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // РЕНДЕРИНГ "Сопоставь пары" (match_pairs)
                // ============================================================
                function renderMatchPairs(config) {
                    const editor = document.getElementById('configEditor');
                    if (!editor) return;

                    const pairs = config.pairs || [];
                    const shuffle = config.shuffle_pairs ?? true;
                    const timeLimit = config.time_limit || 30;

                    let html = `
            <div class="config-field-group">
                <label>Пары для сопоставления <span class="text-danger">*</span></label>
                <div id="matchPairsContainer">
        `;

                    const defaultPairs = pairs.length > 0 ? pairs : [
                        { left: '', right: '', image: '' },
                        { left: '', right: '', image: '' }
                    ];

                    defaultPairs.forEach((pair, index) => {
                        html += `
                <div class="pair-card" style="grid-template-columns: 1fr 1fr 1fr auto;">
                    <div>
                        <label class="pair-label">Левая часть</label>
                        <input type="text" name="config[pairs][${index}][left]"
                               placeholder="Ингушское слово" value="${escapeHtml(pair.left || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Правая часть</label>
                        <input type="text" name="config[pairs][${index}][right]"
                               placeholder="Перевод" value="${escapeHtml(pair.right || '')}">
                    </div>
                    <div>
                        <label class="pair-label">Изображение</label>
                        <input type="text" name="config[pairs][${index}][image]"
                               placeholder="/images/example.jpg" value="${escapeHtml(pair.image || '')}">
                    </div>
                    <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            `;
                    });

                    html += `
                </div>
                <button type="button" class="btn-add-item" onclick="addMatchPair()">
                    <i class="bx bx-plus"></i> Добавить пару
                </button>
            </div>

            <div class="config-field-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="config[shuffle_pairs]"
                           value="1" ${shuffle ? 'checked' : ''}>
                    <label class="form-check-label">Перемешивать пары</label>
                </div>
            </div>

            <div class="config-field-group">
                <label>Лимит времени (секунд)</label>
                <input type="number" class="form-control" name="config[time_limit]"
                       value="${timeLimit}" min="0">
                <small class="text-muted">0 - без лимита</small>
            </div>
        `;

                    editor.innerHTML = html;
                }

                window.addMatchPair = function() {
                    const container = document.getElementById('matchPairsContainer');
                    if (!container) return;
                    const count = container.querySelectorAll('.pair-card').length;
                    const div = document.createElement('div');
                    div.className = 'pair-card';
                    div.style.gridTemplateColumns = '1fr 1fr 1fr auto';
                    div.innerHTML = `
            <div>
                <label class="pair-label">Левая часть</label>
                <input type="text" name="config[pairs][${count}][left]" placeholder="Левая часть" value="">
            </div>
            <div>
                <label class="pair-label">Правая часть</label>
                <input type="text" name="config[pairs][${count}][right]" placeholder="Правая часть" value="">
            </div>
            <div>
                <label class="pair-label">Изображение</label>
                <input type="text" name="config[pairs][${count}][image]" placeholder="/images/example.jpg" value="">
            </div>
            <button type="button" class="btn-remove-item" onclick="this.closest('.pair-card').remove(); updateJsonPreview();">
                <i class="bx bx-x"></i>
            </button>
        `;
                    container.appendChild(div);
                    updateJsonPreview();
                };

                // ============================================================
                // АВТООБНОВЛЕНИЕ JSON ПРИ ИЗМЕНЕНИИ ПОЛЕЙ
                // ============================================================
                document.addEventListener('change', function(e) {
                    if (e.target.closest('.config-field-group') || e.target.closest('.config-card')) {
                        updateJsonPreview();
                    }
                });

                document.addEventListener('input', function(e) {
                    if (e.target.closest('.config-field-group') || e.target.closest('.config-card')) {
                        updateJsonPreview();
                    }
                });
            });
        </script>
    @endpush
@endsection
