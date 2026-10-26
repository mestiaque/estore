<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\LoyaltyTransactionType;
use ME\Retail\Models\Customer;
use ME\Retail\Models\CustomerLevel;
use ME\Retail\Models\LoyaltyTransaction;

/**
 * The single authorized way loyalty points are ever mutated. Point balance is
 * never a directly-editable number — it is always SUM(loyalty_transactions.points).
 * Also owns customer-level (re)assignment, since level is derived from
 * lifetime earned points.
 */
class LoyaltyService
{
    /**
     * Combined earn-rate multiplier from the customer's membership and level (both optional, default 1x each).
     */
    public function multiplierFor(Customer $customer): float
    {
        $multiplier = 1.0;

        if ($customer->hasActiveMembership() && $customer->membership) {
            $multiplier *= (float) $customer->membership->point_multiplier;
        }

        if ($customer->level) {
            $multiplier *= (float) $customer->level->point_multiplier;
        }

        return $multiplier ?: 1.0;
    }

    public function earn(Customer $customer, float $points, ?string $referenceType, int|string|null $referenceId, ?int $userId, ?string $notes): ?LoyaltyTransaction
    {
        if ($points <= 0) {
            return null;
        }

        $transaction = $this->record($customer, LoyaltyTransactionType::Earn, $points, $referenceType, $referenceId, $userId, $notes);
        $this->recalculateLevel($customer);

        return $transaction;
    }

    /**
     * @return array{transaction: LoyaltyTransaction, discount: float}
     */
    public function redeem(Customer $customer, float $points, ?string $referenceType, int|string|null $referenceId, ?int $userId, ?string $notes): array
    {
        if ($points <= 0) {
            throw new InvalidArgumentException('Points to redeem must be greater than zero.');
        }

        $balance = (float) $customer->points_balance;

        if ($points > $balance + 0.0001) {
            throw new InvalidArgumentException("Customer only has {$balance} points available.");
        }

        $transaction = $this->record($customer, LoyaltyTransactionType::Redeem, -$points, $referenceType, $referenceId, $userId, $notes);

        return [
            'transaction' => $transaction,
            'discount'    => $points * (float) get_setting('retail_loyalty_point_value', config('retail.loyalty_point_value', 0.1)),
        ];
    }

    /**
     * Manual correction (plan §83: reason and user are mandatory, always audited).
     * $points is signed — positive grants points, negative removes them.
     */
    public function adjust(Customer $customer, float $points, string $reason, int $userId): LoyaltyTransaction
    {
        if ($points == 0.0) {
            throw new InvalidArgumentException('Adjustment points cannot be zero.');
        }

        $transaction = $this->record($customer, LoyaltyTransactionType::Adjustment, $points, null, null, $userId, $reason);
        $this->recalculateLevel($customer);

        return $transaction;
    }

    public function reverse(Customer $customer, float $points, ?string $referenceType, int|string|null $referenceId, ?int $userId, ?string $notes): ?LoyaltyTransaction
    {
        if ($points <= 0) {
            return null;
        }

        $transaction = $this->record($customer, LoyaltyTransactionType::Reversal, -$points, $referenceType, $referenceId, $userId, $notes);
        $this->recalculateLevel($customer);

        return $transaction;
    }

    private function record(
        Customer $customer,
        LoyaltyTransactionType $type,
        float $points,
        ?string $referenceType,
        int|string|null $referenceId,
        ?int $userId,
        ?string $notes
    ): LoyaltyTransaction {
        return DB::transaction(function () use ($customer, $type, $points, $referenceType, $referenceId, $userId, $notes) {
            $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();

            $balanceAfter = (float) $locked->loyaltyTransactions()->sum('points') + $points;

            return LoyaltyTransaction::create([
                'customer_id'    => $customer->id,
                'type'           => $type,
                'points'         => $points,
                'balance_after'  => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'user_id'        => $userId,
                'notes'          => $notes,
            ]);
        });
    }

    public function recalculateLevel(Customer $customer): void
    {
        $lifetimeEarned = (float) $customer->loyaltyTransactions()
            ->where('type', LoyaltyTransactionType::Earn)
            ->sum('points');

        $level = CustomerLevel::query()
            ->where('status', true)
            ->where('min_points', '<=', $lifetimeEarned)
            ->orderByDesc('min_points')
            ->first();

        if ($customer->customer_level_id !== $level?->id) {
            $customer->update(['customer_level_id' => $level?->id]);
        }
    }
}
