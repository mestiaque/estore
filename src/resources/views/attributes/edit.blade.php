@extends('me::master')

@section('title', trans('Edit Attribute'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.attributes.update', $attribute->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::attributes._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
