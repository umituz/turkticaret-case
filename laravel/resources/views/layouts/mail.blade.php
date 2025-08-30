<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Ecommerce')</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #ffffff;
            line-height: 1.6;
            color: #333333;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        .header {
            background-color: #ffffff;
            padding: 40px 40px 20px 40px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
            color: #000000;
        }
        
        .tagline {
            font-size: 13px;
            color: #666666;
            font-weight: 400;
        }
        
        .content {
            padding: 40px;
            color: #333333;
        }
        
        .content h1 {
            color: #000000;
            font-size: 24px;
            margin-bottom: 24px;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .content h2 {
            color: #000000;
            font-size: 18px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        
        .content p {
            margin-bottom: 16px;
            font-size: 16px;
            color: #333333;
            line-height: 1.5;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #000000;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            margin: 20px 0;
        }
        
        .btn:hover {
            background-color: #333333;
        }
        
        .highlight-box {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            padding: 24px;
            margin: 24px 0;
            border-radius: 4px;
        }
        
        .footer {
            background-color: #f9f9f9;
            color: #666666;
            padding: 30px 40px;
            text-align: center;
            font-size: 13px;
            border-top: 1px solid #e0e0e0;
        }
        
        .footer-brand {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #000000;
        }
        
        .copyright {
            color: #999999;
            font-size: 12px;
        }
        
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 30px 0;
        }
        
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
            }
            
            .header, .content, .footer {
                padding: 20px !important;
            }
            
            .logo {
                font-size: 28px;
            }
            
            .content h1 {
                font-size: 24px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Ecommerce</div>
            <div class="tagline">Your Trusted E-Commerce Platform</div>
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <div class="footer-brand">Ecommerce</div>
            <div class="copyright">
                <p>&copy; {{ date('Y') }} Ecommerce. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>