var inputFile = File("{{INPUT_PATH}}");
var outputFile = File("{{OUTPUT_PATH}}");

var doc = app.open(inputFile);
var options = new IllustratorSaveOptions();
options.compatibility = Compatibility.ILLUSTRATOR17;
doc.saveAs(outputFile, options);
doc.close(SaveOptions.DONOTSAVECHANGES);
