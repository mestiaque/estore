@extends('me::master')

@section('title', trans('Edit Cash Register'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.cash-registers.update', $register->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::cash-registers._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
