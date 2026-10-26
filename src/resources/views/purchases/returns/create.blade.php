@extends('me::master')

@section('title', trans('Process Purchase Return'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <p>@lang('Purchase'): <strong>{{ $purchase->invoice_number }}</strong> &mdash; {{ $purchase->supplier->name ?? __('N/A') }}</p>

        <form action="{{ route('retail.purchases.returns.store', $purchase->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th>@lang('Product')</th>
                            <th>@lang('Unit')</th>
                            <th>@lang('Received')</th>
                            <th>@lang('Already Returned')</th>
                            <th>@lang('Remaining')</th>
                            <th style="min-width:120px">@lang('Return Quantity')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                            @php
                                $ratio = (float) $item->quantity > 0 ? (float) $item->quantity_base / (float) $item->quantity : 0;
                                $remaining = $ratio > 0 ? round($item->remainingReturnableBaseQuantity() / $ratio, 4) : 0;
                                $isSerial = $item->product->track_type === 'serial';
                                $availableSerials = $isSerial ? $item->serials->where('status', \ME\Retail\Enums\SerialStatus::Available) : collect();
                            @endphp
                            <tr>
                                <td>
                                    {{ $item->product->name ?? __('N/A') }}
                                    @if($item->batch)
                                        <br><small class="text-muted">@lang('Batch'): {{ $item->batch->batch_number }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->unit->name ?? __('N/A') }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $ratio > 0 ? round($item->returned_quantity_base / $ratio, 4) : 0 }}</td>
                                <td>{{ $remaining }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $loop->index }}][purchase_item_id]" value="{{ $item->id }}">
                                    @if($isSerial)
                                        @if($availableSerials->isEmpty())
                                            <span class="text-muted">@lang('None returnable')</span>
                                        @else
                                            <select multiple size="4" class="form-control form-control-sm" name="items[{{ $loop->index }}][serial_ids][]">
                                                @foreach($availableSerials as $serial)
                                                    <option value="{{ $serial->id }}">{{ $serial->serial_number }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @else
                                        <input type="number" step="0.0001" min="0" max="{{ $remaining }}"
                                            name="items[{{ $loop->index }}][quantity]" class="form-control form-control-sm"
                                            {{ $remaining <= 0 ? 'disabled' : '' }}>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <label class="form-label">@lang('Notes')</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>

            @component('me::components.btn.save-button', ['text' => __('Process Return')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
