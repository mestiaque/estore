<?php

namespace ME\Retail\Support;

use Illuminate\Support\Str;
use ME\Models\Setting;

/**
 * The on/off switchboard for every optional Retail feature (Config/features.php).
 * Values live in Metheme's `settings` table, master-configurable from
 * retail.settings.modules, with each feature's `default` as the fallback.
 *
 * A feature counts as enabled only when its own switch is on AND every feature
 * it `requires` is enabled too. Used to gate the sidebar (RetailServiceProvider),
 * routes (EnsureRetailFeature middleware), views (@feature Blade directive) and
 * the services whose behaviour depends on a feature (discounts, points, membership).
 */
class RetailModules
{
    /**
     * @return array<string, array{group: string, label: string, description: string, setting: string, default: bool, requires?: string[], routes?: string[]}>
     */
    public static function definitions(): array
    {
        return config('retail_features.features', []);
    }

    /**
     * @return array<string, string>
     */
    public static function groups(): array
    {
        return config('retail_features.groups', []);
    }

    public static function enabled(string $feature, array $visited = []): bool
    {
        $definition = self::definitions()[$feature] ?? null;
        if (!$definition || in_array($feature, $visited, true)) {
            return false;
        }

        if (!self::switchedOn($feature)) {
            return false;
        }

        foreach ($definition['requires'] ?? [] as $required) {
            if (!self::enabled($required, [...$visited, $feature])) {
                return false;
            }
        }

        return true;
    }

    /**
     * The feature's own switch, ignoring its requirements.
     */
    public static function switchedOn(string $feature): bool
    {
        $definition = self::definitions()[$feature] ?? null;
        if (!$definition) {
            return false;
        }

        return (bool) get_setting($definition['setting'], $definition['default'] ?? true);
    }

    public static function set(string $feature, bool $on): void
    {
        $definition = self::definitions()[$feature] ?? null;
        if ($definition) {
            Setting::set($definition['setting'], $on);
        }
    }

    /**
     * The feature a named route belongs to, if any (patterns like "retail.emi-banks.*").
     */
    public static function featureForRoute(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        foreach (self::definitions() as $feature => $definition) {
            foreach ($definition['routes'] ?? [] as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return $feature;
                }
            }
        }

        return null;
    }

    public static function routeEnabled(?string $routeName): bool
    {
        $feature = self::featureForRoute($routeName);

        return $feature === null || self::enabled($feature);
    }

    public static function discountEnabled(): bool
    {
        return self::enabled('discount');
    }

    public static function loyaltyEnabled(): bool
    {
        return self::enabled('loyalty');
    }

    public static function emiEnabled(): bool
    {
        return self::enabled('emi');
    }

    /**
     * Drops every sidebar item whose route belongs to a switched-off feature, and
     * any group left with no items. Guarded — the `settings` table may not exist
     * yet (e.g. mid-`migrate`), in which case the array is returned untouched.
     *
     * @param array<int, array<string, mixed>> $groups
     * @return array<int, array<string, mixed>>
     */
    public static function filterSidebar(array $groups): array
    {
        try {
            $groups = array_map(function (array $group) {
                if (isset($group['children'])) {
                    $group['children'] = array_values(array_filter(
                        $group['children'],
                        fn (array $child) => self::routeEnabled($child['route'] ?? null)
                    ));
                }

                return $group;
            }, $groups);
        } catch (\Throwable) {
            return $groups;
        }

        return array_values(array_filter(
            $groups,
            fn (array $group) => !isset($group['children']) || $group['children'] !== []
        ));
    }
}
