function placeContentOnArtboards(targetDoc, inputPages) {
    writeLog("=== PLACING CONTENT ON ARTBOARDS ===");

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

        // place PDF page
        var placedItem = targetDoc.placedItems.add();
        placedItem.file = new File(pagePath);

        // --- rotate langsung untuk paksa landscape ---
        placedItem.rotate(-90);
        app.redraw();

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
    }
}
