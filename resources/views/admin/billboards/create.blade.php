@extends('layouts.master')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-sm-12">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-4">
                        <h1 class="mb-4 text-center">Create Billboard</h1>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.billboards.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <label for="link" class="form-label">Link</label>
                                <input type="text" class="form-control" id="link" name="link" placeholder="Enter link">
                            </div>

                            @foreach(['file' => 'File', 'logo_file' => 'Logo File', 'horizontal_file' => 'Horizontal File', 'video_file' => 'Video File'] as $field => $label)
                                <div class="mb-3">
                                    <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                                    <input type="file" name="{{ $field }}" id="{{ $field }}" class="form-control">
                                </div>
                            @endforeach

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="status" name="status">
                                <label class="form-check-label" for="status">Active</label>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                <button type="submit" class="btn btn-primary">Create</button>
                                <a href="{{ route('admin.billboards.index') }}" class="btn btn-secondary">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
