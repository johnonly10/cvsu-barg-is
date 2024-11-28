@extends('layouts.app')

@section('content')
<div class="container">
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Pre-Order Canceled!</h4>
        <p>Your pre-order for <strong>{{ $preOrder->product->name }}</strong> has been successfully canceled.</p>
        <hr>
        <p class="mb-0">The quantity you reserved has been released back into stock.</p>
    </div>

    <a href="{{ route('user.orders') }}" class="btn btn-primary">View Your Orders</a>
</div>
@endsection
