@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <h5 class="card-header">Создание типа задания</h5>

                @if ($errors->any())
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h6>Ошибки валидации:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <form class="card-body" action="{{ route('admin.taskTypes.store') }}" method="POST">
                    @csrf

                    <div class="row g-6">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Например: Сопоставление" name="name" value="{{ old('name') }}" required>
                                <label for="name">Название типа</label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" id="slug" class="form-control @error('slug') is-invalid @enderror"
                                       placeholder="match_pairs" name="slug" value="{{ old('slug') }}" required>
                                <label for="slug">Slug (уникальный идентификатор)</label>
                                @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Только латиница, нижнее подчеркивание. Например: <code>match_pairs</code></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" id="icon" class="form-control @error('icon') is-invalid @enderror"
                                       placeholder="bx bx-link" name="icon" value="{{ old('icon') }}">
                                <label for="icon">Иконка (CSS класс)</label>
                                @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Например: <code>bx bx-puzzle-piece</code> или <code>fas fa-puzzle-piece</code></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       placeholder="0" name="sort_order" value="{{ old('sort_order', 0) }}">
                                <label for="sort_order">Порядок сортировки</label>
                                @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating form-floating-outline mb-4">
                            <textarea id="description" class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Описание типа задания" name="description" rows="3">{{ old('description') }}</textarea>
                                <label for="description">Описание</label>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Дефолтный конфиг (JSON)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-floating form-floating-outline mb-4">
                                    <textarea id="default_config" class="form-control @error('default_config') is-invalid @enderror"
                                              placeholder='{"question": "", "options": []}' name="default_config" rows="10"
                                              style="font-family: monospace; font-size: 14px;">{{ old('default_config', '{}') }}</textarea>
                                        <label for="default_config">JSON конфиг по умолчанию</label>
                                        @error('default_config')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">
                                            Укажите структуру данных для этого типа задания.
                                            <a href="#" onclick="document.getElementById('default_config').value = '{}'; return false;">Очистить</a>
                                        </small>
                                    </div>
                                    <div class="alert alert-info">
                                        <strong>Примеры:</strong>
                                        <pre class="mb-0 mt-2" style="font-size: 12px;">
                                            <code>{
                                                "question": "",
                                                "options": [
                                                    {"id": "a", "text": "", "is_correct": false},
                                                    {"id": "b", "text": "", "is_correct": false},
                                                    {"id": "c", "text": "", "is_correct": false},
                                                    {"id": "d", "text": "", "is_correct": false}
                                                ],
                                                "shuffle_options": true
                                            }</code>
                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mb-4 mt-2">
                                <input class="form-check-input" type="checkbox" id="is_active"
                                       name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Активен</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="reset" class="btn btn-outline-secondary waves-effect">Отмена</button>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Создать тип</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Автогенерация slug из названия
            document.getElementById('name').addEventListener('input', function() {
                const slugInput = document.getElementById('slug');
                if (!slugInput.value || slugInput.dataset.generated) {
                    const slug = this.value
                        .toLowerCase()
                        .replace(/[^a-zа-яё\s]/g, '')
                        .replace(/\s+/g, '_')
                        .replace(/[а-яё]/g, function(char) {
                            const map = {
                                'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
                                'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
                                'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
                                'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
                                'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch',
                                'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '',
                                'э': 'e', 'ю': 'yu', 'я': 'ya'
                            };
                            return map[char] || char;
                        });
                    slugInput.value = slug;
                    slugInput.dataset.generated = 'true';
                }
            });

            // Если пользователь вручную редактирует slug, отключаем автогенерацию
            document.getElementById('slug').addEventListener('input', function() {
                this.dataset.generated = 'false';
            });

            // Валидация JSON перед отправкой формы
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const configTextarea = document.getElementById('default_config');
                const configValue = configTextarea.value.trim();

                if (configValue && configValue !== '{}') {
                    try {
                        JSON.parse(configValue);
                    } catch (e) {
                        e.preventDefault();
                        alert('Ошибка в JSON конфиге: ' + e.message);
                        configTextarea.classList.add('is-invalid');
                        configTextarea.focus();
                        return false;
                    }
                }
            });

            // Убираем ошибку при изменении
            document.getElementById('default_config').addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });
    </script>
@endpush
