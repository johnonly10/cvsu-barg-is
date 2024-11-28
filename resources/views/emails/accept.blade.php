@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-success" role="alert">
        <h4 class="alert-heading">Pre-Order Accepted!</h4>
        <p>Your pre-order for <strong>{{ $preOrder->product->name }}</strong> has been successfully accepted.</p>
        <hr>
        <p class="mb-0">Your order is now reserved and will be processed accordingly.</p>
    </div>

    <a href="{{ route('user.orders') }}" class="btn btn-primary">View Your Orders</a>
</div>
@endsection