<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name') . ' - Your Trusted E-Commerce Platform')</title>
    
    {{-- Email Client CSS Reset & Base Styles --}}
    <style>
        /* Reset styles */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        
        /* Base styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
            background-color: #f4f4f4 !important;
            width: 100% !important;
            min-width: 100% !important;
        }
        
        /* Container styles */
        .email-wrapper {
            width: 100%;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            margin: 0;
            padding: 0;
            font-weight: bold;
            line-height: 1.4;
        }
        
        p {
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        a {
            color: #dc2626;
            text-decoration: none;
        }
        
        /* Responsive styles */
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border-radius: 0 !important;
            }
            
            .content-padding {
                padding: 20px !important;
            }
            
            .responsive-table {
                width: 100% !important;
            }
            
            .responsive-button {
                width: 100% !important;
                text-align: center !important;
            }
        }
    </style>
    
    {{-- Component-specific styles --}}
    @stack('styles')
</head>
<body>
    <div class="email-wrapper">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" style="padding: 20px 0;">
                    <div class="email-container">
                        {{-- Header Component --}}
                        @include('layouts.email.components.header')
                        
                        {{-- Main Content --}}
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                            <tr>
                                <td class="content-padding" style="padding: 30px;">
                                    @yield('content')
                                </td>
                            </tr>
                        </table>
                        
                        {{-- Footer Component --}}
                        @include('layouts.email.components.footer')
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>