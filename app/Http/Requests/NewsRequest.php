<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\News;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $news = $this->route('news');

        return [
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::in(News::CATEGORIES)],
            'excerpt' => ['nullable', 'string', 'max:200'],
            'content' => ['nullable', 'string', 'max:60000'],
            'published_at' => ['required', 'date'],
            'is_published' => ['required', 'boolean'],
            'image' => [
                $news === null ? 'nullable' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }
}
