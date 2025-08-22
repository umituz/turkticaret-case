@extends('layouts.mail')

@section('title', 'Order Confirmation')

@section('content')
    <h1>Order Confirmation #{{ substr($order->uuid, 0, 8) }}</h1>
    
    <p>Hello {{ $order->user->name }},</p>
    
    <p>Thank you for your order! We've received your order and are preparing it for delivery.</p>
    
    <div class="highlight-box">
        <h2>Order Details</h2>
        @foreach($order->orderItems as $item)
            <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                <span>{{ $item->product_name }} (x{{ $item->quantity }})</span>
                <span style="font-weight: 600;">${{ number_format($item->total_price / 100, 2) }}</span>
            </div>
        @endforeach
        <div style="display: flex; justify-content: space-between; padding: 16px 0; border-top: 2px solid #dc3545; margin-top: 10px; font-weight: 700; font-size: 18px;">
            <span>Total</span>
            <span>${{ number_format($order->total_amount / 100, 2) }}</span>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <p style="text-align: center; color: #6c757d;">
        Questions? Contact our support team anytime.
    </p>
    
    <p style="text-align: center; margin-top: 20px;">
        <strong>Best regards,</strong><br>
        The TurkTicaret Team
    </p>
@endsection