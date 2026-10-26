@extends('me::master')

@section('title', trans('Add Warehouse'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.warehouses.store') }}" method="POST">
            @csrf
            @include('retail::warehouses._form')
            @component('me::components.btn.save-button', ['text' => __('Save')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
