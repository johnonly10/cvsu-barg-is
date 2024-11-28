@component('mail::message')
# Pre-Order Notification

Hello {{ $user->name }},

We are excited to inform you that the product **{{ $preOrder->product->name }}** that you pre-ordered is now ready for you.

## Order Details:
- **Product:** {{ $preOrder->product->name }}
- **Quantity:** {{ $preOrder->quantity }}
- **Price:** ${{ $preOrder->product->price }}

@if($preOrder->status == 'reserved')
@component('mail::button', ['url' => route('preorders.accept', $preOrder->id)])
    Accept Pre-Order
@endcomponent

If you no longer want to proceed with the pre-order, you can cancel it anytime.

@component('mail::button', ['url' => route('preorders.cancel', $preOrder->id)])
    Cancel Pre-Order
@endcomponent
@else
    <span class="text-muted">No Actions Available</span>
@endif

Thank you for shopping with us!

Best Regards,<br>
{{ config('app.name') }}
@endcomponent

{{-- @component('mail::message')
# Pre-Order Notification

Hello {{ $user->name }},

We are excited to inform you that the product **{{ $preOrder->product->name }}** that you pre-ordered is now ready for you.

## Order Details:
- **Product:** {{ $preOrder->product->name }}
- **Quantity:** {{ $preOrder->quantity }}
- **Price:** ${{ number_format($preOrder->price, 2) }}

@if($preOrder->status == 'reserved')
@component('mail::button', ['url' => route('preorders.accept', ['preOrder' => $preOrder->id])])
Accept Pre-Order
@endcomponent

If you no longer want to proceed with the pre-order, you can cancel it anytime.

@component('mail::button', ['url' => route('preorders.cancel', ['preOrder' => $preOrder->id])])
Cancel Pre-Order
@endcomponent
@else
<span class="text-muted">No Actions Available</span>
@endif

Thank you for shopping with us!

Best Regards,<br>
{{ config('app.name') }}
@endcomponent --}}