@extends('me::master')

@section('title', trans('Edit Brand'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.brands.update', $brand->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::brands._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
