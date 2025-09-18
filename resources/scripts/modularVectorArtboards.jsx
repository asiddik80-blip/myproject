//@target illustrator

// Data dari Laravel
// Format: files = [ { input: ["split/page1.pdf", "split/page2.pdf"], output: "path/to/file.ai", totalPages: 5, width: 841.68, height: 594.72 }, ... ]
var files = {{FILES_ARRAY}};

// Daftar path PDF split (untuk validasi konsistensi dengan input aktual)
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


// 👉 Log raw JSON untuk memastikan placeholder diganti dengan benar
$.writeln("=== RAW FILES_ARRAY ===");
for (var i = 0; i < files.length; i++) {
    $.writeln("File " + i + ": output=" + files[i].output + ", totalPages=" + files[i].totalPages);
    for (var j = 0; j < files[i].input.length; j++) {
        $.writeln("   input[" + j + "]=" + files[i].input[j]);
    }
}

$.writeln("=== RAW ALL_SPLIT_PDF_PATHS ===");
for (var k = 0; k < all_split_pdf_paths.length; k++) {
    $.writeln("   path[" + k + "]=" + all_split_pdf_paths[k]);
}


// 👉 Log semua path PDF yang diterima dari Laravel
for (var i = 0; i < files.length; i++) {
    var fileObj = files[i];
    for (var j = 0; j < fileObj.input.length; j++) {
        $.writeln("Received PDF path: " + fileObj.input[j]);
    }
}

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

    // NEW: Validasi path dan konsistensi
    function validatePathsAndConsistency() {
        writeLog("=== PATH VALIDATION START ===");
        
        // Kumpulkan semua input paths dari files array
        var allInputPaths = [];
        var totalExpectedPages = 0;
        
        for (var i = 0; i < files.length; i++) {
            var fileData = files[i];
            writeLog("File " + (i + 1) + ": " + fileData.input.length + " pages");
            
            for (var j = 0; j < fileData.input.length; j++) {
                allInputPaths.push(fileData.input[j]);
                totalExpectedPages++;
            }
        }
        
        writeLog("Total files to process: " + files.length);
        writeLog("Total pages from files array: " + totalExpectedPages);
        writeLog("Total paths in debug array: " + all_split_pdf_paths.length);
        
        // Validasi konsistensi jumlah
        if (totalExpectedPages !== all_split_pdf_paths.length) {
            writeLog("WARNING: Path count mismatch!");
            writeLog("  - Files array total pages: " + totalExpectedPages);
            writeLog("  - Debug array total paths: " + all_split_pdf_paths.length);
        } else {
            writeLog("SUCCESS: Path counts are consistent");
        }
        
        // Validasi keberadaan file input
        var missingFiles = [];
        var validFiles = [];
        
        for (var i = 0; i < allInputPaths.length; i++) {
            var inputFile = new File(allInputPaths[i]);
            if (!inputFile.exists) {
                missingFiles.push(allInputPaths[i]);
                writeLog("MISSING: " + allInputPaths[i]);
            } else {
                validFiles.push(allInputPaths[i]);
            }
        }
        
        writeLog("Valid files found: " + validFiles.length);
        writeLog("Missing files: " + missingFiles.length);
        
        if (missingFiles.length > 0) {
            writeLog("ERROR: Some input files are missing!");
            for (var i = 0; i < missingFiles.length; i++) {
                writeLog("  - Missing: " + missingFiles[i]);
            }
        }
        
        // Sample path logging untuk debugging
        if (allInputPaths.length > 0) {
            writeLog("Sample input paths:");
            var sampleCount = Math.min(3, allInputPaths.length);
            for (var i = 0; i < sampleCount; i++) {
                writeLog("  [" + (i + 1) + "] " + allInputPaths[i]);
            }
        }
        
        if (all_split_pdf_paths.length > 0) {
            writeLog("Sample debug paths:");
            var sampleCount = Math.min(3, all_split_pdf_paths.length);
            for (var i = 0; i < sampleCount; i++) {
                writeLog("  [" + (i + 1) + "] " + all_split_pdf_paths[i]);
            }
        }
        
        writeLog("=== PATH VALIDATION END ===");
        
        return {
            totalInputPaths: allInputPaths.length,
            validFiles: validFiles.length,
            missingFiles: missingFiles.length,
            isConsistent: totalExpectedPages === all_split_pdf_paths.length,
            missingFilesList: missingFiles
        };
    }

    // Tempatkan isi PDF ke artboard dengan error handling yang lebih baik
    function placeContentOnArtboards(targetDoc, inputPages) {
        writeLog("=== PLACING CONTENT ON ARTBOARDS ===");
        
        var successCount = 0;
        var errorCount = 0;
        
        for (var i = 0; i < inputPages.length; i++) {
            var pageFile = new File(inputPages[i]);
            
            writeLog("Processing page " + (i + 1) + "/" + inputPages.length + ": " + inputPages[i]);
            
            if (!pageFile.exists) {
                writeLog("ERROR: File not found: " + inputPages[i]);
                errorCount++;
                continue;
            }

            try {
                var pdfOpts = new PDFOpenOptions();
                pdfOpts.pageToOpen = 1; 
                pdfOpts.cropBox = PDFCropBoxType.CROPARTBOX;
                pdfOpts.preserveEditability = true;

                var tempDoc = app.open(pageFile, pdfOpts);
                writeLog("Successfully opened: " + inputPages[i]);

                if (tempDoc.artboards.length > 0) {
                    var srcGroup = tempDoc.activeLayer.pageItems;
                    var itemsToMove = [];
                    
                    // Kumpulkan items dulu sebelum move
                    for (var j = srcGroup.length - 1; j >= 0; j--) {
                        itemsToMove.push(srcGroup[j]);
                    }
                    
                    // Pindahkan items
                    for (var k = 0; k < itemsToMove.length; k++) {
                        try {
                            itemsToMove[k].duplicate(targetDoc.layers[0], ElementPlacement.PLACEATBEGINNING);
                        } catch (moveError) {
                            writeLog("Warning: Could not move item " + k + " from page " + (i + 1) + ": " + moveError);
                        }
                    }
                    
                    writeLog("Moved " + itemsToMove.length + " items from page " + (i + 1));
                    successCount++;
                } else {
                    writeLog("Warning: No artboards found in: " + inputPages[i]);
                }

                tempDoc.close(SaveOptions.DONOTSAVECHANGES);
                
                // Set active artboard untuk page ini
                if (i < targetDoc.artboards.length) {
                    targetDoc.artboards.setActiveArtboardIndex(i);
                }
                
                app.activeDocument = targetDoc;
                
            } catch (pageError) {
                writeLog("ERROR processing page " + (i + 1) + ": " + pageError);
                errorCount++;
                
                // Coba tutup dokumen jika masih terbuka
                try {
                    if (tempDoc) {
                        tempDoc.close(SaveOptions.DONOTSAVECHANGES);
                    }
                } catch (closeError) {
                    writeLog("Error closing temp document: " + closeError);
                }
            }
        }
        
        writeLog("Content placement summary:");
        writeLog("  - Successfully processed: " + successCount + " pages");
        writeLog("  - Errors encountered: " + errorCount + " pages");
        writeLog("=== CONTENT PLACEMENT COMPLETED ===");
        
        return {
            successCount: successCount,
            errorCount: errorCount,
            totalPages: inputPages.length
        };
    }

    function processFile(fileData, index, total) {
        writeLog("=== PROCESSING FILE " + (index + 1) + " OF " + total + " ===");

        var inputFiles = fileData.input;
        var outputFile = new File(fileData.output);
        var totalPages = inputFiles.length || 1;
        var artboardWidth = fileData.width || 841.68;
        var artboardHeight = fileData.height || 594.72;
        var spacing = 50;

        writeLog("File details:");
        writeLog("  - Input pages: " + totalPages);
        writeLog("  - Output: " + fileData.output);
        writeLog("  - Artboard size: " + artboardWidth + " x " + artboardHeight);

        // NEW: Pre-validation semua input files
        var preValidation = [];
        for (var f = 0; f < inputFiles.length; f++) {
            var inputFile = new File(inputFiles[f]);
            if (!inputFile.exists) {
                writeLog("ERROR: Input file not found: " + inputFiles[f]);
                preValidation.push({index: f, path: inputFiles[f], exists: false});
            } else {
                preValidation.push({index: f, path: inputFiles[f], exists: true});
            }
        }
        
        var validInputs = 0;
        for (var v = 0; v < preValidation.length; v++) {
            if (preValidation[v].exists) validInputs++;
        }
        
        writeLog("Pre-validation result: " + validInputs + "/" + totalPages + " files exist");
        
        if (validInputs === 0) {
            writeLog("ERROR: No valid input files found for this file. Skipping.");
            return false;
        }

        var doc = null;
        try {
            writeLog("Creating new document...");
            
            // Buat dokumen baru
            doc = app.documents.add(DocumentColorSpace.RGB);
            writeLog("Document created successfully");

            // Atur artboard pertama
            if (doc.artboards.length > 0) {
                doc.artboards[0].name = "Page 1";
                doc.artboards[0].artboardRect = [0, 0, artboardWidth, -artboardHeight];
                writeLog("First artboard configured: Page 1");
            }

            // Tambah artboard lainnya
            writeLog("Adding artboards for " + (totalPages - 1) + " additional pages...");
            for (var page = 2; page <= totalPages; page++) {
                try {
                    var yOffset = -(artboardHeight + spacing) * (page - 1);
                    var newAB = doc.artboards.add([0, yOffset, artboardWidth, yOffset - artboardHeight]);
                    newAB.name = "Page " + page;
                } catch (artboardError) {
                    writeLog("Warning: Could not create artboard for page " + page + ": " + artboardError);
                }
            }
            
            writeLog("Total artboards created: " + doc.artboards.length);

            // Tempatkan konten PDF ke artboard
            var placementResult = placeContentOnArtboards(doc, inputFiles);

            // Check output directory
            var outputDir = outputFile.parent;
            if (!outputDir.exists) {
                writeLog("Creating output directory: " + outputDir.fsName);
                outputDir.create();
            }

            // Save as AI
            writeLog("Saving document as: " + outputFile.fsName);
            var saveOptions = new IllustratorSaveOptions();
            saveOptions.compatibility = Compatibility.ILLUSTRATOR17;
            doc.saveAs(outputFile, saveOptions);
            
            writeLog("SUCCESS: File saved successfully");
            writeLog("Placement summary: " + placementResult.successCount + " successful, " + placementResult.errorCount + " errors");
            
        } catch (e) {
            writeLog("ERROR processing file " + (index + 1) + ": " + e);
            writeLog("Error details: " + e.toString());
            return false;
        } finally {
            if (doc) {
                try { 
                    writeLog("Closing document...");
                    doc.close(SaveOptions.DONOTSAVECHANGES); 
                } catch (closeError) {
                    writeLog("Warning: Error closing document: " + closeError);
                }
            }
        }
        
        writeLog("=== FILE " + (index + 1) + " COMPLETED SUCCESSFULLY ===");
        return true;
    }

    function quitIllustrator() {
        try { 
            writeLog("Quitting Illustrator...");
            app.quit(); 
        } catch (e) { 
            writeLog("ERROR quitting Illustrator: " + e); 
        }
    }

    // === MAIN EXECUTION ===
    function main() {
        writeLog("=== SCRIPT START ===");
        writeLog("Processing " + files.length + " files");
        
        // Step 1: Validate paths and consistency
        var validationResult = validatePathsAndConsistency();
        
        if (validationResult.missingFiles > 0) {
            writeLog("CRITICAL: " + validationResult.missingFiles + " files are missing!");
            writeLog("Cannot proceed with missing files. Check paths:");
            for (var i = 0; i < validationResult.missingFilesList.length; i++) {
                writeLog("  - " + validationResult.missingFilesList[i]);
            }
            
            if (logFile) {
                logFile.writeln("=== SCRIPT FAILED - MISSING FILES ===");
                logFile.close();
            }
            return false;
        }
        
        if (!validationResult.isConsistent) {
            writeLog("WARNING: Path counts are inconsistent but proceeding...");
        }
        
        setNoInteraction();
        
        // Step 2: Process each file
        var successCount = 0;
        var errorCount = 0;
        
        for (var f = 0; f < files.length; f++) {
            writeLog("");
            writeLog(">>> Starting file " + (f + 1) + " of " + files.length);
            
            var result = processFile(files[f], f, files.length);
            if (result) {
                successCount++;
                writeLog(">>> File " + (f + 1) + " completed successfully");
            } else {
                errorCount++;
                writeLog(">>> File " + (f + 1) + " failed to process");
            }
        }
        
        restoreInteraction();
        
        // Step 3: Final summary
        writeLog("");
        writeLog("=== PROCESSING SUMMARY ===");
        writeLog("Files processed successfully: " + successCount);
        writeLog("Files with errors: " + errorCount);
        writeLog("Total processing time: " + ((new Date().getTime() - scriptStartTime) / 1000) + " seconds");
        
        if (successCount === files.length) {
            writeLog("=== ALL FILES PROCESSED SUCCESSFULLY! ===");
            return true;
        } else if (successCount > 0) {
            writeLog("=== PARTIAL SUCCESS - SOME FILES PROCESSED ===");
            return true;
        } else {
            writeLog("=== SCRIPT FAILED - NO FILES PROCESSED ===");
            return false;
        }
    }

    // === SCRIPT EXECUTION ===
    var scriptStartTime = new Date().getTime();
    
    initLog();
    
    var mainResult = main();
    
    if (mainResult) {
        quitIllustrator();
    }

    if (logFile) {
        if (mainResult) {
            logFile.writeln("=== SCRIPT COMPLETED SUCCESSFULLY! ===");
        } else {
            logFile.writeln("=== SCRIPT COMPLETED WITH ERRORS ===");
        }
        logFile.writeln("Total execution time: " + ((new Date().getTime() - scriptStartTime) / 1000) + " seconds");
        logFile.close();
    }

    var finalMessage = mainResult ? 
        "=== SCRIPT COMPLETED SUCCESSFULLY! ===" : 
        "=== SCRIPT COMPLETED WITH ERRORS ===";
    
    writeLog(finalMessage);
    
})();