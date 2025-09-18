function placeContentOnArtboards(targetDoc, inputPages) {
    writeLog("=== PLACING CONTENT ON ARTBOARDS (FIXED EMBED METHOD) ===");

    for (var i = 0; i < inputPages.length; i++) {
        var pagePath = inputPages[i];
        var rect = targetDoc.artboards[i].artboardRect;
        writeLog(">>> Processing page " + (i + 1) + "/" + inputPages.length + ": " + pagePath);
        writeLog("Artboard " + (i + 1) + " rect: " + rect.join(", "));

        targetDoc.artboards.setActiveArtboardIndex(i);
        writeLog("Active artboard index set to: " + i);

        try {
            if (i === 0) {
                // --- PAGE 1 (placedItem, raster) ---
                var placed = targetDoc.placedItems.add();
                placed.file = new File(pagePath);
                placed.embed();

                var bounds = placed.geometricBounds;
                writeLog("Bounds BEFORE center (page " + (i + 1) + "): " + bounds.join(", "));

                var dx = (rect[0] + rect[2]) / 2 - (bounds[0] + bounds[2]) / 2;
                var dy = (rect[1] + rect[3]) / 2 - (bounds[1] + bounds[3]) / 2;
                placed.translate(dx, dy);

                bounds = placed.geometricBounds;
                writeLog("Bounds AFTER center (page " + (i + 1) + "): " + bounds.join(", "));

                writeLog(">>> SUCCESS Placed page " + (i + 1) + " on artboard " + (i + 1));

            } else {
                // --- PAGE 2..n (vector via temp AI embed) ---
                var tempDoc = app.open(new File(pagePath));
                writeLog("Opened tempDoc, pageItems count: " + tempDoc.pageItems.length);

                // Save as temporary AI
                var tempAIPath = File(Folder.temp + "/temp_page_" + (i + 1) + ".ai");
                var saveOpts = new IllustratorSaveOptions();
                saveOpts.compatibility = Compatibility.ILLUSTRATOR17;
                tempDoc.saveAs(tempAIPath, saveOpts);
                writeLog("Saved temp AI for page " + (i + 1) + ": " + tempAIPath.fsName);

                // Close tempDoc
                tempDoc.close(SaveOptions.DONOTSAVECHANGES);
                writeLog("Closed tempDoc for page " + (i + 1));

                // Place the temp AI file
                var placedAI = targetDoc.placedItems.add();
                placedAI.file = tempAIPath;
                placedAI.embed();
                writeLog("Placed & embedded temp AI for page " + (i + 1));

                // Centering
                var bounds2 = placedAI.geometricBounds;
                var dx2 = (rect[0] + rect[2]) / 2 - (bounds2[0] + bounds2[2]) / 2;
                var dy2 = (rect[1] + rect[3]) / 2 - (bounds2[1] + bounds2[3]) / 2;
                placedAI.translate(dx2, dy2);

                bounds2 = placedAI.geometricBounds;
                writeLog("Bounds AFTER center (page " + (i + 1) + "): " + bounds2.join(", "));

                writeLog(">>> SUCCESS Placed page " + (i + 1) + " on artboard " + (i + 1));
            }
        } catch (e) {
            writeLog("ERROR on page " + (i + 1) + ": " + e);
        }
    }

    writeLog("=== CONTENT PLACEMENT COMPLETED (FIXED EMBED METHOD) ===");
}

