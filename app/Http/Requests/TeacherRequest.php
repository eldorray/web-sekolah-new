<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
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
        $record = $this->route('teacher');
        $teacher = $record instanceof User ? $record : null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($teacher?->id),
            ],
            'position' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:240'],
            'instagram' => ['nullable', 'url', 'max:200'],
            'facebook' => ['nullable', 'url', 'max:200'],
            'role' => ['required', Rule::in(['admin', 'guru'])],
            'is_active' => ['required', 'boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => [$teacher === null ? 'required' : 'nullable', 'string', 'min:8'],
        ];
    }
}
