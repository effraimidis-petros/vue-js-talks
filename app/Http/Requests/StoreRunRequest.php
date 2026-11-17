<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRunRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ran_on' => ['required', 'date'],
            'distance_km' => ['required', 'numeric', 'max:999'],
            'duration' => ['required', 'string', 'regex:/^(\d+:)?\d{1,2}:[0-5]\d$/'],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'duration.regex' => __('Use the format mm:ss or h:mm:ss.'),
        ];
    }

    /**
     * The validated duration ("45:30" or "1:05:30") in seconds.
     */
    public function durationInSeconds(): int
    {
        $parts = array_map('intval', explode(':', $this->validated('duration')));

        return array_reduce($parts, fn (int $total, int $part): int => $total * 60 + $part, 0);
    }
}
