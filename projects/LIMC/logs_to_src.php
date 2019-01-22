<?php

// Read in all logs
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("logs"));
$logs = array_filter(iterator_to_array($iterator), function(SplFileInfo $file) {
    return $file->isFile() && ( \in_array($file->getExtension(), ["log"]) );
});

echo "Found " . \count($logs) . " log files.\n";

// Volumes
$volumes = [
    "/Volumes/LIMCBilder/Teil1",
    "/Volumes/LIMCBilder/Teil2",
    "/Volumes/LIMCBilder/Teil3",
    "/Volumes/LIMCBilder/Teil4",
    "/Volumes/LIMCBilder/Teil5",
    "/Volumes/LIMCBilder/Teil6",
    "/Volumes/LIMCBilder/Teil7",
    "/Volumes/LIMCBilder/Teil8",
    "/Volumes/LIMCBilder/Teil9",
    "/Volumes/LIMCBilder/Teil10",
    "/Volumes/LIMCBilder/Teil11",
    "/Volumes/LIMCBilder/Teil12",
    "/Volumes/LIMCBilder/Teil13",
    "/Volumes/LIMCBilder/Teil14",
    "/Volumes/LIMCBilder/Teil15",
    "/Volumes/LIMCBilder/Teil16",
    "/Volumes/LIMCBilder/Teil17",
    "/Volumes/LIMCBilder/Teil18",
    "/Volumes/LIMCBilder/Teil19",
    "/Volumes/LIMCBilder/Teil20",
    "/Volumes/LIMCBilder/Teil21",
    "/Volumes/LIMCBilder/Teil22",
    "/Volumes/LIMCBilder/Teil23",
    "/Volumes/LIMCBilder/Teil24",
    "/Volumes/LIMCBilder2/MartinBenz",
    "/Volumes/LIMCBilder2/Teil17"
];
$destination = "logs";

// Read in all images
$images = [];
foreach ($volumes as $volume) {

    $directoryIterator = new RecursiveDirectoryIterator($volume);
    $directoryIterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directoryIterator);
    $images = \array_merge($images, \array_values(array_filter(iterator_to_array($iterator), function(SplFileInfo $file) {
        return $file->isFile() && ( \in_array($file->getExtension(), ["tif"]) );
    })));


}

echo "Found " . \count($images) . " image files.\n";

echo "Matching images and logs.";

// Now find every image for all logs
$errors = [];
foreach ($logs as $log) {
    /* @var $log SplFileInfo */

    $fileName = \str_replace(["error_", ".tif"], "", $log->getBasename(".log"));

    $relatedFiles = \array_values(\array_filter($images, function(SplFileInfo $fileInfo) use ($fileName) {
        return $fileName === $fileInfo->getBasename(".tif");
    }));

    if (\count($relatedFiles) < 1) {
        $errors[] = $fileName;
        echo "!";
        continue;
    }

    /* @var $relatedFile SplFileInfo */
    $relatedFile = $relatedFiles[0];

    copy($relatedFile->getPathname(), $destination . "/" . $relatedFile->getFilename());
    echo ".";

}

echo "\nAll done!";

if (\count($errors) > 0) {
    echo "\nThe following files could not be found:";
    foreach ($errors as $error) echo "\n" . $error;
}

echo "\n";