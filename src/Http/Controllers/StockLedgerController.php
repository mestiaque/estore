<?php

namespace ME\Retail\Http\Controllers;

use ME\Http\Controllers\Controller;
use ME\Retail\Models\StockLedger;

class StockLedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware('authorization:retail_inventory.view')->only(['index', 'show']);
    }

    public function index()
    {
        $ledgers = StockLedger::with(['product', 'warehouse'])
            ->latest('id')
            ->paginate(get_setting('pagination', 10));

        return view('retail::inventory.ledger-index', compact('ledgers'));
    }

    public function show(StockLedger $stockLedger)
    {
        $stockLedger->load(['product', 'warehouse']);
        return view('retail::inventory.ledger-show', ['ledger' => $stockLedger]);
    }
}
