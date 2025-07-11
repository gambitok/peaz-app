@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Tag Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">ID: {{ $tag['id'] }}</h5>
                <p class="card-text"><strong>Name:</strong> {{ $tag['name'] }}</p>
                <a href="{{ route('admin.tag.edit', $tag['id']) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.tag.index') }}" class="btn btn-secondary">Back to tag</a>
            </div>
        </div>
    </div>
@endsection
