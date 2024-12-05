<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app') <!-- Extends the main layout file -->

@section('content') <!-- Section that will be inserted into the 'content' section of the layout -->
    <div class="container">
        <h1>Welcome to Your Dashboard, {{ auth()->user()->name }}!</h1>

        <!-- Display user information -->
        <div class="user-info">
            <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>Joined:</strong> {{ auth()->user()->created_at->format('M d, Y') }}</p>
        </div>

        <hr>

        <!-- Links to Profile Management and other sections -->
        <div class="dashboard-links">
            <h3>Manage Your Account</h3>
            <ul>
                <li><a href="{{ route('profile.edit') }}">Edit Profile</a></li>
                <li><a href="{{ route('profile.destroy') }}" onclick="event.preventDefault(); document.getElementById('delete-account-form').submit();">Delete Account</a></li>
            </ul>
        </div>

        <!-- Add an account deletion form (hidden) -->
        <form id="delete-account-form" action="{{ route('profile.destroy') }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection
