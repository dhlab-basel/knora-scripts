<?php

require_once "../../general/api/api_inc.php";
require_once "../../general/api/resource.php";
require_once "scene_resource.php";

class PhotoResource extends Resource {

    /**
     * Main method.
     * @param $argv
     * @throws
     */
    static function main(array $argv) {

        $mode = self::getValueOfArgs($argv, "-mode", "json");
        $method = self::getValueOfArgs($argv, "-method");

        $resource = new PhotoResource();
        switch ($method) {

            case "importFromPath":
                $path = self::getValueOfArgs($argv, "-path");
                $extensions = self::getValueOfArgs($argv, "-extensions");
                $index = self::getValueOfArgs($argv, "-i", 0);
                $auth = self::getValueOfArgs($argv, "-auth");
                $resource->importFromPath($path, \explode(",", $extensions), $index, $auth);
                break;

            case "postWithImage":
                $auth = self::getValueOfArgs($argv, "-auth");
                $imageUrl = self::getValueOfArgs($argv, "-image_url");
                $resource_id = $resource->postResource($imageUrl, $auth);
                if ($mode === "json") echo $resource->getJson($resource_id);
                else echo $resource->getString($resource_id);
                break;

            case "delete":
                $auth = self::getValueOfArgs($argv, "-auth");
                $resource_id = self::getValueOfArgs($argv, "-resource_id");
                $resource->deleteByResourceId($resource_id, $auth);
                break;

            default:
                break;

        }

    }

    /**
     * Imports all files to Salsah from a path.
     * @param string $path
     * @param array $extensions
     * @param int $index the index at which we start the import (example: index=1 means start at array index=1, skip index=0)
     * @param string $auth
     */
    private function importFromPath(string $path, array $extensions, int $index, string $auth) {

        $splFileInfos = $this->findPhotosInPath($path, $extensions);

        echo "\n==========\nStarting full import from path \"" . $path . "\".\n";

        $i = 0;
        foreach ($splFileInfos as $splFileInfo) {
            /* @var $splFileInfo SplFileInfo */

            if ($i < $index || strpos($splFileInfo->getFilename(), ".") === 0) {
                $i++;
                continue;
            }

            ob_start();

            try {
                
                echo "\nUploading file with index " . $i . " and path \"" . $splFileInfo->getPathname() . "\"...\n";

                $photo_resource_id = $this->postResource($splFileInfo->getPathname(), $auth);
                if ($photo_resource_id <= 0) throw new Exception("Invalid photo_resource_id!", 1);

                $scene_id = $this->getSceneIdFromPhotoPath($splFileInfo); // throws error code=2

                $scene = new SceneResource();
                $scene_resource_id = $scene->getResourceId($scene_id);
                if ($scene_resource_id <= 0) throw new Exception("Invalid scene_resource_id!", 3);

                $scene->addPhotoByResourceId($scene_resource_id, $photo_resource_id, $auth);

                echo "Done! photo_resource_id=" . $photo_resource_id . ", scene_resource_id=" . $scene_resource_id . "\n----------\n";

                $echo = ob_get_contents();
                ob_end_clean();
                echo $echo;

            } catch(Exception $e) {

                $abort = false;

                switch ($e->getCode()) {

                    case 1:
                        echo "Error in import at index " . $i . ".\n";
                        echo "POST /resources/ failed.\n";
                        echo $e->getMessage() . " (Code: " . $e->getCode() . ").\n";
                        break;
                    case 2:
                        echo "Error in import at index " . $i . ".\n";
                        echo $e->getMessage() . " (Code: " . $e->getCode() . ").\n";
                        break;
                    case 3:
                        echo "Error in import at index " . $i . ".\n";
                        echo "GET /search/ failed.\n";
                        echo $e->getMessage() . " (Code: " . $e->getCode() . ").\n";
                        break;
                    default:
                        if ($e->getCode() > 100) {
                            echo "Fatal error in import at index " . $i . ".\n";
                            echo $e->getMessage() . " (Code: " . $e->getCode() . ").\n";
                            break;
                        } else {
                            echo "Fatal error in import - aborting operation at index " . $i . ".\n";
                            echo "Please restart import with argument -i " . $i . "!\n";
                            echo $e->getMessage() . " (Code: " . $e->getCode() . ").\n";
                            //$abort = true;
                            break;
                        }

                }

                $echo = ob_get_contents();
                ob_end_clean();
                echo $echo;

                // Save error string to file!
                file_put_contents(
                    "logs/error_" . $splFileInfo->getFilename() . ".log",
                    $echo
                );

                if ($abort) {
                    break;
                }

            }

            $i++;

        }

        echo "\nAll done!\n==========\n";

    }

    /**
     * Gets the scene id from a photo's path.
     * @param SplFileInfo $fileInfo
     * @return int
     * @throws Exception
     */
    private function getSceneIdFromPhotoPath(SplFileInfo $fileInfo): int {

        $name = $fileInfo->getFilename();
        $array = explode("X", $name);
        if (is_array($array) && \count($array) === 2) {
            $id = \intval($array[0]);
            if ($id > 0) return $id;
        }

        throw new Exception("No valid scene found in path! Valid format is \"[SceneId]X[FileName]\", found \"" . $fileInfo->getFilename() . "\".", 2);

    }

    /**
     * Finds all photos in a local path and returns the full paths as an array.
     * @param string $path
     * @param array $extensions
     * @return array returns an array of SplFileInfo
     */
    private function findPhotosInPath(string $path, array $extensions): array {

        $allowAllExtensions = false;
        if (\in_array("*", $extensions)) {
            $allowAllExtensions = true;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $allSplFileInfos = array_filter(iterator_to_array($iterator), function(SplFileInfo $file) use ($allowAllExtensions, $extensions) {
            return $file->isFile() && ( $allowAllExtensions || \in_array($file->getExtension(), $extensions) );
        });

        return $allSplFileInfos;

    }

    /**
     * Posts a photo resource which includes a real photo.
     * @param string $imageUrl
     * @param string $auth
     * @return int the resource id
     * @throws Exception
     */
    private function postResource(string $imageUrl, string $auth): int {

        $fileData = @file_get_contents($imageUrl);
        if ($fileData === false || $fileData === "") {
            throw new Exception("500: File \"" . $imageUrl . "\" does not exist", 500);
        }

        $salsahRequest = new SalsahRequest();
        $salsahResponse = $salsahRequest->postWithFile("/resources/", [
                "restype_id" => "limc:photo",
                "properties" => [
                    "limc:newPhoto" => [
                        "value" => 1
                    ]
                ]
            ], $imageUrl, $auth, "imageUrl: " . $imageUrl);

        $jsonArray = $salsahResponse->body;

        if (isset($jsonArray["res_id"]) === false) {
            throw new Exception($salsahResponse->responseCode . " " . $salsahResponse->responseString, $salsahResponse->responseCode);
        }

        return $jsonArray["res_id"];

    }

}

if (isset($argv) && isset($argv[0]) && $argv[0] === basename(__FILE__)) {
    PhotoResource::main($argv);
}
