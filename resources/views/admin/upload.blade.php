<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
</head>
<body>
<h1>Upload File to AWS S3</h1>

@if(session('success'))
    <p>{{ session('success') }}</p>
    <p>File URL: <a href="{{ session('file_url') }}" target="_blank">{{ session('file_url') }}</a></p>
@endif

@if(!empty($imageUrl))
    <p>Here is your image:</p>
    <img src="{{ $imageUrl }}" alt="Uploaded Image" style="max-width: 100%; height: auto;">
@else
    <p>No image available.</p>
@endif

<form action="{{ route('admin.file.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="file">Choose a file:</label>
    <input type="file" name="file" id="file" required>
    <button type="submit">Upload</button>
</form>
</body>
</html>
