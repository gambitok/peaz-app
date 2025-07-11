@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Restaurant Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">ID: {{ $restaurant['id'] }}</h5>
                <p class="card-text"><strong>File:</strong> {{ $restaurant['file'] }}</p>
                <p class="card-text"><strong>Link:</strong> {{ $restaurant['link'] }}</p>
                <p class="card-text"><strong>Status:</strong> {{ $restaurant['status'] ? 'Active' : 'Inactive' }}</p>
                <a href="{{ route('admin.restaurants.edit', $restaurant['id']) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.restaurants.index') }}" class="btn btn-secondary">Back to Restaurants</a>
            </div>
        </div>
    </div>
@endsection
