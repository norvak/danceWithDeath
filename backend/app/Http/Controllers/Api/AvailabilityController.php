<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AvailabilityRequest;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        AvailabilityRequest $request,
        AvailabilityService $availabilityService
    ): JsonResponse {
        $date = $request->validated('date');

        return response()->json([
            'date' => $date,
            'slots' => $availabilityService->forDate($date),
        ]);
    }
}
