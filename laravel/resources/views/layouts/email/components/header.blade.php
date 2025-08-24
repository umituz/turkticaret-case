{{-- Email Header Component --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="background: linear-gradient(135deg, #dc2626, #ef4444); padding: 30px; text-align: center;">
            {{-- Logo & Brand --}}
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <td align="center">
                        <h1 style="color: #ffffff; font-size: 28px; font-weight: bold; margin: 0;">
                            🛍️ {{ config('app.name') }}
                        </h1>
                        @if(isset($headerSubtitle))
                            <p style="color: #ffffff; font-size: 16px; margin-top: 10px; opacity: 0.95;">
                                {{ $headerSubtitle }}
                            </p>
                        @else
                            <p style="color: #ffffff; font-size: 14px; margin-top: 10px; opacity: 0.9;">
                                Your Trusted E-Commerce Platform
                            </p>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@push('styles')
<style>
    /* Header-specific animations for email clients that support it */
    @media screen and (-webkit-min-device-pixel-ratio: 0) {
        .header-gradient {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
        }
    }
</style>
@endpush