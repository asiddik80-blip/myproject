//@target illustrator

// Data dari Laravel
// Format: files = [ { input: ["split/page1.pdf", "split/page2.pdf"], output: "path/to/file.ai", totalPages: 5, width: 841.68, height: 594.72 }, ... ]
var files = {{FILES_ARRAY}}; // tetap ada untuk kompatibilitas
var all_split_pdf_paths = [
    {{ALL_SPLIT_PDF_PATHS}}
];

//LOG AWAL
// Debug awal
$.writeln("=== DEBUG START ===");
$.writeln("Files JSON length: " + files.length);

for (var i = 0; i < files.length; i++) {
    $.writeln("Original Input: " + files[i].originalInput);
    $.writeln("Output Path: " + files[i].output);
    $.writeln("HalamanPDF count: " + files[i].halamanPDF.length);
    for (var j = 0; j < files[i].halamanPDF.length; j++) {
        $.writeln(" - Page " + (j+1) + ": " + files[i].halamanPDF[j]);
    }
}

$.writeln("All split paths count: " + all_split_pdf_paths.length);
for (var k = 0; k < all_split_pdf_paths.length; k++) {
    $.writeln("Split path " + (k+1) + ": " + all_split_pdf_paths[k]);
}
$.writeln("=== DEBUG END ===");

// ==================================================
// GLOBAL STATE
// ==================================================
var logFile = null;
var oldLevel = app.userInteractionLevel;

// ==================================================
// Helper Functions
// ==================================================
function initLog() {
    try {
        logFile = new File("C:/xampp/htdocs/livedubai/storage/logs/jsx_debug_log.txt");
        logFile.open("w");
        logFile.writeln("=== JSX DEBUG LOG - " + new Date() + " ===");
    } catch (e) {
        $.writeln("Warning: Could not create log file: " + e);
    }
}

function writeLog(message) {
    $.writeln(message);
    try {
        if (logFile) {
            logFile.writeln(new Date() + " - " + message);
            logFile.flush();
        }
    } catch (e) {
        $.writeln("Error writing to log file: " + e);
    }
}

function setNoInteraction() {
    try {
        app.userInteractionLevel = UserInteractionLevel.DONTDISPLAYALERTS;
    } catch (e) {
        writeLog("Warning: Could not set user interaction level: " + e);
    }
}

function restoreInteraction() {
    try {
        app.userInteractionLevel = oldLevel;
    } catch (e) {
        writeLog("ERROR: Could not restore interaction level: " + e);
    }
}

// ==================================================
// Validasi semua PDF di all_split_pdf_paths
// ==================================================
function validateAllSplitPaths() {
    writeLog("=== PATH VALIDATION START ===");

    var validFiles = [];
    var missingFiles = [];

    for (var i = 0; i < all_split_pdf_paths.length; i++) {
        var f = new File(all_split_pdf_paths[i]);
        if (f.exists) validFiles.push(f);
        else missingFiles.push(all_split_pdf_paths[i]);
    }

    writeLog("Total files to process: " + all_split_pdf_paths.length);
    writeLog("Valid files: " + validFiles.length);
    writeLog("Missing files: " + missingFiles.length);

    if (missingFiles.length > 0) {
        writeLog("ERROR: Some input files are missing!");
        for (var i = 0; i < missingFiles.length; i++) {
            writeLog("  - Missing: " + missingFiles[i]);
        }
    }

    writeLog("=== PATH VALIDATION END ===");

    return { validFiles: validFiles, missingFiles: missingFiles };
}


//Fungsi auto center dan rotasi
function placeAndCenter(content, abBounds, width, height) {
    // --- Artboard bounds ---
    var abLeft   = abBounds[0];
    var abTop    = abBounds[1];
    var abRight  = abBounds[2];
    var abBottom = abBounds[3];
    var abCenterX = (abLeft + abRight) / 2;
    var abCenterY = (abTop + abBottom) / 2;

    // --- Content bounds ---
    var gb = content.geometricBounds;
    var cLeft   = gb[0];
    var cTop    = gb[1];
    var cRight  = gb[2];
    var cBottom = gb[3];
    var cCenterX = (cLeft + cRight) / 2;
    var cCenterY = (cTop + cBottom) / 2;
    var cWidth  = cRight - cLeft;
    var cHeight = cTop - cBottom;

    // --- Orientation check ---
    var contentLandscape = (cWidth >= cHeight);
    var artboardLandscape = ((abRight - abLeft) >= (abTop - abBottom));

    if (contentLandscape !== artboardLandscape) {
        // Rotasi di sekitar pusat konten
        content.rotate(90, true, true, true, true, Transformation.CENTER);

        // Recalculate bounds after rotation
        gb = content.geometricBounds;
        cLeft   = gb[0];
        cTop    = gb[1];
        cRight  = gb[2];
        cBottom = gb[3];
        cCenterX = (cLeft + cRight) / 2;
        cCenterY = (cTop + cBottom) / 2;
    }

    // --- Translate supaya center ---
    var dx = abCenterX - cCenterX;
    var dy = abCenterY - cCenterY;
    content.translate(dx, dy);
}


// ==================================================
// Fungsi untuk menempatkan PDF vektor ke artboard (versi debug)
// ==================================================

function placeContentOnArtboards(targetDoc, inputPages) {
    writeLog("=== PLACING CONTENT ON ARTBOARDS (FIXED EMBED + NORMALIZE) ===");

    for (var i = 0; i < inputPages.length; i++) {
        var pagePath = inputPages[i];
        var rect = targetDoc.artboards[i].artboardRect;
        writeLog(">>> Processing page " + (i + 1) + "/" + inputPages.length + ": " + pagePath);
        writeLog("Artboard " + (i + 1) + " rect: " + rect.join(", "));

        targetDoc.artboards.setActiveArtboardIndex(i);

        // Variabel artboard detail
        var abLeft   = rect[0];
        var abTop    = rect[1];
        var abRight  = rect[2];
        var abBottom = rect[3];
        var abWidth  = abRight - abLeft;
        var abHeight = abTop - abBottom;

        try {
            if (i === 0) {
                // --- PAGE 1 (placedItem, raster) ---
                var placed = targetDoc.placedItems.add();
                placed.file = new File(pagePath);
                placed.embed();

                var b = placed.geometricBounds; 
                writeLog("Bounds BEFORE center (page 1): " + b.join(", "));

                var itemW = b[2] - b[0];
                var itemH = b[1] - b[3];

                var dx = (abLeft + (abWidth - itemW) / 2) - b[0];
                var dy = (abTop - (abHeight - itemH) / 2) - b[1];

                placed.translate(dx, dy);

                var b2 = placed.geometricBounds;
                writeLog("Bounds AFTER center (page 1): " + b2.join(", "));
                writeLog(">>> SUCCESS Placed page 1 on artboard 1");

            } else {
                // --- PAGE 2..n (vector via temp AI embed) ---
                var tempDoc = app.open(new File(pagePath));
                writeLog("Opened tempDoc, pageItems count: " + tempDoc.pageItems.length);

                var tempAIPath = File(Folder.temp + "/temp_page_" + (i + 1) + ".ai");
                var saveOpts = new IllustratorSaveOptions();
                saveOpts.compatibility = Compatibility.ILLUSTRATOR17;
                tempDoc.saveAs(tempAIPath, saveOpts);
                writeLog("Saved temp AI for page " + (i + 1) + ": " + tempAIPath.fsName);

                tempDoc.close(SaveOptions.DONOTSAVECHANGES);
                writeLog("Closed tempDoc for page " + (i + 1));

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

                var b2 = placedAI.geometricBounds;
                writeLog("Bounds AFTER center (page " + (i + 1) + "): " + b2.join(", "));

                writeLog(">>> SUCCESS Placed page " + (i + 1) + " on artboard " + (i + 1));
            }
        } catch (e) {
            writeLog("ERROR on page " + (i + 1) + ": " + e);
        }
    }

    writeLog("=== CONTENT PLACEMENT COMPLETED (FIXED EMBED + NORMALIZE) ===");
}

// ==================================================
// Proses utama semua PDF
// ==================================================
function processAllSplitPDFs() {
    // Validasi semua path PDF
    var validation = validateAllSplitPaths();
    if (validation.missingFiles.length > 0) return false;

    var totalPages = validation.validFiles.length;
    if (totalPages === 0) {
        writeLog("No files to process.");
        return false;
    }

    // Pastikan outputFile didefinisikan (mengikuti pola sebelumnya)
    var outputFile = new File(files[0].output);

    var doc = app.documents.add(DocumentColorSpace.RGB);
    var spacing = 50;
    var width = 594.72;
    var height = 841.68;

    writeLog("Creating artboards for each page...");

    // Buat artboards baru sesuai jumlah PDF
    for (var i = 0; i < totalPages; i++) {
        var yOffset = -(height + spacing) * i;
        var ab = doc.artboards.add([0, yOffset, width, yOffset - height]);
        ab.name = "Page " + (i + 1);
    }

    // Hapus artboard default jika jumlah artboard > jumlah halaman
    if (doc.artboards.length > totalPages) {
        writeLog("Removing default artboard...");
        doc.artboards[0].remove();
    }

    // Tempatkan konten PDF ke artboard
    var placementResult = placeContentOnArtboards(doc, validation.validFiles);

    // Check output directory
    var outputDir = outputFile.parent;
    if (!outputDir.exists) {
        writeLog("Creating output directory: " + outputDir.fsName);
        outputDir.create();
    }

    // Save document sebagai AI
    writeLog("Saving document as: " + outputFile.fsName);
    var saveOptions = new IllustratorSaveOptions();
    saveOptions.compatibility = Compatibility.ILLUSTRATOR17;
    doc.saveAs(outputFile, saveOptions);

    writeLog("Processing completed. File saved: " + outputFile.fsName);
    return true;
}




// ==================================================
// Eksekusi Script
// ==================================================
var scriptStartTime = new Date().getTime();
initLog();
setNoInteraction();

var mainResult = processAllSplitPDFs();

restoreInteraction();
if (mainResult) {
    try { writeLog("=== SCRIPT COMPLETED SUCCESSFULLY! ==="); app.quit(); } catch(e){}
} else {
    writeLog("=== SCRIPT COMPLETED WITH ERRORS ===");
}

if (logFile) {
    logFile.writeln("Total execution time: " + ((new Date().getTime() - scriptStartTime) / 1000) + " seconds");
    logFile.close();
}
