<?php

namespace App\Http\Controllers\Api\ExternalApi;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * @group Events
 *
 * API for events (read-only)
 *
 */
class EventController extends Controller
{
    /**
     * Get events for the current month where the user is an attendee.
     *
     * Returns a list of events for the authenticated user, filtered to the current month.
     *
     * @authenticated
     * @header Content-Type application/json
     * @header Accept application/json
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     * @header X-App-Authentication {YOUR_APP_TOKEN}
     * @response 200 {
     *   "message": "Events retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "start": "2025-10-05",
     *       "end": "2025-10-05",
     *       "title": "Team Meeting",
     *       "description": "Weekly sync"
     *     },
     *     {
     *       "id": 2,
     *       "start": "2025-10-10 14:00",
     *       "end": "2025-10-10 15:00",
     *       "title": "Client Call",
     *       "description": "Discuss project"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Payment required"
     * }
     */
    public function calendar(Request $request): JsonResponse
    {
        $user = $request->user();

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $events = Event::where(function ($query) use ($user) {
            $query->whereHas('attendees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        })
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('start', [$startOfMonth, $endOfMonth]);
            })
            ->get()
            ->map(fn ($event) => $event->toCalendarArray())
            ->values();

        return response()->json([
            'message' => __('ui.model_retrieved', ['model' => __('ui.event')]),
            'data' => $events,
        ]);
    }
}
