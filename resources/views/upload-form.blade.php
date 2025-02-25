<form action="{{ route('pdf.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="file">Upload PDF:</label>
    <input type="file" name="file" accept=".pdf" required>
    <button type="submit">Upload</button>
</form>
