@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Уроки</h5>
                    <a href="{{ route('admin.lessons.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Создать урок
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Раздел</th>
                                <th>Заданий</th>
                                <th>XP</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($lessons as $lesson)
                                <tr>
                                    <td>{{ $lesson->id }}</td>
                                    <td>{{ $lesson->name }}</td>
                                    <td>{{ $lesson->piece?->name ?? 'Не указан' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $lesson->tasks()->count() }}</span>
                                    </td>
                                    <td>{{ $lesson->xp_reward ?? 10 }}</td>
                                    <td>
                                    <span class="badge bg-{{ $lesson->is_published ? 'success' : 'secondary' }}">
                                        {{ $lesson->is_published ? 'Опубликован' : 'Черновик' }}
                                    </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="dropdown-item">
                                                        <i class="bx bx-edit"></i> Редактировать
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.tasks.index', ['lesson_id' => $lesson->id]) }}" class="dropdown-item">
                                                        <i class="bx bx-list-ul"></i> Задания
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.lessons.delete', $lesson) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Удалить урок?')">
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
                                    <td colspan="7" class="text-center">Нет уроков</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $lessons->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
