@extends('me::master')

@section('title', trans('Edit Product'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::products._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
