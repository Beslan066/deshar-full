@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <h5 class="card-header">Создание урока</h5>

                {{-- БЛОК ОБЩИХ ОШИБОК --}}
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

                <form class="card-body" action="{{ route('admin.lessons.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-6">
                        {{-- НАЗВАНИЕ --}}
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Например: Буква А" name="name" value="{{ old('name') }}" required>
                                <label for="name">Название урока <span class="text-danger">*</span></label>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- РАЗДЕЛ --}}
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline mb-4">
                                <select id="piece_id" class="form-select @error('piece_id') is-invalid @enderror" name="piece_id" required>
                                    <option value="">Выберите раздел</option>
                                    @foreach($pieces as $piece)
                                        <option value="{{ $piece->id }}" {{ old('piece_id') == $piece->id ? 'selected' : '' }}>
                                            {{ $piece->educationModule?->name ?? '' }} → {{ $piece->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="piece_id">Раздел <span class="text-danger">*</span></label>
                                @error('piece_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ПОРЯДОК --}}
                        <div class="col-md-4">
                            <div class="form-floating form-floating-outline mb-4">
                                <input type="number" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                                       placeholder="0" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                <label for="sort_order">Порядок</label>
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
                                       placeholder="15" name="estimated_time" value="{{ old('estimated_time', 15) }}" min="1">
                                <label for="estimated_time">Время (мин)</label>
                                @error('estimated_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- ОПИСАНИЕ --}}
                        {{-- Вместо textarea --}}
                        <div class="col-12">
                            <div class="mb-4">
                                <label for="description" class="form-label">Описание</label>
                                <div id="editor" style="height: 300px;">{!! old('description') !!}</div>
                                <textarea id="description" name="description" style="display:none;">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>



                        {{-- МЕДИАФАЙЛЫ (АУДИО, ФОТО, ВИДЕО) --}}
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Медиафайлы для урока</h6>
                                    <small class="text-muted">Добавьте аудио, изображение или видео для урока</small>
                                </div>
                                <div class="card-body">
                                    <div class="row g-4">
                                        {{-- АУДИО --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="audio" class="form-label">
                                                    <i class="bx bx-music me-1"></i> Аудио
                                                </label>
                                                <input type="file" id="audio" class="form-control @error('audio') is-invalid @enderror"
                                                       name="audio" accept="audio/*">
                                                @error('audio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">MP3, WAV, OGG. Макс. 10MB</small>
                                            </div>
                                        </div>

                                        {{-- ФОТО --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="image" class="form-label">
                                                    <i class="bx bx-image me-1"></i> Изображение
                                                </label>
                                                <input type="file" id="image" class="form-control @error('image') is-invalid @enderror"
                                                       name="image" accept="image/*">
                                                @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">JPG, PNG, GIF, WebP. Макс. 5MB</small>
                                            </div>
                                        </div>

                                        {{-- ВИДЕО --}}
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="video" class="form-label">
                                                    <i class="bx bx-video me-1"></i> Видео
                                                </label>
                                                <input type="file" id="video" class="form-control @error('video') is-invalid @enderror"
                                                       name="video" accept="video/*">
                                                @error('video')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">MP4, WebM, OGG. Макс. 50MB</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- СТАТУСЫ --}}
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-4 mt-2">
                                <input class="form-check-input" type="checkbox" id="is_published"
                                       name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Опубликовать</label>
                            </div>
                            <small class="text-muted text-warning">⚠️ Если не поставить галочку, урок будет сохранен как черновик</small>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch mb-4 mt-2">
                                <input class="form-check-input" type="checkbox" id="is_required"
                                       name="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_required">Обязательный</label>
                            </div>
                        </div>

                        {{-- КНОПКИ --}}
                        <div class="col-12">
                            <a href="{{ route('admin.lessons.index') }}" class="btn btn-outline-secondary waves-effect">Отмена</a>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">Создать урок</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });

            // Обновляем textarea при каждом изменении в редакторе
            quill.on('text-change', function() {
                var html = quill.root.innerHTML;
                document.querySelector('#description').value = html;
            });

            // Также обновляем при отправке формы
            document.querySelector('form').addEventListener('submit', function() {
                var html = quill.root.innerHTML;
                document.querySelector('#description').value = html;
            });

            // Если есть старое значение - устанавливаем его в редактор
            var oldDescription = document.querySelector('#description').value;
            if (oldDescription) {
                quill.root.innerHTML = oldDescription;
            }
        });
    </script>
@endpush
