@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <h1 class="mb-4">Create Billboard</h1>
        <form action="{{ route('admin.billboards.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="caption">Caption:</label>
                <input type="text" class="form-control" id="caption" name="caption">
            </div>
            <div class="form-group">
                <label for="file">File:</label>
                <input type="text" class="form-control" id="file" name="file">
            </div>
            <div class="form-group">
                <label for="link">Link:</label>
                <input type="text" class="form-control" id="link" name="link">
            </div>
            <div class="form-group">
                <label for="tag_id">Tag:</label>
                <select class="form-control" id="tag_id" name="tag_id">
                    <option value="">Select a tag</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" id="verified" name="verified">
                <label for="verified">Verified</label>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" id="status" name="status" style="display: inline;">
                <label for="status">Status</label>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back to Billboards</a>
        </form>
    </div>
@endsection
