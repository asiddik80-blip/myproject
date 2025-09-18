<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ekstraksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Tampilkan hasil ekstrak -->
@if(isset($jsonOutput))
                <div class="container">
                    <h3>Hasil Ekstraksi PDF</h3>
                    <div class="alert alert-success">
                        <pre>{{ json_encode($jsonOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                    <h5><a href="{{ url('syauqiepdf') }}" class="btn btn-primary">Kembali ke halaman upload file PDF</a></h5>
                </div>
            @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
