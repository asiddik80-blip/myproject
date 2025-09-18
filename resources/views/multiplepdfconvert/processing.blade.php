<html>
    <div class="container mt-5 text-center">
        <div class="spinner-border text-primary mb-4" style="width: 4rem; height: 4rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <h3>Sedang memproses file PDF...</h3>
        <p>Mohon tunggu, proses konversi sedang berjalan melalui Adobe Illustrator.</p>
        <p>Setelah selesai, Anda akan diarahkan secara otomatis ke halaman hasil.</p>
    </div>

</html>
<script>
    // Redirect otomatis setelah 5 detik (bisa diubah sesuai kebutuhan)
    setTimeout(function() {
        window.location.href = "{{ route('pdf.multiple.result') }}";
    }, 5000); // 5000ms = 5 detik
</script>

