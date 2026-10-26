<?php

/*
 * Every Retail value a business may want to change, editable on
 * retail.settings.general (RetailSettings reads them). Each entry's default is the
 * matching key in Config/config.php (dots = nesting, e.g. weighed_barcode.prefixes);
 * a saved value lives in Metheme's `settings` table as "retail_" + key with dots
 * turned into underscores (e.g. retail_weighed_barcode_prefixes).
 *
 * type: bool | int | float | string | select | csv (comma list) | multiselect | sizes (label sizes)
 * feature: only shown/used while that feature (Config/features.php) is on
 */
return [
    'groups' => [
        'inventory' => ['label' => 'Inventory', 'icon' => 'fas fa-boxes'],
        'barcode'   => ['label' => 'Barcode', 'icon' => 'fas fa-barcode'],
        'labels'    => ['label' => 'Barcode Labels', 'icon' => 'fas fa-tags'],
        'loyalty'   => ['label' => 'Loyalty Points', 'icon' => 'fas fa-star'],
        'invoice'   => ['label' => 'Invoice', 'icon' => 'fas fa-file-invoice'],
    ],

    'settings' => [
        'allow_negative_stock' => [
            'group' => 'inventory', 'type' => 'bool',
            'label' => 'Allow negative stock',
            'help'  => 'When off, a sale, adjustment or transfer that would take a warehouse below zero is refused.',
        ],
        'low_stock_threshold' => [
            'group' => 'inventory', 'type' => 'int', 'min' => 0, 'max' => 1000000,
            'label' => 'Low stock alert at',
            'help'  => 'Products at or below this quantity show as low stock on the dashboard and reports.',
        ],
        'expiry_warning_days' => [
            'group' => 'inventory', 'type' => 'int', 'min' => 1, 'max' => 3650, 'feature' => 'batch',
            'label' => 'Expiry warning (days)',
            'help'  => 'Batches expiring within this many days are flagged as expiring soon.',
        ],

        'barcode_prefix' => [
            'group' => 'barcode', 'type' => 'string', 'max' => 20, 'feature' => 'barcode',
            'label' => 'Product barcode prefix',
            'help'  => 'Generated product barcodes look like PREFIX-000001.',
        ],
        'batch_barcode_prefix' => [
            'group' => 'barcode', 'type' => 'string', 'max' => 20, 'feature' => 'auto_batch_barcode',
            'label' => 'Lot barcode prefix',
            'help'  => 'Generated lot barcodes look like PREFIX-000001.',
        ],
        'barcode_number_length' => [
            'group' => 'barcode', 'type' => 'int', 'min' => 3, 'max' => 12, 'feature' => 'barcode',
            'label' => 'Generated number length',
            'help'  => 'How many digits follow the prefix (zero padded).',
        ],
        'weighed_barcode.value_type' => [
            'group' => 'barcode', 'type' => 'select', 'feature' => 'weighed_barcode',
            'options' => ['weight' => 'Weight (grams)', 'price' => 'Price (poisha)'],
            'label' => 'Scale label value',
            'help'  => 'What the number inside a weighing-scale label stands for.',
        ],
        'weighed_barcode.prefixes' => [
            'group' => 'barcode', 'type' => 'csv', 'pattern' => '/^\d+$/', 'feature' => 'weighed_barcode',
            'label' => 'Scale label prefixes',
            'help'  => 'Comma separated, e.g. 20,21,22. Only labels starting with one of these are read as scale labels.',
        ],
        'weighed_barcode.item_code_length' => [
            'group' => 'barcode', 'type' => 'int', 'min' => 1, 'max' => 8, 'feature' => 'weighed_barcode',
            'label' => 'Scale PLU code digits',
        ],
        'weighed_barcode.value_length' => [
            'group' => 'barcode', 'type' => 'int', 'min' => 1, 'max' => 8, 'feature' => 'weighed_barcode',
            'label' => 'Scale value digits',
        ],

        'label_sizes' => [
            'group' => 'labels', 'type' => 'sizes', 'feature' => 'barcode',
            'label' => 'Label sizes (mm)',
            'help'  => 'Sizes offered when printing barcode labels. A custom size can still be typed at print time.',
        ],

        'loyalty_point_value' => [
            'group' => 'loyalty', 'type' => 'float', 'min' => 0.0001, 'max' => 1000000, 'step' => '0.0001', 'feature' => 'loyalty',
            'label' => 'Value of one point',
            'help'  => 'Currency amount one point is worth when redeemed.',
        ],
        'loyalty_max_redeem_percent' => [
            'group' => 'loyalty', 'type' => 'float', 'min' => 0, 'max' => 100, 'step' => '0.01', 'feature' => 'loyalty',
            'label' => 'Max share of a bill paid by points (%)',
        ],

        'invoice_types' => [
            'group' => 'invoice', 'type' => 'multiselect',
            'options' => ['a4' => 'A4', 'thermal80' => 'Thermal 80mm', 'thermal58' => 'Thermal 58mm'],
            'label' => 'Invoice formats offered',
        ],
        'default_invoice_type' => [
            'group' => 'invoice', 'type' => 'select',
            'options' => ['a4' => 'A4', 'thermal80' => 'Thermal 80mm', 'thermal58' => 'Thermal 58mm'],
            'label' => 'Default invoice format',
        ],
    ],
];
