@extends('me::master')

@section('title', trans('Edit Expense Category'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.expense-categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('retail::expense-categories._form')
            @component('me::components.btn.save-button', ['text' => __('Update')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
