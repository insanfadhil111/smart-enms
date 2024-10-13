@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

<!-- @section('content') -->
@include('layouts.navbars.auth.topnav', ['title' => 'New Dashboard'])

<div class="sidenav">
    <ul>
        <li>
            <a href="{{ route('buildings.newDashboard', $building->id) }}">Dashboard</a>
        </li>
    </ul>
</div>

<div class="container-fluid py-4">
    <h1>Welcome to the {{ $building->name }} New Dashboard</h1>
    <!-- Add more content here as needed -->
</div>
@endsection