<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query()->with(['event', 'seller']);

        // Filter: search (nama event, venue, kota, atau tipe tiket)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('event', function ($eventQuery) use ($search) {
                    $eventQuery->where('event_name', 'like', "%{$search}%")
                        ->orWhere('venue_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                })->orWhere('ticket_type', 'like', "%{$search}%");
            });
        }

        // Filter: min_price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        // Filter: max_price
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Filter: verified tickets
        if ($request->has('verified') || $request->has('is_verified')) {
            $val = $request->has('verified') ? $request->input('verified') : $request->input('is_verified');
            $query->where('is_verified', filter_var($val, FILTER_VALIDATE_BOOLEAN));
        }

        // Urutkan tiket terbaru dahulu
        $query->latest();

        $tickets = $query->paginate(15);
        $paginated = TicketResource::collection($tickets)->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => 'Tickets retrieved successfully',
            'data' => $paginated['data'],
            'links' => $paginated['links'],
            'meta' => $paginated['meta'],
        ]);
    }

    /**
     * Display the specified ticket.
     */
    public function show(string $id): JsonResponse
    {
        $ticket = Ticket::with(['event', 'seller'])->findOrFail($id);

        return ApiResponse::success(
            new TicketResource($ticket),
            'Ticket retrieved successfully'
        );
    }

    /**
     * Store a newly created ticket listing in storage.
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('ticket_file')) {
            $path = $request->file('ticket_file')->store('ticket_proofs', 'local');
            $validated['ticket_file_path'] = $path;
        }

        unset($validated['ticket_file']);
        $validated['seller_id'] = $request->user()->id;

        $ticket = Ticket::create($validated);
        $ticket->load(['event', 'seller']);

        return ApiResponse::success(
            new TicketResource($ticket),
            'Ticket listed successfully',
            201
        );
    }
}
