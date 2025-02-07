@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">{{ $billboard['title'] }}</h1>
        <div class="card">
            <div class="card-body">
                <p class="card-text"><strong>Caption:</strong> {{ $billboard['caption'] }}</p>
                <p class="card-text"><strong>File:</strong> {{ $billboard['file'] }}</p>
                <p class="card-text"><strong>Link:</strong> <a href="{{ $billboard['link'] }}" target="_blank">{{ $billboard['link'] }}</a></p>
                <p class="card-text"><strong>Verified:</strong> {{ $billboard['verified'] ? 'Yes' : 'No' }}</p>
                <p class="card-text"><strong>Status:</strong> {{ $billboard['status'] ? 'Active' : 'Inactive' }}</p>
                <a href="{{ route('admin.billboards.edit', $billboard['id']) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back to Billboards</a>
            </div>
        </div>
    </div>
@endsection
