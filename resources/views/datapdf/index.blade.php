@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upload Berkas PDF') }}
            </h2>
            <label for="pdf_file" class="mb-4">Silakan pilih berkas PDF yang akan diupload</label>

            <form action="{{ route('uploadpdf') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group input-group col-xs-12">
                    
                    <input type="file" name="pdf_file" class="form-control file-upload-info" accept="application/pdf" required placeholder="Upload PDF">
                    
                    <span class="input-group-append">
                            <button class="file-upload-browse btn btn-primary" type="submit">Upload</button>
                        </span>
                </div>
            </form>
            <div class="container">
            <table class="table table-dark">
                      <thead>
                        <tr>
                          <th> NO </th>
                          <th> NAMA FILE </th>
                          <th> KONTEN </th>
                        </tr>
                      </thead>
                      <tbody>
                                    @forelse ($pdfData as $indexpdf => $itempdf)
                                        <tr>
                                            <td>{{ $indexpdf + 1 }}</td>
                                            <td>{{ $itempdf->filename }}</td>
                                            <td>{{ $itempdf->content }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">No results</td>
                                        </tr>
                                    @endforelse
                      </tbody>
                    </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upload Berkas PDF - Extract Table') }}
            </h2>
            <p class="mb-3">Silakan pilih berkas PDF yang akan diupload:</p>

            <form action="{{ url('/extract-table') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="pdf" class="form-control" accept="application/pdf" required>
                    <button class="btn btn-warning" type="submit">Extract</button>
                </div>
            </form>
        </div>
    </div>

@endsection
