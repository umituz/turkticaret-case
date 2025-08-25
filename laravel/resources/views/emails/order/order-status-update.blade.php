@extends('layouts.email.base')

@section('title', $statusMessage . ' - ' . config('app.name'))

@section('content')
    {{-- Pass header subtitle --}}
    @php
        $headerSubtitle = $statusMessage;
        $isAutomatedEmail = true;
        $showQuickLinks = true;
    @endphp

    <h2 style="color: #111827; font-size: 20px; margin-bottom: 20px;">
        Hello {{ $order->user->name }},
    </h2>
    
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 20px;">
        {{ $statusDescription }}
    </p>
    
    {{-- Status Badge --}}
    @include('layouts.email.components.status-badge', [
        'status' => $newStatus,
        'text' => ucfirst($newStatus)
    ])
    
    {{-- Order Details Info Box --}}
    @include('layouts.email.components.info-box', [
        'title' => '📦 Order Details',
        'items' => [
            ['label' => 'Order Number', 'value' => $order->order_number],
            ['label' => 'Order Date', 'value' => $order->created_at->format('M d, Y \a\t h:i A')],
            ['label' => 'Total Amount', 'value' => \App\Helpers\MoneyHelper::getAmountInfo($order->total_amount)['formatted']],
            ...(($order->shipped_at && $newStatus === 'shipped') ? [
                ['label' => 'Shipped Date', 'value' => $order->shipped_at->format('M d, Y \a\t h:i A')]
            ] : []),
            ...(($order->delivered_at && $newStatus === 'delivered') ? [
                ['label' => 'Delivered Date', 'value' => $order->delivered_at->format('M d, Y \a\t h:i A')]
            ] : []),
            ['label' => 'Shipping Address', 'value' => $order->shipping_address],
        ]
    ])
    
    {{-- Order Items Info Box --}}
    @if($order->orderItems && $order->orderItems->count() > 0)
        @include('layouts.email.components.info-box', [
            'title' => '📋 Order Items',
            'items' => $order->orderItems->map(function($item) {
                $unitPriceInfo = \App\Helpers\MoneyHelper::getAmountInfo($item->unit_price);
                return [
                    'label' => $item->product_name,
                    'value' => $item->quantity . 'x ' . $unitPriceInfo['formatted']
                ];
            })->toArray()
        ])
    @endif
    
    {{-- View Order Button --}}
    @include('layouts.email.components.button', [
        'url' => config('app.frontend_url') . '/account/orders/' . $order->uuid,
        'text' => 'View Order Details',
        'color' => 'primary',
        'align' => 'center'
    ])
    
    {{-- Help Section for Cancelled/Refunded Orders --}}
    @if(in_array($newStatus, ['cancelled', 'refunded']))
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 30px;">
            <tr>
                <td style="background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 8px; padding: 20px;">
                    <h3 style="color: #92400e; font-size: 16px; margin: 0 0 10px 0;">
                        💬 Need Help?
                    </h3>
                    <p style="color: #92400e; font-size: 14px; line-height: 1.5; margin: 0;">
                        If you have any questions about this {{ $newStatus === 'cancelled' ? 'cancellation' : 'refund' }}, 
                        please don't hesitate to contact our customer support team. We're here to help!
                    </p>
                </td>
            </tr>
        </table>
    @endif
    
    {{-- Thank You Message --}}
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-top: 30px; margin-bottom: 10px;">
        Thank you for choosing {{ config('app.name') }}!
    </p>
    
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0;">
        Best regards,<br>
        <strong>The {{ config('app.name') }} Team</strong>
    </p>
@endsection