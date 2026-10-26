@extends('me::master')

@section('title', trans('Edit Store'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.stores.update', $store->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::stores._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
