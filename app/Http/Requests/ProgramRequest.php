<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:150'],
            'icon' => ['required', 'string', 'max:60'],
            'badge' => ['nullable', 'string', 'max:40'],
            'short_description' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:60000'],
            'order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
