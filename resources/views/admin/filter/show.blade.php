@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">ID: {{ $filter->id }}</h5>
                <p class="card-text"><strong>Name:</strong> {{ $filter->name }}</p>
                <p class="card-text"><strong>Tags:</strong>
                    {{ $filter->tags->pluck('name')->implode(', ') }}
                </p>
                <a href="{{ route('admin.filter.edit', $filter->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.filter.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
@endsection
