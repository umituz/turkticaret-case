{{-- Email Footer Component --}}
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
            {{-- Footer Content --}}
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                {{-- Quick Links --}}
                @if(isset($showQuickLinks) && $showQuickLinks)
                <tr>
                    <td align="center" style="padding-bottom: 20px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding: 0 15px;">
                                    <a href="{{ config('app.frontend_url') }}/account" 
                                       style="color: #6b7280; font-size: 14px; text-decoration: none;">
                                        My Account
                                    </a>
                                </td>
                                <td style="color: #d1d5db;">|</td>
                                <td style="padding: 0 15px;">
                                    <a href="{{ config('app.frontend_url') }}/orders" 
                                       style="color: #6b7280; font-size: 14px; text-decoration: none;">
                                        Order History
                                    </a>
                                </td>
                                <td style="color: #d1d5db;">|</td>
                                <td style="padding: 0 15px;">
                                    <a href="{{ config('app.frontend_url') }}/help" 
                                       style="color: #6b7280; font-size: 14px; text-decoration: none;">
                                        Help Center
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif
                
                {{-- Social Media Links (Optional) --}}
                @if(isset($showSocialLinks) && $showSocialLinks)
                <tr>
                    <td align="center" style="padding-bottom: 20px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding: 0 10px;">
                                    <a href="#" style="color: #6b7280;">
                                        <img src="https://via.placeholder.com/24x24/6b7280/ffffff?text=f" 
                                             alt="Facebook" width="24" height="24" style="display: block;">
                                    </a>
                                </td>
                                <td style="padding: 0 10px;">
                                    <a href="#" style="color: #6b7280;">
                                        <img src="https://via.placeholder.com/24x24/6b7280/ffffff?text=t" 
                                             alt="Twitter" width="24" height="24" style="display: block;">
                                    </a>
                                </td>
                                <td style="padding: 0 10px;">
                                    <a href="#" style="color: #6b7280;">
                                        <img src="https://via.placeholder.com/24x24/6b7280/ffffff?text=i" 
                                             alt="Instagram" width="24" height="24" style="display: block;">
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endif
                
                {{-- Copyright & Legal --}}
                <tr>
                    <td align="center">
                        <p style="color: #6b7280; font-size: 14px; margin: 0;">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </p>
                        @if(isset($isAutomatedEmail) && $isAutomatedEmail)
                        <p style="color: #9ca3af; font-size: 12px; margin-top: 10px;">
                            This is an automated email. Please do not reply to this message.
                        </p>
                        @endif
                    </td>
                </tr>
                
                {{-- Unsubscribe Link (Optional) --}}
                @if(isset($showUnsubscribe) && $showUnsubscribe)
                <tr>
                    <td align="center" style="padding-top: 20px;">
                        <a href="{{ config('app.frontend_url') }}/account/email-preferences" 
                           style="color: #9ca3af; font-size: 12px; text-decoration: underline;">
                            Manage Email Preferences
                        </a>
                        <span style="color: #d1d5db; margin: 0 5px;">|</span>
                        <a href="{{ config('app.frontend_url') }}/unsubscribe" 
                           style="color: #9ca3af; font-size: 12px; text-decoration: underline;">
                            Unsubscribe
                        </a>
                    </td>
                </tr>
                @endif
                
                {{-- Company Address (Optional) --}}
                @if(isset($showAddress) && $showAddress)
                <tr>
                    <td align="center" style="padding-top: 20px;">
                        <p style="color: #9ca3af; font-size: 11px; line-height: 1.4;">
                            {{ config('app.name') }} Inc.<br>
                            123 Commerce Street, Suite 100<br>
                            Istanbul, Turkey 34000
                        </p>
                    </td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>