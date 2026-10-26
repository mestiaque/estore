@extends('me::master')

@section('title', trans('Add EMI Plan'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.emi-plans.store') }}" method="POST">
            @csrf
            @include('retail::emi-plans._form')
            @component('me::components.btn.save-button', ['text' => __('Save')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
