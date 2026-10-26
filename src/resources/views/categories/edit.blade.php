@extends('me::master')

@section('title', trans('Edit Category'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::categories._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
