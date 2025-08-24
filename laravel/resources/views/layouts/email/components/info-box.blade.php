{{-- 
    Email Info Box Component
    
    Usage:
    @include('layouts.email.components.info-box', [
        'title' => 'Order Details', // optional
        'items' => [
            ['label' => 'Order Number', 'value' => '#12345'],
            ['label' => 'Total', 'value' => '$99.99'],
        ],
        'style' => 'default' // optional: default, highlight, warning, success
    ])
--}}

@php
    $boxStyles = [
        'default' => 'background-color: #f9fafb; border: 1px solid #e5e7eb;',
        'highlight' => 'background-color: #eff6ff; border: 1px solid #dbeafe;',
        'warning' => 'background-color: #fef3c7; border: 1px solid #fde68a;',
        'success' => 'background-color: #d1fae5; border: 1px solid #a7f3d0;',
        'danger' => 'background-color: #fee2e2; border: 1px solid #fecaca;',
    ];
    
    $selectedStyle = $boxStyles[$style ?? 'default'] ?? $boxStyles['default'];
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 20px 0;">
    <tr>
        <td style="{{ $selectedStyle }} padding: 20px; border-radius: 8px;">
            @if(isset($title))
            <h3 style="margin: 0 0 15px 0; color: #374151; font-size: 16px; font-weight: 600;">
                {{ $title }}
            </h3>
            @endif
            
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                @foreach($items as $index => $item)
                <tr>
                    <td style="padding: 8px 0; {{ $index < count($items) - 1 ? 'border-bottom: 1px solid #e5e7eb;' : '' }}">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tr>
                                <td style="color: #6b7280; font-size: 14px; font-weight: 500; width: 40%;">
                                    {{ $item['label'] }}:
                                </td>
                                <td style="color: #111827; font-size: 14px; text-align: right;">
                                    {!! $item['value'] !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>