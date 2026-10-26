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

    // Optional features (barcode, EMI, membership, warranty, …) are switched on/off from
    // retail.settings.modules — their definitions and defaults live in Config/features.php.

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

    // Batch (lot) barcodes: {batch prefix}-{zero-padded batch id}, e.g. LOT-000001. Whether
    // they are auto-generated is the `auto_batch_barcode` feature (Config/features.php).
    'batch_barcode_prefix' => 'LOT',

    // Weighing-scale barcodes (EAN-13 in-store range): {prefix}{item code}{value}{check digit},
    // e.g. 21 00123 01250 C. The item code is the product's PLU code. `value_type` says what the
    // value is: "weight" in grams (product sold per kg) or "price" in the smallest currency unit
    // (poisha). Switched on/off as the `weighed_barcode` feature; `value_type` and `prefixes`
    // are overridable from retail.settings.modules.
    'weighed_barcode' => [
        'prefixes'         => ['20', '21', '22', '23', '24', '25', '26', '27', '28', '29'],
        'item_code_length' => 5,
        'value_length'     => 5,
        'value_type'       => 'weight',
    ],

    // Printable label sizes (plan §10). "custom" lets the user type mm dimensions on the label form.
    'label_sizes' => [
        '40x30' => ['width' => 40, 'height' => 30],
        '50x30' => ['width' => 50, 'height' => 30],
        '60x40' => ['width' => 60, 'height' => 40],
    ],
];