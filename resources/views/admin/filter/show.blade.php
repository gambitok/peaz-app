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
                 <p class="card-text"><strong>Dietaries:</strong>
                    {{ $filter->dietaries->pluck('name')->implode(', ') }}
                </p>
                 <p class="card-text"><strong>Cuisines:</strong>
                    {{ $filter->cuisines->pluck('name')->implode(', ') }}
                </p>
                <a href="{{ route('admin.filter.edit', $filter->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('admin.filter.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>Posts matching this filter:</h5>
                @if($posts->isEmpty())
                    <p>No posts found.</p>
                @else
                    <ul>
                        @foreach($posts as $post)
                            <li>
                                <strong>{{ $post->title ?? 'Post #'.$post->id }}</strong>
                                <br>
                                <span>Tags: {{ $post->tags->pluck('name')->implode(', ') }}</span><br>
                                <span>Dietaries: {{ $post->dietaries->pluck('name')->implode(', ') }}</span><br>
                                <span>Cuisines: {{ $post->cuisines->pluck('name')->implode(', ') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection
