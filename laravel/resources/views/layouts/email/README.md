# {{ config('app.name', 'App') }} Email Layout System

## Overview
This modular email layout system provides a consistent, professional design for all email communications. It's built with modern email client compatibility and responsive design in mind.

## Structure

```
layouts/email/
├── base.blade.php              # Main email layout
├── components/
│   ├── header.blade.php        # Email header with branding
│   ├── footer.blade.php        # Email footer with links
│   ├── button.blade.php        # Reusable button component
│   └── info-box.blade.php      # Information display boxes
└── README.md                   # This documentation
```

## Usage

### Basic Email Template
```blade
@extends('layouts.email.base')

@section('title', 'Your Email Title')

@section('content')
    @php
        $headerSubtitle = 'Custom header subtitle';
        $isAutomatedEmail = true;
        $showQuickLinks = true;
    @endphp

    <h1>Your Email Content</h1>
    <p>Your email content goes here...</p>
@endsection
```

### Components

#### Button Component
```blade
@include('layouts.email.components.button', [
    'url' => 'https://example.com',
    'text' => 'Click Me',
    'color' => 'primary', // primary, success, warning, danger, dark, info
    'align' => 'center'   // left, center, right
])
```

#### Info Box Component
```blade
@include('layouts.email.components.info-box', [
    'title' => 'Order Details',
    'style' => 'default', // default, highlight, warning, success, danger
    'items' => [
        ['label' => 'Order Number', 'value' => '#12345'],
        ['label' => 'Total', 'value' => '$99.99'],
    ]
])
```


### Header Configuration
Configure the header by setting these PHP variables before your content:

```php
@php
    $headerSubtitle = 'Custom subtitle text';    // Optional custom subtitle
    $isAutomatedEmail = true;                    // Show "automated email" notice
    $showQuickLinks = true;                      // Show navigation links in footer
    $showUnsubscribe = true;                     // Show unsubscribe link
    $showAddress = true;                         // Show company address
    $showSocialLinks = true;                     // Show social media links
@endphp
```

## Features

### Email Client Compatibility
- Outlook (all versions)
- Gmail (web and mobile)
- Apple Mail
- Thunderbird
- Mobile clients (iOS Mail, Android Gmail)

### Responsive Design
- Mobile-first approach
- Optimized for screens 320px and up
- Touch-friendly buttons and links

### Professional Styling
- Dynamic brand colors based on app configuration
- Clean, modern typography
- Consistent spacing and alignment

### Accessibility
- Semantic HTML structure
- Proper color contrast ratios
- Alt text for images (when used)
- Screen reader friendly

## Color Scheme

### Status Colors
- **Confirmed**: Blue (`#1e40af`)
- **Processing**: Orange (`#d97706`)
- **Shipped**: Purple (`#5b21b6`)
- **Delivered**: Green (`#065f46`)
- **Cancelled**: Red (`#dc2626`)
- **Refunded**: Gray (`#374151`)

### Button Colors
- **Primary**: Red gradient (`#dc2626` → `#ef4444`)
- **Success**: Green gradient (`#059669` → `#10b981`)
- **Warning**: Orange gradient (`#d97706` → `#f59e0b`)
- **Danger**: Red gradient (`#dc2626` → `#ef4444`)
- **Dark**: Dark gray gradient (`#1f2937` → `#374151`)
- **Info**: Blue gradient (`#2563eb` → `#3b82f6`)

## Best Practices

1. **Keep it Simple**: Don't overload emails with too many components
2. **Test Thoroughly**: Always test in multiple email clients
3. **Mobile First**: Design for mobile, then enhance for desktop
4. **Clear CTAs**: Use prominent buttons for important actions
5. **Consistent Branding**: Use config('app.name') for dynamic branding
6. **Dynamic Content**: All brand references use Laravel config system

## Migration from Old Layout

Old emails using `@extends('layouts.mail')` should be updated to use `@extends('layouts.email.base')` and leverage the new component system for better consistency and maintainability.