@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Edit Billboard</h1>
        <form action="{{ route('admin.billboards.update', $billboard['id']) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $billboard['title'] }}" required>
            </div>
            <div class="form-group">
                <label for="caption">Caption:</label>
                <input type="text" class="form-control" id="caption" name="caption" value="{{ $billboard['caption'] }}">
            </div>
            <div class="form-group">
                <label for="file">File:</label>
                <input type="text" class="form-control" id="file" name="file" value="{{ $billboard['file'] }}">
            </div>
            <div class="form-group">
                <label for="link">Link:</label>
                <input type="text" class="form-control" id="link" name="link" value="{{ $billboard['link'] }}">
            </div>
            <div class="form-group">
                <label for="tag_id">Tag ID:</label>
                <input type="number" class="form-control" id="tag_id" name="tag_id" value="{{ $billboard['tag']['id'] }}">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="verified" name="verified" {{ $billboard['verified'] ? 'checked' : '' }}>
                <label class="form-check-label" for="verified">Verified</label>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="status" name="status" {{ $billboard['status'] ? 'checked' : '' }}>
                <label class="form-check-label" for="status">Status</label>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back to Billboards</a>
        </form>
    </div>
@endsection

