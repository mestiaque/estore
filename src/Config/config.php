<?php
return [
    'name' => 'retail',

    // If false, StockService::move() throws InsufficientStockException instead
    // of letting a warehouse's stock go below zero.
    'allow_negative_stock' => false,

    // Loyalty: how many currency units one point is worth on redemption, and the
    // maximum share of a single invoice's grand total that points may cover.
    'loyalty_point_value' => 0.1,
    'loyalty_max_redeem_percent' => 50,

    // Optional modules — a business that doesn't run EMI, discounts, or a loyalty
    // points program can switch the corresponding menu/section off entirely
    // (see settings/modules.blade.php). Defaults on so existing behavior is unchanged.
    'enable_discount_module' => true,
    'enable_loyalty_module'  => true,
    'enable_emi_module'      => true,

    // Invoice printing (plan §40-42): supported render types and the one used
    // when a sale's print button isn't given an explicit type.
    'invoice_types' => ['a4', 'thermal80', 'thermal58'],
    'default_invoice_type' => 'a4',

    // Reporting/dashboard thresholds (plan §16, §50, §62).
    'low_stock_threshold' => 10,
    'expiry_warning_days' => 30,

    // Generated barcode format (plan §9): {prefix}{zero-padded product id}, e.g. STORE-000001.
    // Replaceable — BarcodeService is the only place this format is read.
    'barcode_prefix' => 'STORE',
    'barcode_number_length' => 6,

    // Printable label sizes (plan §10). "custom" lets the user type mm dimensions on the label form.
    'label_sizes' => [
        '40x30' => ['width' => 40, 'height' => 30],
        '50x30' => ['width' => 50, 'height' => 30],
        '60x40' => ['width' => 60, 'height' => 40],
    ],
];