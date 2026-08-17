<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_it_returns_all_slots_and_marks_an_existing_appointment_as_occupied(): void
    {
        CarbonImmutable::setTestNow('2026-08-17 08:00:00');
        Appointment::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'appointment_date' => '2026-08-18',
            'appointment_time' => '10:00',
        ]);

        $response = $this->getJson('/api/availability?date=2026-08-18');

        $response
            ->assertOk()
            ->assertJsonCount(10, 'slots')
            ->assertJsonFragment([
                'time' => '10:00',
                'available' => false,
                'status' => 'occupied',
            ]);
    }

    public function test_it_rejects_weekends_and_past_dates_for_availability(): void
    {
        CarbonImmutable::setTestNow('2026-08-17 08:00:00');

        $this->getJson('/api/availability?date=2026-08-16')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');

        $this->getJson('/api/availability?date=2026-08-22')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    }

    public function test_it_books_an_appointment_and_normalizes_customer_data(): void
    {
        CarbonImmutable::setTestNow('2026-08-17 08:00:00');

        $response = $this->postJson('/api/appointments', [
            'name' => '  Jane Doe  ',
            'email' => '  JANE@EXAMPLE.COM  ',
            'appointment_date' => '2026-08-18',
            'appointment_time' => '09:00',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Jane Doe')
            ->assertJsonPath('data.email', 'jane@example.com')
            ->assertJsonPath('data.time', '09:00');

        $this->assertDatabaseHas('appointments', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'appointment_date' => '2026-08-18',
            'appointment_time' => '09:00',
        ]);
    }

    public function test_it_rejects_duplicate_emails_and_occupied_slots(): void
    {
        CarbonImmutable::setTestNow('2026-08-17 08:00:00');
        Appointment::query()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'appointment_date' => '2026-08-18',
            'appointment_time' => '09:00',
        ]);

        $this->postJson('/api/appointments', [
            'name' => 'Jane Again',
            'email' => 'JANE@EXAMPLE.COM',
            'appointment_date' => '2026-08-18',
            'appointment_time' => '10:00',
        ])
            ->assertConflict()
            ->assertJsonPath(
                'message',
                'An appointment is already registered for this email address.'
            )
            ->assertJsonPath(
                'errors.email.0',
                'Use a different email address to book another appointment.'
            );

        $this->postJson('/api/appointments', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'appointment_date' => '2026-08-18',
            'appointment_time' => '09:00',
        ])->assertConflict()->assertJsonValidationErrors('appointment_time');
    }

    public function test_it_rejects_invalid_and_past_time_slots(): void
    {
        CarbonImmutable::setTestNow('2026-08-17 10:30:00');

        $this->postJson('/api/appointments', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'appointment_date' => '2026-08-17',
            'appointment_time' => '09:00',
        ])->assertUnprocessable()->assertJsonValidationErrors('appointment_time');

        $this->postJson('/api/appointments', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'appointment_date' => '2026-08-18',
            'appointment_time' => '09:30',
        ])->assertUnprocessable()->assertJsonValidationErrors('appointment_time');
    }

    public function test_it_rejects_missing_customer_data(): void
    {
        CarbonImmutable::setTestNow('2026-08-17 08:00:00');

        $this->postJson('/api/appointments', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'appointment_date',
                'appointment_time',
            ]);
    }
}
