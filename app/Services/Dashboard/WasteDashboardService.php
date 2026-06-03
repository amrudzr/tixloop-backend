<?php

namespace App\Services\Dashboard;

use App\Models\ResaleListing;
use Illuminate\Database\Eloquent\Builder;

class WasteDashboardService
{
    /**
     * Retrieve waste dashboard metrics with optional filters.
     *
     * @param  array{event_id?: string, category?: string, start_date?: string, end_date?: string}  $filters
     * @return array{potential_impact: array<string, mixed>, listings_breakdown: array<string, mixed>, metadata: array<string, mixed>}
     */
    public function getMetrics(array $filters): array
    {
        $baseQuery = $this->buildBaseQuery($filters);

        return [
            'potential_impact' => $this->computePotentialImpact(clone $baseQuery),
            'listings_breakdown' => $this->computeListingsBreakdown(clone $baseQuery),
            'metadata' => [
                'filters' => [
                    'event_id' => $filters['event_id'] ?? null,
                    'category' => $filters['category'] ?? null,
                    'start_date' => $filters['start_date'] ?? null,
                    'end_date' => $filters['end_date'] ?? null,
                ],
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Build a base query with optional event/category/date filters.
     */
    private function buildBaseQuery(array $filters): Builder
    {
        $query = ResaleListing::query();

        if (! empty($filters['event_id'])) {
            $query->whereHas('ticket', function (Builder $q) use ($filters) {
                $q->where('event_id', $filters['event_id']);
            });
        }

        if (! empty($filters['category'])) {
            $query->whereHas('ticket.event', function (Builder $q) use ($filters) {
                $q->where('event_category', $filters['category']);
            });
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query;
    }

    /**
     * Compute potential impact metrics from verified + active listings.
     *
     * @return array<string, mixed>
     */
    private function computePotentialImpact(Builder $query): array
    {
        $rescuedQuery = (clone $query)
            ->where('verification_status', 'verified')
            ->where('listing_status', 'aktif');

        return [
            'potentially_rescued_tickets' => (clone $rescuedQuery)->count(),
            'verified_listings' => (clone $query)->where('verification_status', 'verified')->count(),
            'active_listings' => (clone $query)->where('listing_status', 'aktif')->count(),
            'listing_value_available' => (float) (clone $rescuedQuery)->sum('current_asking_price'),
        ];
    }

    /**
     * Compute overall listings breakdown by status and value.
     *
     * @return array<string, mixed>
     */
    private function computeListingsBreakdown(Builder $query): array
    {
        return [
            'total_listings' => (clone $query)->count(),
            'pending_listings' => (clone $query)->where('verification_status', 'pending')->count(),
            'rejected_listings' => (clone $query)->where('verification_status', 'rejected')->count(),
            'total_listing_value' => (float) (clone $query)->sum('current_asking_price'),
            'verified_listing_value' => (float) (clone $query)->where('verification_status', 'verified')->sum('current_asking_price'),
        ];
    }
}
