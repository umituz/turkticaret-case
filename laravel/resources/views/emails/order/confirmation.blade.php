@extends('layouts.email.base')

@section('title', 'Order Confirmation - ' . config('app.name'))

@section('content')
    {{-- Pass variables to components --}}
    @php
        $headerSubtitle = 'Order Confirmation #' . substr($order->uuid, 0, 8);
        $isAutomatedEmail = true;
        $showQuickLinks = true;
    @endphp

    <h1 style="color: #111827; font-size: 24px; margin-bottom: 20px; text-align: center;">
        🎉 Order Confirmed!
    </h1>
    
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
        Hello {{ $order->user->name }},
    </p>
    
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
        Thank you for your order! We've received your order and are preparing it for delivery. 
        You'll receive another email with tracking information once your order ships.
    </p>
    
    {{-- Status Badge --}}
    @include('layouts.email.components.status-badge', [
        'status' => 'confirmed',
        'text' => 'Order Confirmed'
    ])
    
    {{-- Order Summary Info Box --}}
    @include('layouts.email.components.info-box', [
        'title' => '📦 Order Summary',
        'style' => 'highlight',
        'items' => [
            ['label' => 'Order Number', 'value' => '#' . substr($order->uuid, 0, 8)],
            ['label' => 'Order Date', 'value' => $order->created_at->format('M d, Y \a\t h:i A')],
            ['label' => 'Estimated Delivery', 'value' => $order->created_at->addDays(3)->format('M d, Y')],
        ]
    ])
    
    {{-- Order Items Info Box --}}
    @if($order->orderItems && $order->orderItems->count() > 0)
        @php
            $orderItemsData = [];
            foreach($order->orderItems as $item) {
                $orderItemsData[] = [
                    'label' => $item->product_name . ' (x' . $item->quantity . ')',
                    'value' => '₺' . number_format($item->total_price / 100, 2)
                ];
            }
            // Add total at the end
            $orderItemsData[] = [
                'label' => '<strong>Total Amount</strong>',
                'value' => '<strong>₺' . number_format($order->total_amount / 100, 2) . '</strong>'
            ];
        @endphp
        
        @include('layouts.email.components.info-box', [
            'title' => '🛒 Items Ordered',
            'items' => $orderItemsData
        ])
    @endif
    
    {{-- Shipping Information --}}
    @if($order->shipping_address)
        @include('layouts.email.components.info-box', [
            'title' => '🚚 Shipping Information',
            'items' => [
                ['label' => 'Delivery Address', 'value' => $order->shipping_address],
                ['label' => 'Shipping Method', 'value' => $order->shipping_method ?? 'Standard Delivery']
            ]
        ])
    @endif
    
    {{-- Track Order Button --}}
    @include('layouts.email.components.button', [
        'url' => config('app.frontend_url') . '/account/orders/' . $order->uuid,
        'text' => '📱 Track Your Order',
        'color' => 'success',
        'align' => 'center'
    ])
    
    {{-- Help Section --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 30px;">
        <tr>
            <td style="background-color: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px; padding: 20px; text-align: center;">
                <h3 style="color: #1e40af; font-size: 16px; margin: 0 0 10px 0;">
                    💬 Questions About Your Order?
                </h3>
                <p style="color: #1e40af; font-size: 14px; line-height: 1.5; margin: 0;">
                    Our customer support team is here to help! Contact us anytime with questions about your order.
                </p>
            </td>
        </tr>
    </table>
    
    {{-- Thank You Message --}}
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-top: 30px; margin-bottom: 10px; text-align: center;">
        Thank you for choosing {{ config('app.name') }}!
    </p>
    
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0; text-align: center;">
        <strong>Best regards,</strong><br>
        The {{ config('app.name') }} Team
    </p>
@endsection