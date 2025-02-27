@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Cuisine Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">ID: {{ $cuisine['id'] }}</h5>
                <p class="card-text"><strong>Name:</strong> {{ $cuisine['name'] }}</p>
                <a href="{{ route('admin.cuisine.edit', $cuisine['id']) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.cuisine.index') }}" class="btn btn-secondary">Back to Cuisines</a>
            </div>
        </div>
    </div>
@endsection
