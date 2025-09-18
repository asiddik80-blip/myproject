//@target illustrator
var files = [
    {{FILES_ARRAY}} // Diganti oleh Laravel controller
];

for (var i = 0; i < files.length; i++) {
    var inputFile = File(files[i].input);
    var outputFile = File(files[i].output);

    if (inputFile.exists) {
        var doc = app.open(inputFile);

        // === MULAI: Tambahkan teks hasil OCR dari file JSON ===
        try {
            // Cari file JSON hasil OCR
            var jsonPath = files[i].input.replace(/\.pdf$/i, '.json');
            var jsonFile = new File(jsonPath);

            if (jsonFile.exists) {
                jsonFile.open("r");
                var jsonText = jsonFile.read();
                jsonFile.close();

                var ocrData = JSON.parse(jsonText);

                var pageCount = 0;
                for (var key in ocrData) {
                    if (pageCount >= 20) break;

                    var text = ocrData[key];
                    var tf = doc.textFrames.add();
                    tf.contents = text;

                    // Posisi default, bisa dikembangkan agar dinamis
                    tf.top = 800 - (pageCount * 30);
                    tf.left = 100;

                    tf.textRange.characterAttributes.textFont = app.textFonts.getByName("ArialMT");
                    tf.textRange.characterAttributes.size = 12;

                    pageCount++;
                }
            } else {
                $.writeln("OCR JSON file not found: " + jsonPath);
            }
        } catch (e) {
            $.writeln("Error inserting OCR text: " + e.message);
        }
        // === SELESAI: Tambahkan teks hasil OCR ===

        // Simpan file .ai
        var options = new IllustratorSaveOptions();
        options.compatibility = Compatibility.ILLUSTRATOR17;
        doc.saveAs(outputFile, options);
        doc.close(SaveOptions.DONOTSAVECHANGES);
    } else {
        $.writeln("Input file not found: " + inputFile.fsName);
    }
}
