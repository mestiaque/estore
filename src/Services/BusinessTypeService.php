<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ME\Models\Setting;
use ME\Retail\Models\Unit;
use ME\Retail\Support\RetailModules;

/**
 * Applies a business-type preset: switches every feature on except the
 * preset's `disabled` list, creates the preset's common units if missing, and
 * supplies the default tracking for new products. It only ever sets defaults —
 * existing products, stock and documents are never touched, and every setting
 * can still be changed individually afterwards.
 *
 * Presets start as Config/business_types.php; once edited on the Business Types
 * screen the whole edited list is stored in the `retail_business_types` setting
 * and used instead, until it is reset.
 */
class BusinessTypeService
{
    private const STORAGE_KEY = 'retail_business_types';

    /**
     * @return array<string, array{label: string, description: string, track_type: string, has_expiry: bool, disabled: string[], units: string[]}>
     */
    public function types(): array
    {
        $stored = get_setting(self::STORAGE_KEY);
        $stored = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($stored) && $stored !== [] ? $stored : config('retail_business_types.types', []);
    }

    public function isCustomised(): bool
    {
        return !empty(get_setting(self::STORAGE_KEY));
    }

    /**
     * Adds or replaces one preset.
     *
     * @param array{label: string, description?: ?string, track_type: string, has_expiry?: bool, disabled?: string[], units?: string[]} $preset
     */
    public function save(string $key, array $preset): void
    {
        $types = $this->types();
        $types[$key] = [
            'label'       => $preset['label'],
            'description' => (string) ($preset['description'] ?? ''),
            'track_type'  => $preset['track_type'],
            'has_expiry'  => $preset['track_type'] === 'batch' && !empty($preset['has_expiry']),
            'disabled'    => array_values(array_intersect($preset['disabled'] ?? [], array_keys(RetailModules::definitions()))),
            'units'       => array_values(array_intersect($preset['units'] ?? [], array_keys($this->unitCatalog()))),
        ];

        Setting::set(self::STORAGE_KEY, $types);
    }

    public function delete(string $key): void
    {
        $types = $this->types();
        unset($types[$key]);

        Setting::set(self::STORAGE_KEY, $types);
    }

    /**
     * Back to the presets shipped in Config/business_types.php.
     */
    public function reset(): void
    {
        Setting::set(self::STORAGE_KEY, null);
    }

    /**
     * A unique key for a new preset, derived from its label.
     */
    public function keyFor(string $label): string
    {
        $base = Str::slug($label, '_') ?: 'custom';
        $key  = $base;
        $n    = 2;

        while (array_key_exists($key, $this->types())) {
            $key = $base . '_' . $n++;
        }

        return $key;
    }

    /**
     * @return array<string, array{name: string, short_name: string, base?: string, factor?: float}>
     */
    public function unitCatalog(): array
    {
        return config('retail_business_types.units', []);
    }

    public function current(): ?string
    {
        $type = get_setting('retail_business_type');

        return $type && array_key_exists($type, $this->types()) ? $type : null;
    }

    /**
     * Defaults pre-filled on the new-product form.
     *
     * @return array{track_type: string, has_expiry: bool}
     */
    public function productDefaults(): array
    {
        $preset    = $this->types()[$this->current()] ?? null;
        $trackType = $preset['track_type'] ?? 'normal';

        // A tracking type whose feature has since been switched off falls back to normal.
        if ($trackType !== 'normal' && !RetailModules::enabled($trackType)) {
            $trackType = 'normal';
        }

        return [
            'track_type' => $trackType,
            'has_expiry' => $trackType === 'batch' && (bool) ($preset['has_expiry'] ?? false),
        ];
    }

    /**
     * @return int How many units were created.
     */
    public function apply(string $type): int
    {
        $preset = $this->types()[$type] ?? throw new InvalidArgumentException("Unknown business type \"{$type}\".");

        return DB::transaction(function () use ($type, $preset) {
            Setting::set('retail_business_type', $type);
            foreach (array_keys(RetailModules::definitions()) as $feature) {
                RetailModules::set($feature, !in_array($feature, $preset['disabled'] ?? [], true));
            }

            return $this->ensureUnits($preset['units']);
        });
    }

    /**
     * Creates each catalog unit (and the base unit it derives from) unless a unit
     * with the same name or short name already exists.
     *
     * @param string[] $keys
     */
    private function ensureUnits(array $keys): int
    {
        $catalog = $this->unitCatalog();
        $created = 0;
        $resolved = [];

        $resolve = function (string $key) use (&$resolve, &$resolved, &$created, $catalog): Unit {
            if (isset($resolved[$key])) {
                return $resolved[$key];
            }

            $definition = $catalog[$key] ?? throw new InvalidArgumentException("Unknown unit \"{$key}\".");

            $unit = Unit::query()
                ->whereRaw('lower(name) = ?', [strtolower($definition['name'])])
                ->orWhereRaw('lower(short_name) = ?', [strtolower($definition['short_name'])])
                ->first();

            if (!$unit) {
                $base = isset($definition['base']) ? $resolve($definition['base']) : null;

                $unit = Unit::create([
                    'base_unit_id'      => $base?->id,
                    'name'              => $definition['name'],
                    'short_name'        => $definition['short_name'],
                    'conversion_factor' => $definition['factor'] ?? 1,
                    'status'            => true,
                ]);
                $created++;
            }

            return $resolved[$key] = $unit;
        };

        foreach ($keys as $key) {
            $resolve($key);
        }

        return $created;
    }
}
