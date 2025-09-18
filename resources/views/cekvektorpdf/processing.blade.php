<div class="container mt-5 text-center">
    <div class="spinner-border text-primary mb-4" style="width: 4rem; height: 4rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <h3>Memproses file PDF dan mengecek vektor...</h3>
    <p>Mohon tunggu beberapa saat.</p>
</div>

<script>
    setTimeout(function() {
        window.location.href = "{{ route('pdf.cekvektor.result') }}";
    }, 5000);
</script>

