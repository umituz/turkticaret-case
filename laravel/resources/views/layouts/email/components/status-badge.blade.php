{{-- 
    Email Status Badge Component
    
    Usage:
    @include('layouts.email.components.status-badge', [
        'status' => 'confirmed', // confirmed, processing, shipped, delivered, cancelled, refunded
        'text' => 'Order Confirmed' // optional, defaults to ucfirst($status)
    ])
--}}

@php
    $badgeStyles = [
        'confirmed' => 'background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;',
        'processing' => 'background-color: #fef3c7; color: #d97706; border: 1px solid #fde68a;',
        'shipped' => 'background-color: #e0e7ff; color: #5b21b6; border: 1px solid #c7d2fe;',
        'delivered' => 'background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;',
        'cancelled' => 'background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca;',
        'refunded' => 'background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db;',
        'pending' => 'background-color: #f9fafb; color: #6b7280; border: 1px solid #e5e7eb;',
        'completed' => 'background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;',
        'active' => 'background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;',
        'inactive' => 'background-color: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db;',
    ];
    
    $selectedStyle = $badgeStyles[$status] ?? $badgeStyles['pending'];
    $displayText = $text ?? ucfirst($status);
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 10px 0;">
    <tr>
        <td>
            <span style="display: inline-block; padding: 6px 12px; {{ $selectedStyle }} border-radius: 20px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $displayText }}
            </span>
        </td>
    </tr>
</table>