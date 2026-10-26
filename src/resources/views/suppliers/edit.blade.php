@extends('me::master')

@section('title', trans('Edit Supplier'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::suppliers._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
