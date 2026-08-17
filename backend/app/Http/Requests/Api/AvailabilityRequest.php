<?php

namespace App\Http\Requests\Api;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Throwable;

class AvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function (string $attribute, mixed $value, Closure $fail): void {
                    try {
                        if (! CarbonImmutable::createFromFormat('Y-m-d', $value)->isWeekday()) {
                            $fail('The selected date must be a business day.');
                        }
                    } catch (Throwable) {
                        // The date_format rule provides the validation message.
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'A date is required.',
            'date.date_format' => 'The date must use the YYYY-MM-DD format.',
            'date.after_or_equal' => 'The date cannot be in the past.',
        ];
    }
}
