//@target illustrator
/*
    Script: PDF ke AI Multi-Artboard + Vektorisasi Stabil
    Catatan:
    - Semua halaman PDF masuk 1 file AI
    - Artboard = jumlah halaman PDF
    - Vektorisasi hanya untuk RasterItem / PlacedItem
    - Tidak memunculkan dialog
    - Illustrator ditutup otomatis
*/

function main() {
    var files = {{FILES_ARRAY}}; // Format: [{input:"path.pdf", output:"path.ai"}, ...]

    for (var f = 0; f < files.length; f++) {
        var inputFile = new File(files[f].input);
        var outputFile = new File(files[f].output);

        if (!inputFile.exists) {
            $.writeln("File PDF tidak ditemukan: " + inputFile.fsName);
            continue;
        }

        var oldLevel = app.userInteractionLevel;
        try { app.userInteractionLevel = UserInteractionLevel.DONTDISPLAYALERTS; } catch (_) {}

        try {
            // Hitung total halaman PDF
            var totalPages = getPDFPageCount(inputFile);

            var doc = null;

            for (var i = 0; i < totalPages; i++) {
                var options = new PDFOpenOptions();
                options.pageToOpen = i + 1;
                options.preserveEditability = true;

                if (i === 0) {
                    // Halaman pertama -> dokumen utama
                    doc = app.open(inputFile, options);
                } else {
                    // Halaman berikutnya -> buka sementara
                    var tempDoc = app.open(inputFile, options);

                    // Buat artboard baru di dokumen utama sesuai ukuran tempDoc
                    var abRect = tempDoc.artboards[0].artboardRect;
                    doc.artboards.add(abRect);

                    // Pindahkan semua objek dari tempDoc ke artboard terakhir
                    tempDoc.selection = null;
                    tempDoc.pageItems.everyItem().selected = true;
                    app.copy();
                    tempDoc.close(SaveOptions.DONOTSAVECHANGES);

                    doc.artboards.setActiveArtboardIndex(doc.artboards.length - 1);
                    app.paste();
                }
            }

            // Vektorisasi aman
            for (var j = 0; j < doc.pageItems.length; j++) {
                var item = doc.pageItems[j];
                if (item.typename === "RasterItem" || item.typename === "PlacedItem") {
                    try { item.trace(); } catch (e) { $.writeln("Gagal vektorisasi item #" + j + ": " + e); }
                }
            }

            // Simpan file AI
            var saveOptions = new IllustratorSaveOptions();
            saveOptions.compatibility = Compatibility.ILLUSTRATOR17;
            saveOptions.pdfCompatible = true;
            saveOptions.embedICCProfile = true;
            doc.saveAs(outputFile, saveOptions);
            doc.close(SaveOptions.DONOTSAVECHANGES);

            $.writeln("Selesai: " + outputFile.fsName);

        } catch (e) {
            $.writeln("Terjadi error pada file: " + inputFile.fsName + " - " + e);
        }

        // Pulihkan level interaksi
        try { app.userInteractionLevel = oldLevel; } catch (_) {}
    }

    // Tutup Illustrator
    try { app.quit(); } catch (_) {}
}

// Fungsi untuk mendapatkan jumlah halaman PDF dengan aman
function getPDFPageCount(file) {
    try {
        var tempDoc = app.open(file);
        var count = tempDoc.pages ? tempDoc.pages.length : 1;
        tempDoc.close(SaveOptions.DONOTSAVECHANGES);
        return count;
    } catch (e) {
        return 1; // fallback minimal 1 halaman
    }
}

main();
