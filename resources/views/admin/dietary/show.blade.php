@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Dietary Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">ID: {{ $dietary['id'] }}</h5>
                <p class="card-text"><strong>Name:</strong> {{ $dietary['name'] }}</p>
                <a href="{{ route('admin.dietary.edit', $dietary['id']) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.dietary.index') }}" class="btn btn-secondary">Back to dietary</a>
            </div>
        </div>
    </div>
@endsection
