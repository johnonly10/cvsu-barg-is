<!-- resources/views/preorders/cancel.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">Pre-Order Canceled</h4>
        <p>Your pre-order for <strong>{{ $preOrder->product->name }}</strong> has been canceled successfully.</p>
        <hr>
        <p class="mb-0">The pre-ordered item has been removed from your orders. You can still place a new order if desired.</p>
    </div>

    <a href="{{ route('user.orders') }}" class="btn btn-primary">View Your Orders</a>
</div>
@endsection
