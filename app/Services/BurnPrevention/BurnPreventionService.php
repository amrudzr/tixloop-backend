<?php

namespace App\Services\BurnPrevention;

use App\Models\ResaleListing;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class BurnPreventionService
{
    /** @var int TTE thresholds in hours */
    private const HIGH_RISK_HOURS = 24;

    private const MEDIUM_RISK_HOURS = 72;

    private const PENDING_HIGH_RISK_HOURS = 48;

    /**
     * Calculate risk level for a single listing based on TTE and verification status.
     *
     * @return array{risk_level: string, time_to_event_hours: float, is_at_floor: bool, recommendation: string}
     */
    public function assessListing(ResaleListing $listing): array
    {
        $eventDatetime = $listing->ticket->event->event_datetime;
        $now = now();

        if ($eventDatetime->lte($now)) {
            return [
                'risk_level' => 'expired',
                'time_to_event_hours' => 0,
                'is_at_floor' => false,
                'recommendation' => 'Event has passed. Ticket is potentially wasted.',
            ];
        }

        $tte = $now->diffInMinutes($eventDatetime) / 60;
        $isAtFloor = (float) $listing->current_asking_price <= (float) $listing->floor_price;

        $riskLevel = $this->determineRiskLevel($tte, $listing->verification_status);
        $recommendation = $this->generateRecommendation($riskLevel, $isAtFloor, (float) $listing->floor_price);

        return [
            'risk_level' => $riskLevel,
            'time_to_event_hours' => round($tte, 1),
            'is_at_floor' => $isAtFloor,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Compute dashboard metrics for burn prevention.
     *
     * @param  array{event_id?: string}  $filters
     * @return array{summary: array<string, mixed>, metadata: array<string, mixed>}
     */
    public function getMetrics(array $filters): array
    {
        $now = now();
        $highThreshold = $now->copy()->addHours(self::HIGH_RISK_HOURS);
        $mediumThreshold = $now->copy()->addHours(self::MEDIUM_RISK_HOURS);
        $pendingHighThreshold = $now->copy()->addHours(self::PENDING_HIGH_RISK_HOURS);

        $baseQuery = $this->buildBaseListingQuery($filters);

        // High risk: verified+active listings with TTE <= 24h
        $highRiskVerified = (clone $baseQuery)
            ->where('verification_status', 'verified')
            ->where('listing_status', 'aktif')
            ->whereHas('ticket.event', function (Builder $q) use ($now, $highThreshold) {
                $q->where('event_datetime', '>', $now)
                    ->where('event_datetime', '<=', $highThreshold);
            });

        // High risk: pending listings with TTE <= 48h
        $highRiskPending = (clone $baseQuery)
            ->where('verification_status', 'pending')
            ->whereHas('ticket.event', function (Builder $q) use ($now, $pendingHighThreshold) {
                $q->where('event_datetime', '>', $now)
                    ->where('event_datetime', '<=', $pendingHighThreshold);
            });

        $highRiskCount = (clone $highRiskVerified)->count() + (clone $highRiskPending)->count();
        $highRiskValue = (float) (clone $highRiskVerified)->sum('current_asking_price')
            + (float) (clone $highRiskPending)->sum('current_asking_price');

        // Medium risk: verified+active listings with 24h < TTE <= 72h
        $mediumRiskCount = (clone $baseQuery)
            ->where('verification_status', 'verified')
            ->where('listing_status', 'aktif')
            ->whereHas('ticket.event', function (Builder $q) use ($highThreshold, $mediumThreshold) {
                $q->where('event_datetime', '>', $highThreshold)
                    ->where('event_datetime', '<=', $mediumThreshold);
            })
            ->count();

        $mediumRiskValue = (float) (clone $baseQuery)
            ->where('verification_status', 'verified')
            ->where('listing_status', 'aktif')
            ->whereHas('ticket.event', function (Builder $q) use ($highThreshold, $mediumThreshold) {
                $q->where('event_datetime', '>', $highThreshold)
                    ->where('event_datetime', '<=', $mediumThreshold);
            })
            ->sum('current_asking_price');

        // Low risk: verified+active listings with TTE > 72h
        $lowRiskCount = (clone $baseQuery)
            ->where('verification_status', 'verified')
            ->where('listing_status', 'aktif')
            ->whereHas('ticket.event', function (Builder $q) use ($mediumThreshold) {
                $q->where('event_datetime', '>', $mediumThreshold);
            })
            ->count();

        // Historical wasted tickets: event passed, ticket still aktif, never used
        $wastedQuery = Ticket::query()
            ->where('status', 'aktif')
            ->whereNull('used_at')
            ->whereHas('event', function (Builder $q) use ($now) {
                $q->where('event_datetime', '<=', $now);
            });

        if (! empty($filters['event_id'])) {
            $wastedQuery->where('event_id', $filters['event_id']);
        }

        $historicalWastedTickets = $wastedQuery->count();

        return [
            'summary' => [
                'total_value_at_risk' => round($highRiskValue + $mediumRiskValue, 2),
                'high_risk_count' => $highRiskCount,
                'medium_risk_count' => $mediumRiskCount,
                'low_risk_count' => $lowRiskCount,
                'historical_wasted_tickets' => $historicalWastedTickets,
            ],
            'metadata' => [
                'filters' => [
                    'event_id' => $filters['event_id'] ?? null,
                ],
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Retrieve the authenticated user's at-risk listings with computed risk data.
     *
     * @param  array{event_id?: string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function getUserListingsAtRisk(string $userId, array $filters): array
    {
        $now = now();
        $mediumThreshold = $now->copy()->addHours(self::MEDIUM_RISK_HOURS);
        $pendingHighThreshold = $now->copy()->addHours(self::PENDING_HIGH_RISK_HOURS);

        $query = ResaleListing::with(['ticket.event'])
            ->where('seller_id', $userId)
            ->whereIn('listing_status', ['aktif', 'ditangguhkan'])
            ->whereHas('ticket.event', function (Builder $q) use ($now) {
                $q->where('event_datetime', '>', $now);
            })
            ->where(function (Builder $q) use ($mediumThreshold, $pendingHighThreshold) {
                // Verified listings within 72h risk window
                $q->where(function (Builder $sub) use ($mediumThreshold) {
                    $sub->where('verification_status', 'verified')
                        ->where('listing_status', 'aktif')
                        ->whereHas('ticket.event', function (Builder $eq) use ($mediumThreshold) {
                            $eq->where('event_datetime', '<=', $mediumThreshold);
                        });
                })
                // OR pending listings within 48h risk window
                    ->orWhere(function (Builder $sub) use ($pendingHighThreshold) {
                        $sub->where('verification_status', 'pending')
                            ->whereHas('ticket.event', function (Builder $eq) use ($pendingHighThreshold) {
                                $eq->where('event_datetime', '<=', $pendingHighThreshold);
                            });
                    });
            });

        if (! empty($filters['event_id'])) {
            $query->whereHas('ticket', function (Builder $q) use ($filters) {
                $q->where('event_id', $filters['event_id']);
            });
        }

        return $query->get()->map(fn (ResaleListing $listing) => [
            'listing' => $listing,
            'assessment' => $this->assessListing($listing),
        ])->all();
    }

    /**
     * Determine risk level from TTE hours and verification status.
     */
    private function determineRiskLevel(float $tteHours, string $verificationStatus): string
    {
        if ($verificationStatus === 'pending' && $tteHours <= self::PENDING_HIGH_RISK_HOURS) {
            return 'high';
        }

        if ($tteHours <= self::HIGH_RISK_HOURS) {
            return 'high';
        }

        if ($tteHours <= self::MEDIUM_RISK_HOURS) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Generate simple recommendation based on risk level and floor price comparison.
     */
    private function generateRecommendation(string $riskLevel, bool $isAtFloor, float $floorPrice): string
    {
        if ($riskLevel === 'high') {
            if ($isAtFloor) {
                return 'Price is at floor. Maximum rescue visibility achieved.';
            }

            return "Drop price to floor (\${$floorPrice}) to maximize chance of instant sell.";
        }

        if ($riskLevel === 'medium') {
            if ($isAtFloor) {
                return 'Price is at floor. Monitor market activity.';
            }

            return "Adjust asking price closer to floor (\${$floorPrice}) to increase visibility.";
        }

        return 'Listing active. Monitor market activity.';
    }

    /**
     * Build base query for listing aggregations with optional event_id filter.
     */
    private function buildBaseListingQuery(array $filters): Builder
    {
        $query = ResaleListing::query();

        if (! empty($filters['event_id'])) {
            $query->whereHas('ticket', function (Builder $q) use ($filters) {
                $q->where('event_id', $filters['event_id']);
            });
        }

        return $query;
    }
}
