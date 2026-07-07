<?php

namespace App\Http\Resources\Frontend\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EducationModuleCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = EducationModuleResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'school_class_type_id' => $request->user()?->school_class_type_id,
                'user_class_type' => $request->user()?->schoolClassType?->name ?? 'Не указан',
                'user_school_class' => $request->user()?->schoolClass?->name ?? 'Не указан',
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Модули успешно получены',
        ];
    }
}
