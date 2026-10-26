@extends('me::master')

@section('title', trans('Edit EMI Plan'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.emi-plans.update', $plan->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::emi-plans._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
