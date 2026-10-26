@extends('me::master')

@section('title', trans('Edit Membership'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.memberships.update', $membership->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::memberships._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
