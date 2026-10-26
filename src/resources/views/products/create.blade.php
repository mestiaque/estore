@extends('me::master')

@section('title', trans('Add Product'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.products.store') }}" method="POST">
            @csrf
            @include('retail::products._form')
            @component('me::components.btn.save-button', ['text' => __('Save')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
