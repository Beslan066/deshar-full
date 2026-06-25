@extends('layouts.admin')

@section('content')
    <div class="content-wrapper">
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Задания</h5>
                    <div>
                        @if(request()->has('lesson_id'))
                            <a href="{{ route('admin.tasks.create', ['lesson_id' => request()->lesson_id]) }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Добавить задание
                            </a>
                        @else
                            <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> Добавить задание
                            </a>
                        @endif
                    </div>
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
                                <th>Урок</th>
                                <th>Тип</th>
                                <th>Попыток</th>
                                <th>XP</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>{{ $task->id }}</td>
                                    <td>{{ $task->title ?? 'Без названия' }}</td>
                                    <td>{{ $task->lesson?->name ?? 'Не указан' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $task->taskType?->name ?? 'Не указан' }}</span>
                                    </td>
                                    <td>{{ $task->max_attempts ?? 3 }}</td>
                                    <td>{{ $task->xp_reward ?? 10 }}</td>
                                    <td>
                                    <span class="badge bg-{{ $task->is_published ? 'success' : 'secondary' }}">
                                        {{ $task->is_published ? 'Опубликовано' : 'Черновик' }}
                                    </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Действия
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="dropdown-item">
                                                        <i class="bx bx-edit"></i> Редактировать
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Удалить задание?')">
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
                                    <td colspan="8" class="text-center">Нет заданий</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
