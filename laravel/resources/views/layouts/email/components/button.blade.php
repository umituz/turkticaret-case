{{-- 
    Email Button Component
    
    Usage:
    @include('layouts.email.components.button', [
        'url' => 'https://example.com',
        'text' => 'Click Me',
        'color' => 'primary' // optional: primary (default), success, warning, danger, dark
        'align' => 'center' // optional: left, center (default), right
    ])
--}}

@php
    $buttonColors = [
        'primary' => 'background: linear-gradient(135deg, #dc2626, #ef4444);',
        'success' => 'background: linear-gradient(135deg, #059669, #10b981);',
        'warning' => 'background: linear-gradient(135deg, #d97706, #f59e0b);',
        'danger' => 'background: linear-gradient(135deg, #dc2626, #ef4444);',
        'dark' => 'background: linear-gradient(135deg, #1f2937, #374151);',
        'info' => 'background: linear-gradient(135deg, #2563eb, #3b82f6);',
    ];
    
    $selectedColor = $buttonColors[$color ?? 'primary'] ?? $buttonColors['primary'];
    $alignment = $align ?? 'center';
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td align="{{ $alignment }}" style="padding: 20px 0;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td style="{{ $selectedColor }} border-radius: 6px;">
                        <a href="{{ $url }}" 
                           target="_blank"
                           style="display: inline-block; padding: 14px 28px; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 14px; line-height: 1;">
                            {{ $text }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>