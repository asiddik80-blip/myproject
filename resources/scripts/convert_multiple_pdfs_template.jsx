var files = [
    {{FILES_ARRAY}} // baris ini akan digantikan di controller
];

for (var i = 0; i < files.length; i++) {
    var inputFile = File(files[i].input);
    var outputFile = File(files[i].output);

    if (inputFile.exists) {
        var doc = app.open(inputFile);
        var options = new IllustratorSaveOptions();
        options.compatibility = Compatibility.ILLUSTRATOR17;
        doc.saveAs(outputFile, options);
        doc.close(SaveOptions.DONOTSAVECHANGES);
    } else {
        $.writeln("File not found: " + inputFile.fsName);
    }
}
