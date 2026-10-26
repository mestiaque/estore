@extends('me::master')

@section('title', trans('Edit Customer Level'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.customer-levels.update', $level->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::customer-levels._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
