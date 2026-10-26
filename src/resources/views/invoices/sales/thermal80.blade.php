@extends('retail::printMaster2')

@section('title', $sale->invoice_number)

@push('css')
<style>
    body { width: 80mm; font-family: Arial, sans-serif; }
    .container { width: 80mm; padding: 2mm; margin-top: 3rem; font-size: 11px; }
    .print-header .header-row { gap: 6px; }
    .company-name { font-size: 16px !important; }
    .company-address, .company-contact { font-size: 9px; }
    .thermal-line { display: flex; justify-content: space-between; }
    .thermal-item { border-bottom: 1px dashed #999; padding: 2px 0; }
    .thermal-divider { border-top: 1px dashed #333; margin: 4px 0; }
    table, th, td { border: none !important; padding: 2px !important; font-size: 10px !important; }
    th { background: none !important; }
    @media print {
        .container { width: 80mm; }
    }
</style>
@endpush

@section('contents')
@include('retail::invoices._header', ['logoQrSize' => 36])
@include('retail::invoices.sales._thermal-body')
@endsection
