{{-- Shared print header — printMaster2 defines the .print-header/.company-*/.report-title
     CSS classes but leaves them unused, so every content view that extends it includes this
     once. Layout: logo (left) | company name/address (center) | QR code (right), invoice
     title centered below.

     Logo/QR size is passed in as $logoQrSize (default 60) and applied inline rather than
     via an overridable CSS class — an including view's own @push('css') always renders
     BEFORE this partial's (Blade groups pushes by render depth, not source order, so a
     nested @include's pushes land after the parent's regardless of where they're written),
     so a plain class-based override from e.g. thermal58.blade.php could never actually win. --}}
@php
    $logoQrSize = $logoQrSize ?? 60;
    $qrText = $printQr ?? null;
@endphp
<div class="print-header">
    <div class="header-row">
        <div class="header-logo" style="flex-basis: {{ $logoQrSize }}px; width: {{ $logoQrSize }}px;">
            @if(get_setting('shop_logo'))
                <img src="{{ route('shop_logo.show', get_setting('shop_logo')) }}" class="company-logo" style="width: {{ $logoQrSize }}px; height: {{ $logoQrSize }}px;">
            @else
                <img src="{{ asset('assets/img/default-img/Encodex_c.png') }}" class="company-logo" style="width: {{ $logoQrSize }}px; height: {{ $logoQrSize }}px;">
            @endif
        </div>
        <div class="header-company">
            <div class="company-name">{{ get_setting('shop_name', config('app.name')) }}</div>
            <div class="company-address">{{ get_setting('shop_address') }}</div>
            <div class="company-contact">@lang('Phone'): {{ get_setting('shop_phone') }} | @lang('Email'): {{ get_setting('shop_email') }}</div>
        </div>
        <div class="header-qr" style="flex-basis: {{ $logoQrSize }}px; width: {{ $logoQrSize }}px;">
            @if($qrText)
                <div id="header-qr-code"></div>
            @endif
        </div>
    </div>
    <div class="report-title"><span>{{ $printTitle ?? 'INVOICE' }}</span></div>
</div>

@push('css')
<style>
    .print-header .header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 5px;
    }
    .print-header .header-logo,
    .print-header .header-qr {
        display: flex;
        justify-content: center;
        flex-shrink: 0;
    }
    .print-header .header-company {
        flex: 1 1 auto;
        text-align: center;
        min-width: 0;
    }
    .print-header .header-qr canvas {
        max-width: 100%;
        max-height: 100%;
    }
    .print-header .report-title {
        margin: 6px 0 0;
        text-align: center;
    }
    .print-header .report-title span {
        display: inline-block;
        padding: 2px 0;
        background: none;
        color: #0047ab;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 4px;
        border-radius: 0;
        border-bottom: 2px solid #0047ab;
    }
</style>
@endpush

@if($qrText)
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('header-qr-code'), {
        text: @json($qrText),
        width: {{ $logoQrSize }},
        height: {{ $logoQrSize }},
        correctLevel: QRCode.CorrectLevel.M,
    });
</script>
@endpush
@endif
