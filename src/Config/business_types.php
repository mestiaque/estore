<?php

/*
 * Business-type presets (plan §1: one package for every kind of shop). Picking a
 * type on retail.settings.business-type only sets defaults — every feature in
 * Config/features.php is switched on except the preset's `disabled` list, new
 * products get the preset's tracking, and its common units are created. Every
 * setting stays individually changeable afterwards, and nothing already saved
 * (products, stock, invoices) is modified. Add or edit presets freely here.
 *
 * Units: `factor` is how many base units one of this unit equals (quantity_base =
 * quantity × factor), so 1 Gram = 0.001 Kilogram and 1 Dozen = 12 Pieces.
 */
return [
    'units' => [
        'piece' => ['name' => 'Piece', 'short_name' => 'pc'],
        'dozen' => ['name' => 'Dozen', 'short_name' => 'dz', 'base' => 'piece', 'factor' => 12],
        'kg'    => ['name' => 'Kilogram', 'short_name' => 'kg'],
        'gram'  => ['name' => 'Gram', 'short_name' => 'g', 'base' => 'kg', 'factor' => 0.001],
        'litre' => ['name' => 'Litre', 'short_name' => 'L'],
        'ml'    => ['name' => 'Millilitre', 'short_name' => 'ml', 'base' => 'litre', 'factor' => 0.001],
        'metre' => ['name' => 'Metre', 'short_name' => 'm'],
        'foot'  => ['name' => 'Foot', 'short_name' => 'ft', 'base' => 'metre', 'factor' => 0.3048],
        'set'   => ['name' => 'Set', 'short_name' => 'set'],
        'pair'  => ['name' => 'Pair', 'short_name' => 'pair'],
    ],

    'types' => [
        'grocery' => [
            'label'              => 'Grocery / Super Shop',
            'description'        => 'Packaged and loose goods sold by piece, weight or volume. Dated lots (dairy, bakery) can be batch-tracked per product.',
            'track_type'         => 'normal',
            'has_expiry'         => false,
            'disabled'           => ['serial', 'warranty', 'emi', 'variants'],
            'units'              => ['piece', 'dozen', 'kg', 'gram', 'litre', 'ml'],
        ],
        'pharmacy' => [
            'label'              => 'Pharmacy / Medical Shop',
            'description'        => 'Every medicine is received and sold by batch with an expiry date (earliest expiry sold first).',
            'track_type'         => 'batch',
            'has_expiry'         => true,
            'disabled'           => ['serial', 'warranty', 'emi', 'variants', 'weighed_barcode'],
            'units'              => ['piece', 'litre', 'ml'],
        ],
        'electronics' => [
            'label'              => 'Electronics / Computer Shop',
            'description'        => 'Each unit has its own serial number and warranty; big-ticket items can be sold on EMI.',
            'track_type'         => 'serial',
            'has_expiry'         => false,
            'disabled'           => ['batch', 'weighed_barcode'],
            'units'              => ['piece', 'set', 'metre'],
        ],
        'mobile' => [
            'label'              => 'Mobile Shop',
            'description'        => 'Handsets tracked by IMEI with warranty and EMI; accessories can be switched to normal tracking per product.',
            'track_type'         => 'serial',
            'has_expiry'         => false,
            'disabled'           => ['batch', 'weighed_barcode'],
            'units'              => ['piece', 'set'],
        ],
        'furniture' => [
            'label'              => 'Furniture Shop',
            'description'        => 'Items and sets sold by piece, often on EMI.',
            'track_type'         => 'normal',
            'has_expiry'         => false,
            'disabled'           => ['batch', 'serial', 'warranty', 'loyalty', 'weighed_barcode'],
            'units'              => ['piece', 'set'],
        ],
        'clothing' => [
            'label'              => 'Clothing / Fashion Shop',
            'description'        => 'Size and colour variants of each design; fabric can be sold by length.',
            'track_type'         => 'normal',
            'has_expiry'         => false,
            'disabled'           => ['batch', 'serial', 'warranty', 'emi', 'weighed_barcode'],
            'units'              => ['piece', 'pair', 'dozen', 'metre'],
        ],
        'hardware' => [
            'label'              => 'Hardware Shop',
            'description'        => 'Fittings by piece, materials by weight or length.',
            'track_type'         => 'normal',
            'has_expiry'         => false,
            'disabled'           => ['batch', 'serial', 'warranty', 'loyalty', 'membership', 'emi', 'weighed_barcode'],
            'units'              => ['piece', 'dozen', 'kg', 'gram', 'metre', 'foot', 'litre'],
        ],
        'cosmetics' => [
            'label'              => 'Cosmetics Shop',
            'description'        => 'Products received in dated lots and sold earliest expiry first.',
            'track_type'         => 'batch',
            'has_expiry'         => true,
            'disabled'           => ['serial', 'warranty', 'emi', 'weighed_barcode'],
            'units'              => ['piece', 'ml', 'gram'],
        ],
        'wholesale' => [
            'label'              => 'Wholesale Shop',
            'description'        => 'Bulk sales to registered customers on credit, by piece, dozen or weight.',
            'track_type'         => 'normal',
            'has_expiry'         => false,
            'disabled'           => ['serial', 'warranty', 'loyalty', 'membership', 'emi', 'hold_sale', 'weighed_barcode'],
            'units'              => ['piece', 'dozen', 'kg', 'gram', 'litre'],
        ],
        'general' => [
            'label'              => 'General / Multi-purpose Store',
            'description'        => 'Everything switched on; choose tracking per product.',
            'track_type'         => 'normal',
            'has_expiry'         => false,
            'disabled'           => [],
            'units'              => ['piece', 'dozen', 'kg', 'gram', 'litre', 'ml', 'metre', 'set'],
        ],
    ],
];
