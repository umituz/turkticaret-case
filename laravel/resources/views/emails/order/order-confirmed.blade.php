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
        🎉 Your Order is Confirmed!
    </h1>

    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
        Hi {{ $order->user->name }},
    </p>

    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 30px;">
        Great news! Your order has been confirmed and we're now preparing it for shipment.
        We'll send you tracking details as soon as your order is on its way.
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
            ['label' => 'Order Number', 'value' => '#' . $order_number],
            ['label' => 'Order Date', 'value' => $order_date_formatted],
            ['label' => 'Status', 'value' => $status_label],
            ['label' => 'Estimated Delivery', 'value' => $estimated_delivery_formatted],
        ]
    ])

    @if($order_items_data && count($order_items_data) > 0)
        @php
            // Add total amount at the end of items
            $allItems = array_merge($order_items_data, [
                [
                    'label' => '<strong>Total Amount</strong>',
                    'value' => '<strong>' . $total_amount_formatted . '</strong>'
                ]
            ]);
        @endphp

        @include('layouts.email.components.info-box', [
            'title' => '🛒 Items Ordered',
            'items' => $allItems
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
                    💬 Need Help?
                </h3>
                <p style="color: #1e40af; font-size: 14px; line-height: 1.5; margin: 0;">
                    Have questions about your order? Our customer support team is available 24/7 to assist you.
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
