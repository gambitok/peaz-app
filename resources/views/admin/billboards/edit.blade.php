@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4">Edit Billboard</h1>
                        <form action="{{ route('admin.billboards.update', $billboard['id']) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="title">Title:</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ $billboard['title'] }}" required>
                            </div>
                            <div class="form-group mt-2">
                                <label for="caption">Caption:</label>
                                <input type="text" class="form-control" id="caption" name="caption" value="{{ $billboard['caption'] }}">
                            </div>
                            <div class="form-group mt-2">
                                <label for="link">Link:</label>
                                <input type="text" class="form-control" id="link" name="link" value="{{ $billboard['link'] }}">
                            </div>
                            <div class="form-group mt-2">
                                <label for="tag_id">Tag:</label>
                                <select class="form-control" id="tag_id" name="tag_id">
                                    <option value="">Select a tag</option>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->id }}" @if($billboard['tag_id'] == $tag->id) selected @endif>{{ $tag->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mt-2">
                                <label for="file" class="col-form-label">File</label>
                                <div>
                                    <input type="file" name="file" id="file" class="form-control">
                                </div>
                            </div>
                            <div class="form-check mb-3 mt-2">
                                <input type="checkbox" class="form-check-input" id="verified" name="verified" @if($billboard['verified']) checked @endif>
                                <label class="form-check-label" for="verified">Verified</label>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="status" name="status" @if($billboard['status']) checked @endif>
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back to Billboards</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
