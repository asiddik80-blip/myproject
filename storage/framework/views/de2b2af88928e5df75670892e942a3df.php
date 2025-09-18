

<?php $__env->startSection('content'); ?>
<?php
    $secondsPerPage = 15;
    $bufferSeconds = 30;
    $totalPages = array_sum($pageCounts ?? []);
    $totalFiles = count($pageCounts);
    $estimatedSeconds = ($totalPages * $secondsPerPage) + $bufferSeconds;
    $estimatedMinutes = ceil($estimatedSeconds / 60);
?>

<div class="container text-center mt-5">
    <h3 class="mb-3">Convert PDF to Illustrator</h3>
    
    <!-- Estimasi waktu -->
    <div class="alert alert-info mx-auto" style="max-width: 600px;">
        <strong>Estimation <?php echo e($estimatedMinutes); ?> minutes</strong>
        for  <strong><?php echo e($totalPages); ?> pages</strong>
        from  <strong><?php echo e($totalFiles); ?> PDF files</strong>.
    </div>

    <!-- Tampilkan nama file PDF bergantian -->
    <div class="mt-4">
        <h5 id="currentFileName" class="text-primary fw-bold"></h5>
    </div>


    <!-- Progress bar -->
    <div class="progress mx-auto my-4" style="height: 30px; max-width: 600px;">
        <div id="progressBar" class="progress-bar progress-bar-striped bg-success"
             role="progressbar" style="width: 0%; transition: width 1s;">
            0%
        </div>
    </div>

    
    <div class="mt-4">
        <h5 class="mb-2 text-muted">PDF Split Path (JSON Preview)</h5>
        <div class="bg-light border rounded p-3" style="max-height: 400px; overflow-y: auto; font-size: 0.9rem;">
            <pre><?php echo e(json_encode($allSplitPdfPaths, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
        </div>
    </div>

    

    <!-- Tombol manual -->
    <!-- <div class="mt-4">
        <p class="text-muted">Jika halaman tidak beralih otomatis:</p>
        <a href="<?php echo e(route('ocrpdf.result')); ?>" class="btn btn-outline-primary">
            Lihat Hasil Manual
        </a>
    </div> -->
</div>

<script>
    const estimatedSeconds = <?php echo e($estimatedSeconds); ?>;
    let currentSecond = 0;
    const progressBar = document.getElementById('progressBar');

    const interval = setInterval(() => {
        currentSecond++;

        let progress = Math.floor((currentSecond / estimatedSeconds) * 100);
        if (progress > 100) progress = 100;

        progressBar.style.width = progress + '%';
        progressBar.innerText = progress + '%';

        if (progress >= 100) {
            clearInterval(interval);
            window.location.href = "<?php echo e(route('ocrpdf.result')); ?>";
        }
    }, 1000);
</script>

<script>
    // Kirim permintaan AJAX ke server untuk mulai proses berat
    fetch("<?php echo e(route('ocrpdf.processHeavy')); ?>", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(data => {
        console.log('Proses selesai:', data);
        window.location.href = "<?php echo e(route('ocrpdf.result')); ?>";
    });
</script>

<script>
    const fileNames = <?php echo json_encode(array_keys($pageCounts), 15, 512) ?>;
    const fileLabel = document.getElementById('currentFileName');

    let currentIndex = 0;

    setInterval(() => {
        if (fileNames.length === 0) return;
        fileLabel.textContent = fileNames[currentIndex];
        currentIndex = (currentIndex + 1) % fileNames.length;
    }, 500);
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app-layer-dua', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myproject\resources\views/ocrpdf/processing.blade.php ENDPATH**/ ?>