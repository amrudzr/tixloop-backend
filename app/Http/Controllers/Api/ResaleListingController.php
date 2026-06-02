<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Resources\ResaleListingResource;
use App\Models\Ticket;
use App\Services\ResaleListingService;
use Illuminate\Http\JsonResponse;

class ResaleListingController extends Controller
{
    public function __construct(
        private ResaleListingService $resaleListingService,
    ) {}

    /**
     * Create a marketplace listing from an owned ticket.
     */
    public function store(StoreListingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $ticket = Ticket::findOrFail($validated['ticket_id']);

        $listing = $this->resaleListingService->createListing(
            user: $request->user(),
            ticket: $ticket,
            currentAskingPrice: (float) $validated['current_asking_price'],
        );

        $listing->load(['ticket.event', 'seller']);

        return ApiResponse::success(
            new ResaleListingResource($listing),
            'Marketplace listing created successfully',
            201
        );
    }
}
