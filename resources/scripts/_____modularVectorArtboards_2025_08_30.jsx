//@target illustrator

// Data dari Laravel
// Format: files = [ { input: ["split/page1.pdf", "split/page2.pdf"], output: "path/to/file.ai", totalPages: 5, width: 841.68, height: 594.72 }, ... ]
var files = {{FILES_ARRAY}};

// Daftar path PDF split (opsional, untuk debug/analisa, bukan loop utama)
var all_split_pdf_paths = [
    {{ALL_SPLIT_PDF_PATHS}}
];

(function () {
    // === GLOBAL STATE ===
    var logFile = null;
    var oldLevel = app.userInteractionLevel;

    // === Helper Functions ===
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

    // Tempatkan isi PDF ke artboard
    function placeContentOnArtboards(targetDoc, inputPages) {
        for (var i = 0; i < inputPages.length; i++) {
            var pageFile = new File(inputPages[i]);
            if (!pageFile.exists) {
                writeLog("File tidak ditemukan: " + inputPages[i]);
                continue;
            }

            var pdfOpts = new PDFOpenOptions();
            pdfOpts.pageToOpen = 1; 
            pdfOpts.cropBox = PDFCropBoxType.CROPARTBOX;
            pdfOpts.preserveEditability = true;

            var tempDoc = app.open(pageFile, pdfOpts);

            if (tempDoc.artboards.length > 0) {
                var srcGroup = tempDoc.activeLayer.pageItems;
                for (var j = srcGroup.length - 1; j >= 0; j--) {
                    srcGroup[j].duplicate(targetDoc.layers[0], ElementPlacement.PLACEATBEGINNING);
                }
            }

            tempDoc.close(SaveOptions.DONOTSAVECHANGES);
            targetDoc.artboards.setActiveArtboardIndex(i);
            app.activeDocument = targetDoc;
        }
    }

    function processFile(fileData, index, total) {
        writeLog("=== PROCESSING FILE " + (index + 1) + " OF " + total + " ===");

        var inputFiles = fileData.input;
        var outputFile = new File(fileData.output);
        var totalPages = inputFiles.length || 1;
        var artboardWidth = fileData.width || 841.68;
        var artboardHeight = fileData.height || 594.72;
        var spacing = 50;

        // cek file
        for (var f = 0; f < inputFiles.length; f++) {
            if (!new File(inputFiles[f]).exists) {
                writeLog("ERROR: File tidak ditemukan: " + inputFiles[f]);
                return;
            }
        }

        var doc = null;
        try {
            // Buat dokumen baru
            doc = app.documents.add(DocumentColorSpace.RGB);

            // Atur artboard pertama
            if (doc.artboards.length > 0) {
                doc.artboards[0].name = "Page 1";
                doc.artboards[0].artboardRect = [0, 0, artboardWidth, -artboardHeight];
            }

            // Tambah artboard lainnya
            for (var page = 2; page <= totalPages; page++) {
                var yOffset = -(artboardHeight + spacing) * (page - 1);
                var newAB = doc.artboards.add([0, yOffset, artboardWidth, yOffset - artboardHeight]);
                newAB.name = "Page " + page;
            }

            // Tempatkan konten PDF ke artboard
            placeContentOnArtboards(doc, inputFiles);

            // Save as AI
            var saveOptions = new IllustratorSaveOptions();
            saveOptions.compatibility = Compatibility.ILLUSTRATOR17;
            doc.saveAs(outputFile, saveOptions);
            writeLog("File saved: " + outputFile.fsName);
        } catch (e) {
            writeLog("ERROR processing file: " + e);
        } finally {
            if (doc) {
                try { doc.close(SaveOptions.DONOTSAVECHANGES); } catch (e) {}
            }
        }
        writeLog("=== FILE " + (index + 1) + " COMPLETED ===");
    }

    function quitIllustrator() {
        try { app.quit(); } catch (e) { writeLog("ERROR quitting Illustrator: " + e); }
    }

    // === MAIN ===
    initLog();
    writeLog("=== SCRIPT START ===");
    setNoInteraction();

    for (var f = 0; f < files.length; f++) {
        processFile(files[f], f, files.length);
    }

    restoreInteraction();
    quitIllustrator();

    if (logFile) {
        logFile.writeln("=== SCRIPT COMPLETED SUCCESSFULLY! ===");
        logFile.close();
    }

    writeLog("=== SCRIPT COMPLETED SUCCESSFULLY! ===");
})();
