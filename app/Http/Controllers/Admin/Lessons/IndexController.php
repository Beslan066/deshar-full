<?php

namespace App\Http\Controllers\Admin\Lessons;

use App\Http\Controllers\Controller;
use App\Models\EducationModulePiece;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lesson::with('piece');

        // Фильтр по разделу
        if ($request->has('piece_id') && $request->piece_id) {
            $query->where('piece_id', $request->piece_id);
        }

        $lessons = $query->orderBy('sort_order')->paginate(20);

        return view('admin.lessons.index', compact('lessons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pieces = EducationModulePiece::where('is_published', true)->get();
        return view('admin.lessons.create', compact('pieces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'piece_id' => 'required|exists:education_module_pieces,id',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'xp_reward' => 'nullable|integer',
            'estimated_time' => 'nullable|integer',
            // Медиа
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->except(['audio', 'image', 'video']);
        $data['slug'] = Str::slug($request->name);
        $data['is_published'] = $request->has('is_published');
        $data['is_required'] = $request->has('is_required');
        $data['sort_order'] = $request->sort_order ?? 0;

        // ============================================================
        // ОБРАБОТКА МЕДИАФАЙЛОВ
        // ============================================================

        // Аудио
        if ($request->hasFile('audio')) {
            $file = $request->file('audio');
            if ($file->isValid()) {
                $path = $file->store('lessons/audio', 'public');
                $data['audio'] = $path;
            }
        }

        // Изображение
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $path = $file->store('lessons/images', 'public');
                $data['image'] = $path;
            }
        }

        // Видео
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            if ($file->isValid()) {
                $path = $file->store('lessons/videos', 'public');
                $data['video'] = $path;
            }
        }

        $lesson = Lesson::create($data);

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', 'Урок "' . $lesson->name . '" успешно создан!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        $pieces = EducationModulePiece::where('is_published', true)->get();
        return view('admin.lessons.edit', compact('lesson', 'pieces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'piece_id' => 'required|exists:education_module_pieces,id',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
            'is_required' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'xp_reward' => 'nullable|integer',
            'estimated_time' => 'nullable|integer',
            // Медиа
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,ogg|max:51200',
        ]);

        $data = $request->except(['audio', 'image', 'video']);
        $data['slug'] = Str::slug($request->name);
        $data['is_published'] = $request->has('is_published');
        $data['is_required'] = $request->has('is_required');

        // ============================================================
        // ОБРАБОТКА МЕДИАФАЙЛОВ
        // ============================================================

        // Аудио
        if ($request->hasFile('audio')) {
            // Удаляем старый файл
            if ($lesson->audio) {
                Storage::disk('public')->delete($lesson->audio);
            }

            $file = $request->file('audio');
            if ($file->isValid()) {
                $path = $file->store('lessons/audio', 'public');
                $data['audio'] = $path;
            }
        }

        // Изображение
        if ($request->hasFile('image')) {
            if ($lesson->image) {
                Storage::disk('public')->delete($lesson->image);
            }

            $file = $request->file('image');
            if ($file->isValid()) {
                $path = $file->store('lessons/images', 'public');
                $data['image'] = $path;
            }
        }

        // Видео
        if ($request->hasFile('video')) {
            if ($lesson->video) {
                Storage::disk('public')->delete($lesson->video);
            }

            $file = $request->file('video');
            if ($file->isValid()) {
                $path = $file->store('lessons/videos', 'public');
                $data['video'] = $path;
            }
        }

        $lesson->update($data);

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', 'Урок "' . $lesson->name . '" успешно обновлен!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $name = $lesson->name;

        // Удаляем медиафайлы
        if ($lesson->audio) {
            Storage::disk('public')->delete($lesson->audio);
        }
        if ($lesson->image) {
            Storage::disk('public')->delete($lesson->image);
        }
        if ($lesson->video) {
            Storage::disk('public')->delete($lesson->video);
        }

        $lesson->delete();

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', 'Урок "' . $name . '" успешно удален!');
    }
}
