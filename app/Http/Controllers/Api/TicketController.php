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
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            'message' => 'Daftar tiket berhasil diambil',
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
            'Data tiket berhasil diambil'
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
            'Tiket berhasil diunggah.',
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
            'Riwayat kepemilikan tiket berhasil diambil'
        );
    }

    /**
     * Download the ticket proof file.
     */
    public function downloadProof(string $id): BinaryFileResponse|JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        Gate::authorize('view', $ticket);

        if (!$ticket->ticket_proof_path) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        $disk = config('filesystems.tickets_disk', 'local');
        
        if (!Storage::disk($disk)->exists($ticket->ticket_proof_path)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file(Storage::disk($disk)->path($ticket->ticket_proof_path));
    }

    /**
     * Download the ticket physical photo file.
     */
    public function downloadPhysicalPhoto(string $id): BinaryFileResponse|JsonResponse
    {
        $ticket = Ticket::findOrFail($id);

        Gate::authorize('view', $ticket);

        $metadata = $ticket->ticket_metadata ?? [];
        $physicalPhotoPath = $metadata['physical_photo_path'] ?? null;

        if (!$physicalPhotoPath) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        $disk = config('filesystems.tickets_disk', 'local');

        if (!Storage::disk($disk)->exists($physicalPhotoPath)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file(Storage::disk($disk)->path($physicalPhotoPath));
    }
}
