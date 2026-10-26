@extends('retail::printMaster2')

@section('title', trans('Barcode Labels'))

@push('css')
<style>
    .container { padding: 4mm; margin-top: 3rem; }
    .sheet { display: flex; flex-wrap: wrap; gap: 2mm; }
    .label {
        width: {{ $dimensions['width'] }}mm;
        height: {{ $dimensions['height'] }}mm;
        border: 1px dashed #ccc;
        padding: 1mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        text-align: center;
    }
    .label .name { font-size: 8px; font-weight: bold; line-height: 1.1; max-height: 2.2em; overflow: hidden; }
    .label .price { font-size: 9px; font-weight: bold; }
    .label svg { width: 90%; height: auto; }
    @media print {
        .label { border: none; }
    }
</style>
@endpush

@section('contents')
<div class="sheet">
    @foreach($labels as $index => $product)
        <div class="label">
            <div class="name">{{ $product->name }}</div>
            <svg class="barcode" data-code="{{ $product->barcode ?? $product->sku }}"></svg>
            <div class="price">{{ number_format($product->sale_price, 2) }}</div>
        </div>
    @endforeach
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.11.5/JsBarcode.all.min.js"></script>
<script>
    document.querySelectorAll('.barcode').forEach(function (el) {
        JsBarcode(el, el.dataset.code, {
            format: 'CODE128',
            displayValue: true,
            fontSize: 10,
            height: 30,
            margin: 0,
        });
    });
</script>
@endpush
