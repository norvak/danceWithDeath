<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    /** @return list<string> */
    public function times(): array
    {
        return array_map(
            static fn (int $hour): string => sprintf('%02d:00', $hour),
            range(9, 18)
        );
    }

    /** @return list<array{time: string, available: bool, status: string}> */
    public function forDate(string $date): array
    {
        $occupiedTimes = Appointment::query()
            ->whereDate('appointment_date', $date)
            ->pluck('appointment_time')
            ->map(static fn (mixed $time): string => substr((string) $time, 0, 5))
            ->all();

        $now = CarbonImmutable::now(config('app.timezone'));

        return array_map(function (string $time) use ($date, $occupiedTimes, $now): array {
            $occupied = in_array($time, $occupiedTimes, true);
            $slot = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                "{$date} {$time}",
                config('app.timezone')
            );
            $past = $slot->lessThan($now);

            return [
                'time' => $time,
                'available' => ! $occupied && ! $past,
                'status' => $occupied ? 'occupied' : ($past ? 'unavailable' : 'available'),
            ];
        }, $this->times());
    }
}
