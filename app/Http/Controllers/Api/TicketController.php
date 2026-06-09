<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\OwnershipHistoryResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService,
    ) {}

    /**
     * Display a listing of tickets with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Ticket::class);

        $user = $request->user();
        $query = Ticket::query()->with(['event', 'currentOwner']);

        if (! $user->hasRole('admin')) {
            $query->where('current_owner_id', $user->id);
        }

        // Filter: search (nama event, venue, kota)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('event', function ($eventQuery) use ($search) {
                    $eventQuery->where('event_name', 'like', "%{$search}%")
                        ->orWhere('venue_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            });
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
        $ticket = Ticket::with(['event', 'currentOwner'])->findOrFail($id);

        Gate::authorize('view', $ticket);

        return ApiResponse::success(
            new TicketResource($ticket),
            'Ticket retrieved successfully'
        );
    }

    /**
     * Upload a new ticket with proof file.
     */
    public function upload(StoreTicketRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $ticket = $this->ticketService->uploadTicket(
            user: $request->user(),
            data: $validated,
            ticketProof: $request->file('ticket_proof'),
            physicalPhoto: $request->file('physical_photo'),
        );

        $ticket->load(['event', 'currentOwner']);

        return ApiResponse::success(
            new TicketResource($ticket),
            'Ticket uploaded successfully',
            201
        );
    }

    /**
     * Display the ownership history of a ticket.
     */
    public function history(string $id): JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        Gate::authorize('view', $ticket);

        $ticket->load([
            'ownershipHistories' => function ($query) {
                $query->orderByDesc('transferred_at')->orderByDesc('id');
            },
            'ownershipHistories.previousOwner' => function ($query) {
                $query->select('id', 'name');
            },
            'ownershipHistories.newOwner' => function ($query) {
                $query->select('id', 'name');
            },
        ]);

        return ApiResponse::success(
            OwnershipHistoryResource::collection($ticket->ownershipHistories),
            'Ticket ownership history retrieved successfully'
        );
    }
}
