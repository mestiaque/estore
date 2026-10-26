@extends('me::master')

@section('title', trans('Sales Report'))

@section('content')
@component('retail::reports._date-filter', ['from' => $from, 'to' => $to])
    @slot('extra')
        <div class="col-auto">
            <label class="form-label mb-0">@lang('Group By')</label>
            <select name="group_by" class="form-control form-control-sm">
                @foreach(['day' => 'Day', 'product' => 'Product', 'category' => 'Category', 'customer' => 'Customer', 'cashier' => 'Cashier', 'store' => 'Store', 'payment_method' => 'Payment Method'] as $value => $label)
                    <option value="{{ $value }}" {{ $groupBy === $value ? 'selected' : '' }}>@lang($label)</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label mb-0">@lang('Store')</label>
            <select name="store_id" class="form-control form-control-sm">
                <option value="">@lang('All Stores')</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ (string) $storeId === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                @endforeach
            </select>
        </div>
    @endslot
@endcomponent

<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>{{ ucfirst(str_replace('_', ' ', $groupBy)) }}</th>
                    @if(in_array($groupBy, ['product', 'day']))
                        <th class="text-end">@lang('Quantity')</th>
                    @endif
                    @if($groupBy === 'day')
                        <th class="text-end">@lang('Invoices')</th>
                    @endif
                    <th class="text-end">@lang('Total')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        @if(in_array($groupBy, ['product', 'day']))
                            <td class="text-end">{{ $row->quantity ?? '' }}</td>
                        @endif
                        @if($groupBy === 'day')
                            <td class="text-end">{{ $row->invoice_count }}</td>
                        @endif
                        <td class="text-end">{{ number_format($row->total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">@lang('No data for the selected range')</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="{{ in_array($groupBy, ['product', 'day']) ? ($groupBy === 'day' ? 3 : 2) : 1 }}" class="text-end">@lang('Grand Total')</th>
                    <th class="text-end">{{ number_format($rows->sum('total'), 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
