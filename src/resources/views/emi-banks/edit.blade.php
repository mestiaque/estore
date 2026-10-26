@extends('me::master')

@section('title', trans('Edit Bank'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.emi-banks.update', $bank->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::emi-banks._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
