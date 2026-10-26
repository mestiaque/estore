@extends('me::master')

@section('title', trans('Edit Warehouse'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.warehouses.update', $warehouse->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::warehouses._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
