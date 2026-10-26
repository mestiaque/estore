@extends('me::master')

@section('title', trans('Add Membership'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.memberships.store') }}" method="POST">
            @csrf
            @include('retail::memberships._form')
            @component('me::components.btn.save-button', ['text' => __('Save')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
