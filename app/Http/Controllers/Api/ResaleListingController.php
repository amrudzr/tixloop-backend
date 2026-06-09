<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BrowseListingsRequest;
use App\Http\Requests\StoreListingRequest;
use App\Http\Resources\ResaleListingDetailResource;
use App\Http\Resources\ResaleListingIndexResource;
use App\Http\Resources\ResaleListingResource;
use App\Http\Resources\SellerListingResource;
use App\Models\ResaleListing;
use App\Models\Ticket;
use App\Services\ResaleListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResaleListingController extends Controller
{
    public function __construct(
        private ResaleListingService $resaleListingService,
    ) {}

    /**
     * List authenticated seller's own listings.
     */
    public function sellerListings(Request $request): JsonResponse
    {
        $listings = $this->resaleListingService->getSellerListings(
            seller: $request->user(),
            status: $request->query('status'),
            perPage: (int) ($request->query('per_page', 15))
        );

        return ApiResponse::success(
            SellerListingResource::collection($listings),
            'Seller listings retrieved successfully',
            200,
            [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ]
        );
    }

    /**
     * Browse marketplace listings.
     */
    public function index(BrowseListingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $listings = $this->resaleListingService->browseListings(
            search: $validated['search'] ?? null,
            perPage: (int) ($validated['per_page'] ?? 15)
        );

        return ApiResponse::success(
            ResaleListingIndexResource::collection($listings),
            'Marketplace listings retrieved successfully',
            200,
            [
                'current_page' => $listings->currentPage(),
                'last_page' => $listings->lastPage(),
                'per_page' => $listings->perPage(),
                'total' => $listings->total(),
            ]
        );
    }

    /**
     * Get marketplace listing details.
     */
    public function show(string $id): JsonResponse
    {
        $listing = ResaleListing::with(['ticket.event', 'seller'])
            ->where('listing_status', 'aktif')
            ->where('verification_status', 'verified')
            ->findOrFail($id);

        return ApiResponse::success(
            new ResaleListingDetailResource($listing),
            'Marketplace listing retrieved successfully',
            200
        );
    }

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
