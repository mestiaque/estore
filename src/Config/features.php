<?php

/*
 * Every optional part of the Retail system, switchable on retail.settings.modules
 * (and pre-set by a business-type preset). For each feature:
 *   setting  — key in Metheme's `settings` table holding the on/off value
 *   default  — value used until the business changes it
 *   requires — features that must also be on (e.g. warranty needs serial tracking)
 *   routes   — named routes (menu items, screens) that belong to the feature; they
 *              disappear from the sidebar and are blocked while the feature is off
 *
 * Turning a feature off only hides/blocks it — nothing already saved is deleted, and
 * existing documents (old EMI contracts, batch products, …) keep displaying normally.
 */
return [
    'groups' => [
        'scanning'  => 'Barcode & Scanning',
        'inventory' => 'Inventory & Tracking',
        'sales'     => 'Sales & POS',
        'customers' => 'Customers & Loyalty',
        'finance'   => 'Cash, Expense & EMI',
    ],

    'features' => [
        'barcode' => [
            'group'       => 'scanning',
            'label'       => 'Barcode',
            'description' => 'Product barcodes, barcode labels, and the scan box on the POS and Purchase screens.',
            'setting'     => 'retail_enable_barcode',
            'default'     => true,
            'routes'      => ['retail.barcode-labels.form', 'retail.barcode-labels.print', 'retail.barcode.lookup', 'retail.products.barcode.generate', 'retail.products.barcode.bulk-generate'],
        ],
        'auto_batch_barcode' => [
            'group'       => 'scanning',
            'label'       => 'Auto-generate lot barcodes',
            'description' => 'A new batch received without the supplier\'s lot barcode gets one generated, so every lot can be labelled and scanned.',
            'setting'     => 'retail_auto_generate_batch_barcode',
            'default'     => true,
            'requires'    => ['barcode', 'batch'],
        ],
        'weighed_barcode' => [
            'group'       => 'scanning',
            'label'       => 'Weighing-scale barcodes',
            'description' => 'Scan labels printed by a weighing scale for loose goods (Scale PLU code + weight or price).',
            'setting'     => 'retail_weighed_barcode_enabled',
            'default'     => false,
            'requires'    => ['barcode'],
        ],

        'batch' => [
            'group'       => 'inventory',
            'label'       => 'Batch & expiry tracking',
            'description' => 'Receive and sell products by lot with expiry dates (earliest expiry sold first).',
            'setting'     => 'retail_enable_batch',
            'default'     => true,
        ],
        'serial' => [
            'group'       => 'inventory',
            'label'       => 'Serial / IMEI tracking',
            'description' => 'Track every single unit by its serial number or IMEI.',
            'setting'     => 'retail_enable_serial',
            'default'     => true,
        ],
        'warranty' => [
            'group'       => 'inventory',
            'label'       => 'Warranty',
            'description' => 'Warranty months on serial-tracked units, shown on the sale and the invoice.',
            'setting'     => 'retail_enable_warranty',
            'default'     => true,
            'requires'    => ['serial'],
        ],
        'variants' => [
            'group'       => 'inventory',
            'label'       => 'Product variants',
            'description' => 'Attributes such as size and colour, generating one product per combination.',
            'setting'     => 'retail_enable_variants',
            'default'     => true,
            'routes'      => ['retail.attributes.*', 'retail.attribute-values.*', 'retail.products.variants.*'],
        ],
        'stock_transfer' => [
            'group'       => 'inventory',
            'label'       => 'Stock transfer',
            'description' => 'Move stock between warehouses.',
            'setting'     => 'retail_enable_stock_transfer',
            'default'     => true,
            'routes'      => ['retail.stock-transfers.*'],
        ],
        'purchase_return' => [
            'group'       => 'inventory',
            'label'       => 'Purchase return',
            'description' => 'Return received goods to the supplier.',
            'setting'     => 'retail_enable_purchase_return',
            'default'     => true,
            'routes'      => ['retail.purchases.returns.*'],
        ],

        'tax' => [
            'group'       => 'sales',
            'label'       => 'Tax / VAT',
            'description' => 'Tax fields on sale and purchase lines and invoices.',
            'setting'     => 'retail_enable_tax',
            'default'     => true,
        ],
        'discount' => [
            'group'       => 'sales',
            'label'       => 'Discount rules & coupons',
            'description' => 'Automatic product, category, brand and quantity discounts, plus coupon codes.',
            'setting'     => 'retail_enable_discount_module',
            'default'     => true,
            'routes'      => ['retail.discount-rules.*'],
        ],
        'hold_sale' => [
            'group'       => 'sales',
            'label'       => 'Hold sale',
            'description' => 'Park an unfinished sale on the POS and resume it later.',
            'setting'     => 'retail_enable_hold_sale',
            'default'     => true,
            'routes'      => ['retail.sales.hold', 'retail.held-sales.*'],
        ],
        'sale_return' => [
            'group'       => 'sales',
            'label'       => 'Sale return',
            'description' => 'Take back sold goods and refund or credit the customer.',
            'setting'     => 'retail_enable_sale_return',
            'default'     => true,
            'routes'      => ['retail.sales.returns.*'],
        ],

        'loyalty' => [
            'group'       => 'customers',
            'label'       => 'Loyalty points & customer levels',
            'description' => 'Customers earn and redeem points; levels give extra discounts and point rates.',
            'setting'     => 'retail_enable_loyalty_module',
            'default'     => true,
            'routes'      => ['retail.customer-levels.*', 'retail.loyalty.*', 'retail.settings.loyalty.*', 'retail.reports.loyalty'],
        ],
        'membership' => [
            'group'       => 'customers',
            'label'       => 'Membership',
            'description' => 'Paid memberships with a discount and point multiplier for a set duration.',
            'setting'     => 'retail_enable_membership',
            'default'     => true,
            'routes'      => ['retail.memberships.*'],
        ],

        'cash_register' => [
            'group'       => 'finance',
            'label'       => 'Cash register',
            'description' => 'Open and close cash drawers per shift and reconcile cash.',
            'setting'     => 'retail_enable_cash_register',
            'default'     => true,
            'routes'      => ['retail.cash-registers.*', 'retail.cash-register-sessions.*', 'retail.reports.cash-register'],
        ],
        'expense' => [
            'group'       => 'finance',
            'label'       => 'Expense',
            'description' => 'Record shop expenses such as rent, bills and salaries.',
            'setting'     => 'retail_enable_expense',
            'default'     => true,
            'routes'      => ['retail.expense-categories.*', 'retail.expenses.*'],
        ],
        'emi' => [
            'group'       => 'finance',
            'label'       => 'EMI',
            'description' => 'Sell on instalments through EMI banks, plans and contracts.',
            'setting'     => 'retail_enable_emi_module',
            'default'     => true,
            'routes'      => ['retail.emi-banks.*', 'retail.emi-plans.*', 'retail.emi-contracts.*', 'retail.emi-installments.*', 'retail.reports.emi'],
        ],
    ],
];
