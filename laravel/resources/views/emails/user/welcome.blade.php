@extends('layouts.email.base')

@section('title', 'Welcome to ' . config('app.name'))

@section('content')
    {{-- Pass variables to components --}}
    @php
        $headerSubtitle = 'Welcome to our marketplace!';
        $showQuickLinks = true;
        $showUnsubscribe = true;
    @endphp

    <h1 style="color: #111827; font-size: 28px; margin-bottom: 20px; text-align: center;">
        🎉 Welcome to {{ config('app.name') }}, {{ $user->name }}!
    </h1>
    
    <p style="color: #374151; font-size: 18px; line-height: 1.6; margin-bottom: 30px; text-align: center;">
        Thank you for joining our marketplace! We're excited to have you as part of the {{ config('app.name') }} community.
    </p>
    
    {{-- Welcome Benefits --}}
    @include('layouts.email.components.info-box', [
        'title' => '🎁 What You Can Do Now',
        'style' => 'success',
        'items' => [
            ['label' => '🛒 Shop Products', 'value' => 'Browse thousands of quality products'],
            ['label' => '⚡ Fast Delivery', 'value' => 'Get your orders delivered quickly'],
            ['label' => '💳 Secure Payment', 'value' => 'Safe and secure checkout process'],
            ['label' => '📞 24/7 Support', 'value' => 'Our team is always here to help']
        ]
    ])
    
    {{-- Get Started Buttons --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding: 0 10px;">
                            @include('layouts.email.components.button', [
                                'url' => config('app.frontend_url') . '/products',
                                'text' => '🛍️ Start Shopping',
                                'color' => 'primary',
                                'align' => 'center'
                            ])
                        </td>
                        <td style="padding: 0 10px;">
                            @include('layouts.email.components.button', [
                                'url' => config('app.frontend_url') . '/account/profile',
                                'text' => '👤 Complete Profile',
                                'color' => 'info',
                                'align' => 'center'
                            ])
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    {{-- Quick Tips --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 30px;">
        <tr>
            <td style="background-color: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px; padding: 20px;">
                <h3 style="color: #1e40af; font-size: 16px; margin: 0 0 15px 0; text-align: center;">
                    💡 Quick Tips for New Members
                </h3>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td style="color: #1e40af; font-size: 14px; line-height: 1.6;">
                            <p style="margin: 0 0 10px 0;">
                                📱 <strong>Download our mobile app</strong> for the best shopping experience
                            </p>
                            <p style="margin: 0 0 10px 0;">
                                🔔 <strong>Enable notifications</strong> to get updates on your orders and special offers
                            </p>
                            <p style="margin: 0;">
                                💰 <strong>Check out our deals section</strong> for amazing discounts and promotions
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    {{-- Contact Section --}}
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin-top: 30px;">
        <tr>
            <td style="text-align: center; padding: 20px 0; border-top: 1px solid #e5e7eb;">
                <h3 style="color: #374151; font-size: 16px; margin: 0 0 10px 0;">
                    Need Help Getting Started?
                </h3>
                <p style="color: #6b7280; font-size: 14px; line-height: 1.6; margin: 0;">
                    Our customer support team is available 24/7 to help you with any questions.<br>
                    Feel free to contact us anytime!
                </p>
            </td>
        </tr>
    </table>
    
    {{-- Thank You Message --}}
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-top: 30px; margin-bottom: 10px; text-align: center;">
        <strong>Happy Shopping!</strong>
    </p>
    
    <p style="color: #374151; font-size: 16px; line-height: 1.6; margin: 0; text-align: center;">
        <strong>Welcome aboard,</strong><br>
        The {{ config('app.name') }} Team
    </p>
@endsection