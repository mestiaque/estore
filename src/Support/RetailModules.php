<?php

namespace ME\Retail\Support;

/**
 * Whether the optional Discount, Loyalty, and EMI modules are switched on for
 * this business — read from Metheme's `settings` table (master-configurable
 * via retail.settings.modules) with the `retail.php` config as the fallback
 * default. Used both to gate the sidebar (RetailServiceProvider) and by the
 * settings screen itself; core services never need to check this, since
 * discount/coupon/points/EMI are already optional per-sale inputs.
 */
class RetailModules
{
    public static function discountEnabled(): bool
    {
        return (bool) get_setting('retail_enable_discount_module', config('retail.enable_discount_module', true));
    }

    public static function loyaltyEnabled(): bool
    {
        return (bool) get_setting('retail_enable_loyalty_module', config('retail.enable_loyalty_module', true));
    }

    public static function emiEnabled(): bool
    {
        return (bool) get_setting('retail_enable_emi_module', config('retail.enable_emi_module', true));
    }

    /**
     * Drops the EMI group and the Discount/Loyalty menu items from a sidebar
     * array when the corresponding module is switched off. Guarded — the
     * `settings` table may not exist yet (e.g. mid-`migrate`), in which case
     * every module is treated as enabled and the array is returned untouched.
     *
     * @param array<int, array<string, mixed>> $groups
     * @return array<int, array<string, mixed>>
     */
    public static function filterSidebar(array $groups): array
    {
        try {
            $discountEnabled = self::discountEnabled();
            $loyaltyEnabled  = self::loyaltyEnabled();
            $emiEnabled      = self::emiEnabled();
        } catch (\Throwable) {
            return $groups;
        }

        if ($discountEnabled && $loyaltyEnabled && $emiEnabled) {
            return $groups;
        }

        $loyaltyRoutes = [
            'retail.customer-levels.index',
            'retail.loyalty.adjust.create',
            'retail.settings.loyalty.edit',
        ];

        $groups = array_filter($groups, fn (array $group) => $emiEnabled || ($group['title'] ?? null) !== 'EMI');

        foreach ($groups as &$group) {
            if (!isset($group['children'])) {
                continue;
            }

            $group['children'] = array_values(array_filter($group['children'], function (array $child) use ($discountEnabled, $loyaltyEnabled, $emiEnabled, $loyaltyRoutes) {
                $route = $child['route'] ?? null;

                if (!$discountEnabled && $route === 'retail.discount-rules.index') {
                    return false;
                }
                if (!$loyaltyEnabled && (in_array($route, $loyaltyRoutes, true) || $route === 'retail.reports.loyalty')) {
                    return false;
                }
                if (!$emiEnabled && $route === 'retail.reports.emi') {
                    return false;
                }

                return true;
            }));
        }
        unset($group);

        return array_values($groups);
    }
}
