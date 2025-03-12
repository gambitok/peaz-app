@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="mb-4">Create Billboard</h1>
                        <!-- Display error messages -->
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.billboards.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mt-2">
                                <label class="control-label" for="link">Link:</label>
                                <input type="text" class="form-control" id="link" name="link" placeholder="Link">
                            </div>
                            <div class="form-group mt-2">
                                <label for="file" class="col-form-label">File</label>
                                <div>
                                    <input type="file" name="file" id="file" class="form-control">
                                    <p>No file uploaded</p>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="status" name="status" style="display: inline;">
                                <label class="form-check-label" for="status">Status</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Create</button>
                            <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back to Billboards</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
