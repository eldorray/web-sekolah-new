<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $record = $this->route('user');
        $user = $record instanceof User ? $record : null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'role' => ['required', Rule::in(['admin', 'guru'])],
            'is_active' => ['required', 'boolean'],
            'password' => [$user === null ? 'required' : 'nullable', 'string', 'min:8'],
        ];
    }
}
