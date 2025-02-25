<h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload PDF') }}
        </h2>

        <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <form action="{{ route('uploadpdf') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="pdf_file" accept="application/pdf" required>
            <button type="submit">Upload PDF</button>
        </form>
            </div>
        </div>
        
    </div>