<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePpdbRegistrationRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:120'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'gender' => ['required', 'in:L,P'],
            'birthplace' => ['required', 'string', 'max:80'],
            'birthdate' => ['required', 'date', 'before:today'],
            'previous_school' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'father_name' => ['required', 'string', 'max:120'],
            'mother_name' => ['required', 'string', 'max:120'],
            'parent_phone' => ['required', 'string', 'max:30'],
            'parent_email' => ['required', 'email', 'max:120'],
            'grade_target' => ['required', 'string', 'max:60'],
            'kk_file' => ['required', 'file', 'mimes:pdf', 'max:2048'],
            'birth_certificate_file' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kk_file.mimes' => 'Kartu keluarga harus berkas PDF.',
            'birth_certificate_file.mimes' => 'Akta kelahiran harus JPG atau PNG.',
        ];
    }
}
