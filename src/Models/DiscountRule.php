<?php

namespace ME\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use ME\Retail\Enums\DiscountRuleType;
use ME\Retail\Enums\DiscountValueType;

class DiscountRule extends Model
{
    protected $table = 'retail_discount_rules';

    protected $fillable = [
        'name',
        'type',
        'target_id',
        'coupon_code',
        'min_quantity',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'max_uses',
        'used_count',
        'sl',
        'status',
    ];

    protected $casts = [
        'type'           => DiscountRuleType::class,
        'discount_type'  => DiscountValueType::class,
        'min_quantity'   => 'decimal:4',
        'discount_value' => 'decimal:2',
        'starts_at'      => 'date',
        'ends_at'        => 'date',
        'status'         => 'boolean',
    ];

    public function isCurrentlyActive(): bool
    {
        if (!$this->status) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->starts_at && $this->starts_at->toDateString() > $today) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->toDateString() < $today) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Resolve this rule's discount against a base amount.
     */
    public function amountFor(float $base): float
    {
        $amount = $this->discount_type === DiscountValueType::Percent
            ? $base * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return min($amount, $base);
    }
}
