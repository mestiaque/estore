@extends('retail::printMaster2')

@section('title', $sale->invoice_number)

@push('css')
<style>
    body { width: 58mm; font-family: Arial, sans-serif; }
    .container { width: 58mm; padding: 1mm; margin-top: 3rem; font-size: 9px; }
    .print-header { text-align: center; }
    .print-header .header-row { gap: 4px; }
    .company-name { font-size: 12px !important; }
    .company-address, .company-contact { font-size: 7px; }
    .thermal-line { display: flex; justify-content: space-between; }
    .thermal-item { border-bottom: 1px dashed #999; padding: 1px 0; }
    .thermal-divider { border-top: 1px dashed #333; margin: 3px 0; }
    table, th, td { border: none !important; padding: 1px !important; font-size: 8px !important; }
    th { background: none !important; }
    @media print {
        .container { width: 58mm; }
    }
</style>
@endpush

@section('contents')
@include('retail::invoices._header', ['logoQrSize' => 22])
@include('retail::invoices.sales._thermal-body')
@endsection
