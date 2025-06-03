@if(session('success'))
    <p>{{ session('success') }}</p>
    <img src="{{ session('file_url') }}" alt="Uploaded Image" style="max-width: 300px; height: auto;">
@endif

@if($imageUrl)
    <p>Current image:</p>
    <img src="{{ $imageUrl }}" alt="Current Image" style="max-width: 300px; height: auto;">
@endif

<form action="{{ route('admin.upload.file') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>

@if($errors->any())
    <ul>
        @foreach($errors->all() as $error)
            <li style="color:red;">{{ $error }}</li>
        @endforeach
    </ul>
@endif
