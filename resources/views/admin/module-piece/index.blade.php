@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Разделы</h5>
                    <a href="{{ route('admin.educationModulesPieces.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Создать раздел
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Модуль</th>
                                <th>Уроков</th>
                                <th>Заданий</th>
                                <th>XP</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($pieces as $piece)
                                <tr>
                                    <td>{{ $piece->id }}</td>
                                    <td>
                                        @if($piece->fon)
                                            <img src="{{ $piece->fon }}" alt="" width="40" height="40" class="rounded me-2" style="object-fit: cover;">
                                        @endif
                                        {{ $piece->name }}
                                    </td>
                                    <td>{{ $piece->educationModule?->name ?? 'Не указан' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $piece->total_lessons }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $piece->total_tasks }}</span>
                                    </td>
                                    <td>{{ $piece->total_xp ?? 0 }}</td>
                                    <td>
                                    <span class="badge bg-{{ $piece->is_published ? 'success' : 'secondary' }}">
                                        {{ $piece->is_published ? 'Опубликован' : 'Черновик' }}
                                    </span>
                                        @if($piece->is_required)
                                            <span class="badge bg-primary">Обязательный</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="{{ route('admin.educationModulesPieces.edit', $piece) }}" class="dropdown-item">
                                                        <i class="bx bx-edit"></i> Редактировать
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.lessons.index', ['piece_id' => $piece->id]) }}" class="dropdown-item">
                                                        <i class="bx bx-list-ul"></i> Уроки ({{ $piece->total_lessons }})
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.tasks.index', ['piece_id' => $piece->id]) }}" class="dropdown-item">
                                                        <i class="bx bx-task"></i> Задания ({{ $piece->total_tasks }})
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.educationModulesPieces.delete', $piece) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Удалить раздел "{{ $piece->name }}" и все его уроки и задания?')">
                                                        <i class="bx bx-trash"></i> Удалить
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bx bx-folder-open bx-lg text-muted d-block mb-2"></i>
                                        <span class="text-muted">Нет разделов. Создайте первый раздел!</span>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $pieces->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
