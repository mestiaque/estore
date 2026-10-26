@extends('me::master')

@section('title', trans('Edit Unit'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.units.update', $unit->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::units._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
