<?php

namespace App\Http\Requests\Api;

use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Throwable;

class StoreAppointmentRequest extends FormRequest
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
            'name' => ['bail', 'required', 'string', 'max:120'],
            'email' => ['bail', 'required', 'email', 'max:255'],
            'appointment_date' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function (string $attribute, mixed $value, Closure $fail): void {
                    try {
                        if (! CarbonImmutable::createFromFormat('Y-m-d', $value)->isWeekday()) {
                            $fail('The appointment date must be a business day.');
                        }
                    } catch (Throwable) {
                        // The date_format rule provides the validation message.
                    }
                },
            ],
            'appointment_time' => [
                'bail',
                'required',
                'date_format:H:i',
                Rule::in(app(AvailabilityService::class)->times()),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? strtolower(trim($this->email)) : $this->email,
        ]);
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['appointment_date', 'appointment_time'])) {
                    return;
                }

                $appointment = CarbonImmutable::createFromFormat(
                    'Y-m-d H:i',
                    "{$this->appointment_date} {$this->appointment_time}",
                    config('app.timezone')
                );

                if ($appointment->isPast()) {
                    $validator->errors()->add(
                        'appointment_time',
                        'The appointment time cannot be in the past.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A name is required.',
            'email.required' => 'An email address is required.',
            'email.email' => 'The email address must be valid.',
            'appointment_date.required' => 'An appointment date is required.',
            'appointment_date.date_format' => 'The appointment date must use the YYYY-MM-DD format.',
            'appointment_date.after_or_equal' => 'The appointment date cannot be in the past.',
            'appointment_time.required' => 'An appointment time is required.',
            'appointment_time.date_format' => 'The appointment time must use the HH:MM format.',
            'appointment_time.in' => 'The appointment time must be an available one-hour slot between 09:00 and 19:00.',
        ];
    }
}
