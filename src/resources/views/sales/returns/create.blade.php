@extends('me::master')

@section('title', trans('Process Sale Return'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <p>@lang('Sale'): <strong>{{ $sale->invoice_number }}</strong> &mdash; {{ $sale->customer->name ?? __('Walk-in Customer') }}</p>

        <form action="{{ route('retail.sales.returns.store', $sale->id) }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th>@lang('Product')</th>
                            <th>@lang('Unit')</th>
                            <th>@lang('Sold')</th>
                            <th>@lang('Already Returned')</th>
                            <th>@lang('Remaining')</th>
                            <th style="min-width:120px">@lang('Return Quantity')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                            @php
                                $ratio = (float) $item->quantity > 0 ? (float) $item->quantity_base / (float) $item->quantity : 0;
                                $remaining = $ratio > 0 ? round($item->remainingReturnableBaseQuantity() / $ratio, 4) : 0;
                                $isSerial = $item->product->track_type === 'serial';
                                $soldSerials = $isSerial
                                    ? $item->serials->filter(fn($s) => $s->status === \ME\Retail\Enums\SerialStatus::Sold)
                                    : collect();
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
                                    <input type="hidden" name="items[{{ $loop->index }}][sale_item_id]" value="{{ $item->id }}">
                                    @if($isSerial)
                                        @if($soldSerials->isEmpty())
                                            <span class="text-muted">@lang('None returnable')</span>
                                        @else
                                            <select multiple size="4" class="form-control form-control-sm" name="items[{{ $loop->index }}][serial_ids][]">
                                                @foreach($soldSerials as $serial)
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

            @if($openSessions->isNotEmpty())
            <div class="mb-3">
                <label class="form-label">@lang('Refund From Cash Register')</label>
                <select name="cash_register_session_id" class="form-control">
                    <option value="">@lang('None (refund not paid from a drawer)')</option>
                    @foreach($openSessions as $session)
                        <option value="{{ $session->id }}">{{ $session->cashRegister->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

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
