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
        'text' => $statusMessage
    ])
    
    {{-- Order Details Info Box --}}
    @php
        $orderDetailsItems = [
            ['label' => 'Order Number', 'value' => $order_number],
            ['label' => 'Order Date', 'value' => $order_date_formatted],
        ];
        
        // Add status-specific dates
        $orderDetailsItems = array_merge($orderDetailsItems, $status_dates);
        
        // Add shipping address
        $orderDetailsItems[] = ['label' => 'Shipping Address', 'value' => $order->shipping_address];
    @endphp
    
    @include('layouts.email.components.info-box', [
        'title' => '📦 Order Details',
        'items' => $orderDetailsItems
    ])
    
    {{-- Order Items Info Box --}}
    @if($order_items_display && count($order_items_display) > 0)
        @include('layouts.email.components.info-box', [
            'title' => '📋 Order Items',
            'items' => $order_items_display
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