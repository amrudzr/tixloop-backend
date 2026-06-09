<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a paginated listing of events with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('event_name', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('event_category', $request->input('category'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        $query->latest('event_datetime');

        $events = $query->paginate(
            min((int) $request->input('per_page', 15), 50)
        );

        $paginated = EventResource::collection($events)->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => 'Events retrieved successfully',
            'data' => $paginated['data'],
            'links' => $paginated['links'],
            'meta' => $paginated['meta'],
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show(string $id): JsonResponse
    {
        $event = Event::findOrFail($id);

        return ApiResponse::success(
            new EventResource($event),
            'Event retrieved successfully'
        );
    }
}
