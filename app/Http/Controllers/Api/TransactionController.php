<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\ResaleListing;
use App\Models\Transaction;
use App\Services\CheckoutService;
use App\Services\EscrowService;
use App\Services\OwnershipTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
        private OwnershipTransferService $ownershipTransferService,
        private EscrowService $escrowService,
    ) {}

    /**
     * Get transaction history for the logged-in user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $transactions = Transaction::with([
            'buyer',
            'seller',
            'ticket.event',
        ])
            ->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $paginated = TransactionResource::collection($transactions)->response()->getData(true);

        return response()->json([
            'success' => true,
            'message' => 'Transaction history retrieved successfully',
            'data' => $paginated['data'],
            'links' => $paginated['links'],
            'meta' => $paginated['meta'],
        ]);
    }

    /**
     * Initiate a checkout for a resale listing.
     */
    public function checkout(Request $request, string $id): JsonResponse
    {
        $listing = ResaleListing::findOrFail($id);

        $transaction = $this->checkoutService->initiateCheckout(
            buyer: $request->user(),
            listing: $listing,
        );

        $transaction->load(['buyer', 'seller', 'ticket']);

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Checkout initiated successfully',
            201
        );
    }

    /**
     * Simulate payment and hold escrow (Phase 1).
     */
    public function simulatePayment(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::with(['ticket', 'resaleListing'])->findOrFail($id);

        Gate::authorize('simulatePayment', $transaction);

        $transaction = $this->ownershipTransferService->simulatePayment($transaction);

        $transaction->load(['buyer', 'seller', 'ticket']);

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Payment simulation successful. Escrow held.',
            200
        );
    }

    /**
     * Release escrow and transfer ownership (Phase 2).
     */
    public function releaseEscrow(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::with(['ticket', 'resaleListing'])->findOrFail($id);

        Gate::authorize('releaseEscrow', $transaction);

        $transaction = $this->escrowService->releaseEscrow($transaction, $request->user());

        $transaction->load(['buyer', 'seller', 'ticket']);

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Escrow released. Ownership transferred.',
            200
        );
    }

    /**
     * Get transaction details.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $transaction = Transaction::with(['buyer', 'seller', 'ticket', 'resaleListing'])->findOrFail($id);

        Gate::authorize('view', $transaction);

        return ApiResponse::success(
            new TransactionResource($transaction),
            'Transaction retrieved successfully',
            200
        );
    }
}
