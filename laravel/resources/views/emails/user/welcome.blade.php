@extends('layouts.mail')

@section('title', 'Welcome to TurkTicaret')

@section('content')
    <h1>Welcome to TurkTicaret, {{ $user->name }}!</h1>
    
    <p>Thank you for joining our marketplace. You can now browse thousands of products and start shopping.</p>
    
    <div class="divider"></div>
    
    <p style="text-align: center; color: #6c757d;">
        Questions? Contact our support team anytime.
    </p>
    
    <p style="text-align: center; margin-top: 20px;">
        <strong>Happy Shopping!</strong><br>
        The TurkTicaret Team
    </p>
@endsection