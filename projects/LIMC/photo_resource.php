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

        $auth = self::getValueOfArgs($argv, "-auth");
        $method = self::getValueOfArgs($argv, "-method");
        $scene_id = self::getValueOfArgs($argv, "-scene_id", -1);

        $resource = new PhotoResource();
        switch ($method) {

            case "postWithImage":
                $imageUrl = self::getValueOfArgs($argv, "-image_url");
                $resource_id = $resource->postResource($imageUrl, $auth);
                echo $resource->getJson($resource_id);
                break;

            case "delete":
                $resource_id = self::getValueOfArgs($argv, "-resource_id");
                $resource->deleteByResourceId($resource_id, $auth);
                break;

            default:
                break;

        }

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
