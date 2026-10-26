@extends('me::master')

@section('title', trans('Add Unit'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.units.store') }}" method="POST">
            @csrf
            @include('retail::units._form')
            @component('me::components.btn.save-button', ['text' => __('Save')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
