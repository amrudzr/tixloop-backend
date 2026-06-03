<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrowsePendingListingsRequest;
use App\Http\Requests\Admin\RejectListingRequest;
use App\Http\Requests\Admin\VerifyListingRequest;
use App\Http\Resources\Admin\AdminResaleListingResource;
use App\Models\ResaleListing;
use App\Services\AdminResaleListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AdminResaleListingController extends Controller
{
    public function __construct(
        private AdminResaleListingService $adminResaleListingService,
    ) {}

    /**
     * List pending listings for admin review.
     */
    public function index(BrowsePendingListingsRequest $request): JsonResponse
    {
        Gate::authorize('viewPending', ResaleListing::class);

        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);

        $listings = $this->adminResaleListingService->getPendingListings(
            search: $validated['search'] ?? null,
            perPage: $perPage
        );

        return ApiResponse::success(
            AdminResaleListingResource::collection($listings),
            'Pending listings retrieved successfully',
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
     * Show a single listing for admin review.
     */
    public function show(string $id): JsonResponse
    {
        Gate::authorize('viewPending', ResaleListing::class);

        $listing = ResaleListing::with(['ticket.event', 'seller', 'verifiedByAdmin'])
            ->findOrFail($id);

        return ApiResponse::success(
            new AdminResaleListingResource($listing),
            'Listing retrieved successfully',
            200
        );
    }

    /**
     * Verify a pending listing.
     */
    public function verify(VerifyListingRequest $request, string $id): JsonResponse
    {
        $listing = ResaleListing::findOrFail($id);
        Gate::authorize('verify', $listing);

        $listing = $this->adminResaleListingService->verifyListing(
            listing: $listing,
            admin: $request->user()
        );

        $listing->load(['ticket.event', 'seller', 'verifiedByAdmin']);

        return ApiResponse::success(
            new AdminResaleListingResource($listing),
            'Listing verified successfully',
            200
        );
    }

    /**
     * Reject a pending listing.
     */
    public function reject(RejectListingRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();
        $listing = ResaleListing::findOrFail($id);
        Gate::authorize('reject', $listing);

        $listing = $this->adminResaleListingService->rejectListing(
            listing: $listing,
            admin: $request->user(),
            reason: $validated['rejection_reason']
        );

        $listing->load(['ticket.event', 'seller', 'verifiedByAdmin']);

        return ApiResponse::success(
            new AdminResaleListingResource($listing),
            'Listing rejected successfully',
            200
        );
    }
}
