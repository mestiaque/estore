<?php

namespace ME\Retail\Tests\Feature;

use ME\Retail\Support\RetailSettings;

/**
 * Builds a full Retail Settings form submission from the current values, so a
 * test only has to override the fields it is about.
 */
trait SubmitsRetailSettings
{
    protected function currentSettingsPayload(): array
    {
        return collect(RetailSettings::visible())->mapWithKeys(function (array $definition, string $key) {
            $value = RetailSettings::get($key);

            return [RetailSettings::fieldName($key) => match ($definition['type']) {
                'bool'  => $value ? '1' : '0',
                'csv'   => implode(',', (array) $value),
                'sizes' => array_values((array) $value),
                default => $value,
            }];
        })->all();
    }
}
