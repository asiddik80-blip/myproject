#target illustrator

var files = [
    {{FILES_ARRAY}}
];

for (var i = 0; i < files.length; i++) {
    var inputFile = new File(files[i].input);
    if (!inputFile.exists) {
        $.writeln("File tidak ditemukan: " + inputFile.fsName);
        continue;
    }

    var doc;
    try {
        doc = app.open(inputFile);
    } catch (e) {
        $.writeln("Gagal membuka file: " + inputFile.fsName + " - " + e);
        continue;
    }

    var hasVector = false;
    for (var j = 0; j < doc.pageItems.length; j++) {
        var item = doc.pageItems[j];
        if (!(item.typename === "PlacedItem" || item.typename === "RasterItem")) {
            hasVector = true;
            break;
        }
    }

    if (hasVector) {
        try {
            var saveFile = new File(files[i].output);
            var options = new IllustratorSaveOptions();
            doc.saveAs(saveFile, options);
            $.writeln("Disimpan: " + saveFile.fsName);
        } catch (e) {
            $.writeln("Gagal menyimpan: " + files[i].output + " - " + e);
        }
    } else {
        $.writeln("Tidak ada objek vektor pada: " + files[i].input);
    }

    doc.close(SaveOptions.DONOTSAVECHANGES);
}
