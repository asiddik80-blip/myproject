// ==================================================
// Fungsi untuk menempatkan PDF vektor ke artboard (versi debug)
// Script ini menghasilkan konten image di artboard 1 dengan posisi yang pas, 
// dan konten vektor di artboard 2 sampai n, tetapi dengan posisi yang belum pas
// ==================================================

function placeContentOnArtboards(targetDoc, inputPages) {
    writeLog("=== PLACING CONTENT ON ARTBOARDS (MIXED RASTER/VECTOR) ===");

    for (var i = 0; i < inputPages.length; i++) {
        var pagePath = inputPages[i];
        writeLog("Processing page " + (i + 1) + "/" + inputPages.length + ": " + pagePath);

        var abIndex = i;
        targetDoc.artboards.setActiveArtboardIndex(abIndex);
        var abRect = targetDoc.artboards[abIndex].artboardRect; 
        // [left, top, right, bottom]
        var abLeft   = abRect[0];
        var abTop    = abRect[1];
        var abRight  = abRect[2];
        var abBottom = abRect[3];
        var abWidth  = abRight - abLeft;
        var abHeight = abTop - abBottom;

        writeLog("Artboard " + (i + 1) + " rect: " + abRect.join(", "));

        try {
            if (i === 0) {
                // --- ARTBOARD 1: Image (raster) ---
                writeLog("Processing as IMAGE (artboard 1)");
                
                var placedItem = targetDoc.placedItems.add();
                placedItem.file = new File(pagePath);
                
                // --- ukur bounds sebelum centering ---
                var b = placedItem.geometricBounds; // [l, t, r, b]
                writeLog("Bounds BEFORE center (page " + (i + 1) + "): " + b.join(", "));

                var itemW = b[2] - b[0];
                var itemH = b[1] - b[3];

                // hitung translasi relatif ke artboard
                var dx = (abLeft + (abWidth - itemW) / 2) - b[0];
                var dy = (abTop - (abHeight - itemH) / 2) - b[1];

                placedItem.translate(dx, dy);

                // --- ukur bounds setelah centering ---
                var b2 = placedItem.geometricBounds;
                writeLog("Bounds AFTER center (page " + (i + 1) + "): " + b2.join(", "));
                
                writeLog(">>> SUCCESS Placed page 1 as IMAGE on artboard 1");

            } else {
                // --- ARTBOARD 2..n: Vector ---
                writeLog("Processing as VECTOR (artboard " + (i + 1) + ")");
                
                // Buka PDF sebagai dokumen sementara
                var tempDoc = app.open(new File(pagePath));
                writeLog("Opened tempDoc, pageItems count: " + tempDoc.pageItems.length);

                // Simpan sebagai AI sementara
                var tempAIPath = File(Folder.temp + "/temp_page_" + (i + 1) + ".ai");
                var saveOpts = new IllustratorSaveOptions();
                saveOpts.compatibility = Compatibility.ILLUSTRATOR17;
                tempDoc.saveAs(tempAIPath, saveOpts);
                writeLog("Saved temp AI for page " + (i + 1) + ": " + tempAIPath.fsName);

                tempDoc.close(SaveOptions.DONOTSAVECHANGES);
                writeLog("Closed tempDoc for page " + (i + 1));

                // Tempatkan dan embed AI sementara
                var placedAI = targetDoc.placedItems.add();
                placedAI.file = tempAIPath;
                placedAI.embed();
                writeLog("Placed & embedded temp AI for page " + (i + 1));

                // STEP 1: Ambil bounds awal
                var b = placedAI.geometricBounds;
                writeLog("Bounds RAW (page " + (i + 1) + "): " + b.join(", "));

                // STEP 2: Normalisasi ke origin (0,0)
                placedAI.translate(-b[0], -b[1]);
                b = placedAI.geometricBounds;
                writeLog("Bounds AFTER normalize (page " + (i + 1) + "): " + b.join(", "));

                // STEP 3: Hitung ukuran baru
                var itemW = b[2] - b[0];
                var itemH = b[1] - b[3];

                // STEP 4: Hitung translasi supaya center di artboard
                var dx = (abLeft + (abWidth - itemW) / 2) - b[0];
                var dy = (abTop - (abHeight - itemH) / 2) - b[1];

                placedAI.translate(dx, dy);

                // --- ukur bounds setelah centering ---
                var b2 = placedAI.geometricBounds;
                writeLog("Bounds AFTER center (page " + (i + 1) + "): " + b2.join(", "));

                writeLog(">>> SUCCESS Placed page " + (i + 1) + " as VECTOR on artboard " + (i + 1));
                
                // Hapus file sementara
                try {
                    tempAIPath.remove();
                    writeLog("Removed temp file: " + tempAIPath.fsName);
                } catch (e) {
                    writeLog("Warning: Could not remove temp file: " + e);
                }
            }
        } catch (e) {
            writeLog("ERROR on page " + (i + 1) + ": " + e);
        }
    }

    writeLog("=== CONTENT PLACEMENT COMPLETED (MIXED RASTER/VECTOR) ===");
}
