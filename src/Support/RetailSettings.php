<?php

namespace ME\Retail\Support;

use ME\Models\Setting;

/**
 * Business-editable Retail values (Config/settings.php). A value saved from
 * retail.settings.general wins; otherwise the default from Config/config.php
 * is used, so a fresh install behaves exactly as configured in code.
 */
class RetailSettings
{
    /**
     * @return array<string, array{group: string, type: string, label: string, help?: string, feature?: string, options?: array<string, string>, min?: int|float, max?: int|float, step?: string, pattern?: string}>
     */
    public static function definitions(): array
    {
        return config('retail_settings.settings', []);
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public static function groups(): array
    {
        return config('retail_settings.groups', []);
    }

    /**
     * The `settings` table key a value is stored under, e.g. weighed_barcode.prefixes -> retail_weighed_barcode_prefixes.
     */
    public static function storageKey(string $key): string
    {
        return 'retail_' . str_replace('.', '_', $key);
    }

    /**
     * Form field name for a key — dots would read as nesting in validation.
     */
    public static function fieldName(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    public static function get(string $key): mixed
    {
        $type    = self::definitions()[$key]['type'] ?? 'string';
        $default = config('retail.' . $key);
        $raw     = get_setting(self::storageKey($key));

        if ($raw === null || ($raw === '' && $type !== 'bool' && $type !== 'string')) {
            return $default;
        }

        return match ($type) {
            'bool'        => (bool) $raw,
            'int'         => (int) $raw,
            'float'       => (float) $raw,
            'csv'         => is_array($raw) ? $raw : array_values(array_filter(array_map('trim', explode(',', (string) $raw)), fn ($v) => $v !== '')),
            'multiselect',
            'sizes'       => is_array($raw) ? $raw : (json_decode((string) $raw, true) ?? $default),
            default       => (string) $raw,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        $type = self::definitions()[$key]['type'] ?? 'string';

        Setting::set(self::storageKey($key), match ($type) {
            'bool'  => $value ? '1' : '0',
            'csv'   => implode(',', (array) $value),
            'multiselect',
            'sizes' => (array) $value,
            default => $value === null ? null : (string) $value,
        });
    }

    /**
     * Settings shown on the form: those with no feature, or whose feature is on.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function visible(): array
    {
        return array_filter(
            self::definitions(),
            fn (array $definition) => empty($definition['feature']) || RetailModules::enabled($definition['feature'])
        );
    }

    /**
     * Validation rules for the given settings (default: the visible ones), keyed by form field.
     *
     * @param array<string, array<string, mixed>>|null $definitions
     * @return array<string, mixed>
     */
    public static function rules(?array $definitions = null): array
    {
        $rules = [];

        foreach ($definitions ?? self::visible() as $key => $definition) {
            $field = 'settings.' . self::fieldName($key);

            switch ($definition['type']) {
                case 'bool':
                    $rules[$field] = 'nullable|boolean';
                    break;
                case 'int':
                    $rules[$field] = ['required', 'integer', 'min:' . ($definition['min'] ?? 0), 'max:' . ($definition['max'] ?? PHP_INT_MAX)];
                    break;
                case 'float':
                    $rules[$field] = ['required', 'numeric', 'min:' . ($definition['min'] ?? 0), 'max:' . ($definition['max'] ?? PHP_INT_MAX)];
                    break;
                case 'select':
                    $rules[$field] = ['required', 'in:' . implode(',', array_keys($definition['options']))];
                    break;
                case 'multiselect':
                    $rules[$field] = 'required|array|min:1';
                    $rules[$field . '.*'] = ['in:' . implode(',', array_keys($definition['options']))];
                    break;
                case 'csv':
                    $rules[$field] = ['required', 'string', 'max:255', 'regex:/^\s*\d+(\s*,\s*\d+)*\s*$/'];
                    break;
                case 'sizes':
                    $rules[$field] = 'required|array|min:1';
                    $rules[$field . '.*.width'] = 'required|numeric|min:10|max:300';
                    $rules[$field . '.*.height'] = 'required|numeric|min:10|max:300';
                    break;
                default:
                    $rules[$field] = ['required', 'string', 'max:' . ($definition['max'] ?? 255), 'regex:/^[A-Za-z0-9_-]+$/'];
            }
        }

        return $rules;
    }

    /**
     * Turns the submitted form value of one setting into the value to store.
     */
    public static function fromInput(string $key, mixed $input): mixed
    {
        $type = self::definitions()[$key]['type'] ?? 'string';

        return match ($type) {
            'bool'        => (bool) $input,
            'csv'         => array_values(array_filter(array_map('trim', explode(',', (string) $input)), fn ($v) => $v !== '')),
            'multiselect' => array_values((array) $input),
            // Rows become {"40x30": {"width": 40, "height": 30}}, named by their dimensions.
            'sizes'       => collect((array) $input)
                ->mapWithKeys(fn ($row) => [
                    self::trimNumber($row['width']) . 'x' . self::trimNumber($row['height'])
                        => ['width' => (float) $row['width'], 'height' => (float) $row['height']],
                ])
                ->all(),
            default       => $input,
        };
    }

    /**
     * 40.00 -> "40", 40.50 -> "40.5".
     */
    private static function trimNumber(mixed $number): string
    {
        return rtrim(rtrim(number_format((float) $number, 2, '.', ''), '0'), '.');
    }
}
