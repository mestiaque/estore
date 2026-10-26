@extends('me::master')

@section('title', trans('Edit Discount Rule'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.discount-rules.update', $rule->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::discount-rules._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
