@extends('me::master')

@section('title', trans('Add Discount Rule'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.discount-rules.store') }}" method="POST">
            @csrf
            @include('retail::discount-rules._form')
            @component('me::components.btn.save-button', ['text' => __('Save')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
