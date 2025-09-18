//@target illustrator

// Data dari Laravel
// Format: files = [ { input: "path/to/file.pdf", output: "path/to/file.ai", totalPages: 5, width: 841.68, height: 594.72 }, ... ]
var files = {{FILES_ARRAY}};

(function () {
    // Create log file di folder yang sama dengan input files
    var logFile = null;
    var logPath = "";
    
    try {
        // Ambil path dari file pertama untuk menentukan lokasi log
        if (files.length > 0) {
            var firstInputPath = files[0].input;
            var inputFile = new File(firstInputPath);
            var parentFolder = inputFile.parent;
            logPath = parentFolder.fsName + "/jsx_debug_log.txt";
            logFile = new File(logPath);
            logFile.open("w");
            logFile.writeln("=== JSX DEBUG LOG - " + new Date() + " ===");
        }
    } catch (e) {
        $.writeln("Warning: Could not create log file: " + e);
    }

    // === Helper Functions ===
    function writeLog(message) {
        $.writeln(message);
        try {
            if (logFile) {
                logFile.writeln(message);
            }
        } catch (e) {
            $.writeln("Error writing to log file: " + e);
        }
    }

    function dumpObject(obj) {
        var str = "";
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                str += key + ": " + obj[key] + ", ";
            }
        }
        if (str.length > 2) {
            str = str.substring(0, str.length - 2); // hapus koma terakhir
        }
        return "{" + str + "}";
    }

    writeLog("=== SCRIPT START ===");
    writeLog("Files array length: " + files.length);
    writeLog("DEBUG raw files = " + files.toSource());
    if (files.length > 0) {
        writeLog("DEBUG files[0] = " + files[0]);
    }
    writeLog("Log file location: " + logPath);
    
    // Simpan & set level interaksi agar tidak menampilkan dialog
    var oldLevel = app.userInteractionLevel;
    try { 
        app.userInteractionLevel = UserInteractionLevel.DONTDISPLAYALERTS; 
        writeLog("User interaction level set to DONTDISPLAYALERTS");
    } catch (e) {
        writeLog("Warning: Could not set user interaction level: " + e);
    }

    for (var f = 0; f < files.length; f++) {
        writeLog("=== PROCESSING FILE " + (f + 1) + " OF " + files.length + " ===");
        
        var fileData = files[f];
        writeLog("File data received - checking properties...");
        writeLog("- input: " + fileData.input);
        writeLog("- output: " + fileData.output);  
        writeLog("- totalPages (original): " + fileData.totalPages);
        writeLog("- pages array: " + (fileData.pages ? fileData.pages.length + " items" : "not available"));
        writeLog("- width: " + fileData.width);
        writeLog("- height: " + fileData.height);
        
        var inputFile = new File(fileData.input);
        var outputFile = new File(fileData.output);
        var totalPages = fileData.totalPages || 1;
        
        writeLog("*** Using dynamic totalPages: " + totalPages + " ***");
        
        var artboardWidth = fileData.width || 841.68;
        var artboardHeight = fileData.height || 594.72;
        var spacing = 50;

        writeLog("Input file: " + inputFile.fsName);
        writeLog("Output file: " + outputFile.fsName);
        writeLog("Total pages (final): " + totalPages);
        writeLog("Artboard dimensions: " + artboardWidth + " x " + artboardHeight);

        if (!inputFile.exists) {
            writeLog("ERROR: File tidak ditemukan: " + inputFile.fsName);
            continue;
        }

        writeLog("File exists, processing: " + inputFile.fsName + " (" + totalPages + " pages)");

        // === FALLBACK APPROACH: Create empty artboards and use placedItems ===
        writeLog("=== FALLBACK MULTI-PAGE APPROACH (No PDFOpenOptions) ===");
        
        var doc = null;

        try {
            // Step 1: Open PDF (will open page 1 by default)
            writeLog("STEP 1: Opening PDF as base document (page 1)...");
            doc = app.open(inputFile);
            writeLog("Base document opened successfully");

            // Setup first artboard
            if (doc.artboards.length > 0) {
                doc.artboards[0].name = "Page 1";
                var firstRect = doc.artboards[0].artboardRect;
                writeLog("Original artboard rect: " + firstRect);
                doc.artboards[0].artboardRect = [0, 0, artboardWidth, -artboardHeight];
                writeLog("First artboard setup: Page 1 with rect [0, 0, " + artboardWidth + ", " + (-artboardHeight) + "]");
            } else {
                writeLog("ERROR: No artboards found in base document");
            }

            writeLog("STEP 2: Creating additional artboards...");
            writeLog("DEBUG: totalPages value = " + totalPages);
            writeLog("DEBUG: Expected artboards to create: " + (totalPages - 1));
            
            // Step 2: Create additional empty artboards
            for (var page = 2; page <= totalPages; page++) {
                writeLog("=== Creating artboard for page " + page + " ===");
                
                try {
                    // Calculate artboard position
                    var yOffset = -(artboardHeight + spacing) * (page - 1);
                    writeLog("Artboard " + page + " yOffset: " + yOffset);
                    
                    // Create new artboard
                    var newArtboard = doc.artboards.add([0, yOffset, artboardWidth, yOffset - artboardHeight]);
                    newArtboard.name = "Page " + page;
                    writeLog("SUCCESS: Created artboard: " + newArtboard.name + " with rect [0, " + yOffset + ", " + artboardWidth + ", " + (yOffset - artboardHeight) + "]");

                } catch (artboardError) {
                    writeLog("ERROR: Failed to create artboard for page " + page + ": " + artboardError);
                }
            }

            writeLog("STEP 3: Placing PDF content on each artboard...");
            writeLog("Current artboards count: " + doc.artboards.length);
            
            // Step 3: Place PDF content on each artboard
            for (var page = 2; page <= totalPages; page++) {
                writeLog("=== Placing content on artboard " + page + " ===");
                
                try {
                    // Set active artboard
                    doc.artboards.setActiveArtboardIndex(page - 1);
                    writeLog("Set active artboard to index: " + (page - 1));

                    // Create placed item for this artboard
                    var placedItem = doc.placedItems.add();
                    placedItem.file = inputFile;
                    writeLog("Created placed item for page " + page);

                    // Get current artboard bounds for positioning
                    var currentArtboard = doc.artboards[page - 1];
                    var artboardRect = currentArtboard.artboardRect;
                    var artboardCenterX = (artboardRect[0] + artboardRect[2]) / 2;
                    var artboardCenterY = (artboardRect[1] + artboardRect[3]) / 2;
                    
                    // Position placed item at artboard center
                    placedItem.position = [artboardCenterX - (placedItem.width / 2), 
                                         artboardCenterY + (placedItem.height / 2)];
                    
                    writeLog("Positioned placed item at artboard " + page + " center");
                    writeLog("PlacedItem dimensions: " + placedItem.width + " x " + placedItem.height);

                    // Add note: Content will be same as page 1 due to PDF limitation
                    writeLog("NOTE: Content is same as page 1 (PDF multi-page limitation without PDFOpenOptions)");

                } catch (placeError) {
                    writeLog("ERROR: Failed to place content on artboard " + page + ": " + placeError);
                }
            }

            writeLog("STEP 4: Final document verification...");
            writeLog("Total artboards in final document: " + doc.artboards.length);
            
            // Log artboard names for verification
            writeLog("Artboard verification:");
            for (var ab = 0; ab < doc.artboards.length; ab++) {
                writeLog("  Artboard " + ab + ": " + doc.artboards[ab].name);
            }
            
            writeLog("STEP 5: Checking vector detection...");
            // Vector detection
            var hasVector = false;
            try {
                writeLog("Total pageItems in final document: " + doc.pageItems.length);
                for (var j = 0; j < Math.min(doc.pageItems.length, 10); j++) {
                    var item = doc.pageItems[j];
                    writeLog("Item " + j + ": " + item.typename);
                    if (!(item.typename === "PlacedItem" || item.typename === "RasterItem")) {
                        hasVector = true;
                        writeLog("Vector found at item " + j);
                    }
                }
                if (doc.pageItems.length > 10) {
                    writeLog("... and " + (doc.pageItems.length - 10) + " more items (truncated for log)");
                }
                writeLog("Vector detection result: " + hasVector);
            } catch (e) {
                writeLog("ERROR: Error saat memeriksa vektor: " + e);
            }

            writeLog("STEP 6: Saving final document...");
            // Save document
            if (hasVector || true) { // Force save for testing
                try {
                    var saveOptions = new IllustratorSaveOptions();
                    saveOptions.compatibility = Compatibility.ILLUSTRATOR17;
                    doc.saveAs(outputFile, saveOptions);
                    writeLog("SUCCESS: File saved: " + outputFile.fsName + " with " + doc.artboards.length + " artboards");
                    writeLog("WARNING: All artboards contain same content (page 1) due to PDF limitation");
                    
                } catch (e) {
                    writeLog("ERROR: Gagal menyimpan file AI: " + e);
                }
            } else {
                writeLog("SKIP: Tidak ada objek vektor pada: " + inputFile.fsName);
            }

        } catch (e) {
            writeLog("ERROR: Error in main processing: " + e);
        } finally {
            writeLog("CLEANUP: Closing document...");
            
            // Close document
            try {
                if (doc && doc.saved !== undefined) {
                    doc.close(SaveOptions.DONOTSAVECHANGES);
                    writeLog("Document closed successfully");
                }
            } catch (e) {
                writeLog("Warning: Error closing document: " + e);
            }
        }
        
        writeLog("=== FILE " + (f + 1) + " COMPLETED ===");
    }

    writeLog("CLEANUP: Restoring user interaction level...");
    // Pulihkan level interaksi
    try { 
        app.userInteractionLevel = oldLevel; 
        writeLog("User interaction level restored");
    } catch (e) {
        writeLog("ERROR: Could not restore interaction level: " + e);
    }

    writeLog("CLEANUP: Quitting Illustrator...");
    // Tutup Illustrator setelah semua proses selesai
    try { 
        app.quit(); 
        writeLog("Illustrator quit successfully");
    } catch (e) {
        writeLog("ERROR: Error quitting Illustrator: " + e);
    }

    // Close log file
    try {
        if (logFile) {
            logFile.writeln("=== SCRIPT COMPLETED SUCCESSFULLY! ===");
            logFile.close();
        }
    } catch (e) {
        $.writeln("Error closing log file: " + e);
    }

    writeLog("=== SCRIPT COMPLETED SUCCESSFULLY! ===");
})()