<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAppointmentRequest;
use App\Models\Appointment;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (Appointment::query()->where('email', $data['email'])->exists()) {
            return $this->conflict(
                'email',
                'An appointment is already registered for this email address.',
                'Use a different email address to book another appointment.'
            );
        }

        if (Appointment::query()
            ->whereDate('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->exists()) {
            return $this->conflict(
                'appointment_time',
                'This appointment time is no longer available.',
                'Choose another available time to continue.'
            );
        }

        try {
            $appointment = Appointment::query()->create($data);
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000' && $exception->getCode() !== '23505') {
                throw $exception;
            }

            return $this->conflict(
                'appointment',
                'The email address or appointment slot is no longer available.'
            );
        }

        return response()->json([
            'message' => 'Appointment booked successfully.',
            'data' => [
                'id' => $appointment->id,
                'name' => $appointment->name,
                'email' => $appointment->email,
                'date' => $appointment->appointment_date->format('Y-m-d'),
                'time' => substr($appointment->appointment_time, 0, 5),
            ],
        ], 201);
    }

    private function conflict(
        string $field,
        string $message,
        string $resolution = 'Choose another email address or appointment time.'
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'errors' => [
                $field => [$resolution],
            ],
        ], 409);
    }
}
