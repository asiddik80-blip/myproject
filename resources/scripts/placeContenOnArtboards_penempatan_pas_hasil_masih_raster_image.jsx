// ==================================================
// Fungsi untuk menempatkan PDF vektor ke artboard (versi debug)
// 03 SEPTEMBER 2025
// ==================================================
function placeContentOnArtboards(targetDoc, inputPages) {
    writeLog("=== PLACING CONTENT ON ARTBOARDS (SAFE BOUNDS) ===");

    for (var i = 0; i < inputPages.length; i++) {
        var pagePath = inputPages[i];
        writeLog(">>> Processing page " + (i + 1) + "/" + inputPages.length + ": " + pagePath);

        var artboardRect = targetDoc.artboards[i].artboardRect;
        writeLog("Artboard " + (i + 1) + " rect: " + artboardRect.join(", "));

        targetDoc.artboards.setActiveArtboardIndex(i);
        writeLog("Active artboard index set to: " + i);

        // Tempatkan PDF sebagai placedItem
        var placedItem = targetDoc.placedItems.add();
        placedItem.file = new File(pagePath);

        // Tunggu sebentar agar Illustrator render placedItem
        app.redraw();

        // --- Ambil bounds dengan aman ---
        var bounds = null;
        try {
            bounds = placedItem.visibleBounds;
            if (bounds) {
                writeLog("Bounds BEFORE center (page " + (i + 1) + "): " + bounds.join(", "));
            } else {
                writeLog("Bounds BEFORE center (page " + (i + 1) + "): [undefined]");
            }
        } catch (e) {
            writeLog("Bounds BEFORE center (page " + (i + 1) + "): ERROR reading bounds -> " + e);
        }

        if (bounds) {
            var contentW = bounds[2] - bounds[0];
            var contentH = bounds[1] - bounds[3];
            var artW = artboardRect[2] - artboardRect[0];
            var artH = artboardRect[1] - artboardRect[3];

            writeLog("Content size (W x H): " + contentW + " x " + contentH);
            writeLog("Artboard size (W x H): " + artW + " x " + artH);

            // Cek orientasi
            if ((contentW > contentH && artW < artH) || (contentW < contentH && artW > artH)) {
                writeLog(">>> Orientation mismatch detected, rotating -90 deg...");
                placedItem.rotate(-90);
                app.redraw();
                bounds = placedItem.visibleBounds;
                writeLog("Bounds AFTER rotate (page " + (i + 1) + "): " + bounds.join(", "));
                contentW = bounds[2] - bounds[0];
                contentH = bounds[1] - bounds[3];
                writeLog("Content size AFTER rotate (W x H): " + contentW + " x " + contentH);
            }

            // Hitung posisi tengah artboard
            var centerX = (artboardRect[0] + artboardRect[2]) / 2;
            var centerY = (artboardRect[1] + artboardRect[3]) / 2;
            var contentCenterX = (bounds[0] + bounds[2]) / 2;
            var contentCenterY = (bounds[1] + bounds[3]) / 2;
            var dx = centerX - contentCenterX;
            var dy = centerY - contentCenterY;

            // Geser konten ke tengah artboard
            placedItem.translate(dx, dy);

            app.redraw();

            // Update bounds setelah dipindahkan
            var newBounds = placedItem.visibleBounds;
            writeLog("Bounds AFTER center (page " + (i + 1) + "): " + newBounds.join(", "));
            writeLog("dx, dy used for translate (page " + (i + 1) + "): dx=" + dx + ", dy=" + dy);

            var insideX = newBounds[0] >= artboardRect[0] && newBounds[2] <= artboardRect[2];
            var insideY = newBounds[1] <= artboardRect[1] && newBounds[3] >= artboardRect[3];
            writeLog("Inside artboard check: X=" + insideX + ", Y=" + insideY);
        }

        writeLog(">>> SUCCESS Placed page " + (i + 1) + " on artboard " + (i + 1));
    }

    writeLog("=== CONTENT PLACEMENT COMPLETED (SAFE BOUNDS) ===");
}
