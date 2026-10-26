@extends('me::master')

@section('title', trans('Add Customer'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.customers.store') }}" method="POST">
            @csrf
            @include('retail::customers._form')
            @component('me::components.btn.save-button', ['text' => __('Save')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
