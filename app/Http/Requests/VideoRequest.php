<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Video;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VideoRequest extends FormRequest
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
            'youtube' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:200'],
            'order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Rejects anything the parser cannot turn into a video id, so a typo never
     * reaches the page as a broken embed.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('youtube')) {
                    return;
                }

                if (Video::parseYoutubeId((string) $this->string('youtube')) === null) {
                    $validator->errors()->add(
                        'youtube',
                        'Tautan YouTube tidak dikenali. Tempel alamat video atau ID-nya.',
                    );
                }
            },
        ];
    }

    public function youtubeId(): string
    {
        return (string) Video::parseYoutubeId((string) $this->string('youtube'));
    }
}
