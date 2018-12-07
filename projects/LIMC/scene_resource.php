<?php

require_once "../../general/api/resource.php";
require_once "../../general/api/api_inc.php";

class SceneResource extends Resource {

    /**
     * Main method.
     * @param $argv
     * @throws
     */
    static function main(array $argv) {

        $mode = self::getValueOfArgs($argv, "-mode", "json");
        $auth = self::getValueOfArgs($argv, "-auth", "");
        $method = self::getValueOfArgs($argv, "-method");

        $resource = new SceneResource();
        switch ($method) {

            case "getById":
                $scene_id = self::getValueOfArgs($argv, "-scene_id");
                $resource_id = $resource->getResourceId($scene_id);
                if ($mode === "json") echo $resource->getJson($scene_id, $resource_id);
                else echo $resource->getString($scene_id, $resource_id);
                break;

            case "putPhoto":
                $scene_resource_id = self::getValueOfArgs($argv, "-resource_id");
                $photo_resource_id = self::getValueOfArgs($argv, "-photo_resource_id");
                $resource->addPhotoByResourceId($scene_resource_id, $photo_resource_id, $auth);
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
     * Gets the resource id from the salsah.org server.
     * @param $scene_id
     * @return int
     * @throws
     */
    function getResourceId(int $scene_id): int {

        $url = "/search/?searchtype=extended&property_id%5B%5D=619&compop%5B%5D=EQ&searchval%5B%5D=" . $scene_id . "&show_nrows=1&start_at=0&filter_by_restype=80";

        $salsahRequest = new SalsahRequest();
        $salsahResponse = $salsahRequest->get($url, "", "scene_id: " . $scene_id);

        $jsonArray = $salsahResponse->body;

        if (isset($jsonArray["subjects"][0]["obj_id"]) === false) {
            if (isset($jsonArray["subjects"])) {
                throw new Exception("404: Resource not found", 404);
            } else {
                throw new Exception("500: Unknown error", 500);
            }
        }

        return str_replace("_-_local", "", $jsonArray["subjects"][0]["obj_id"]);

    }

    /**
     * Adds a photo to a scene
     * @param int $scene_resource_id
     * @param int $photo_resource_id
     * @param string $auth
     * @throws Exception
     */
    function addPhotoByResourceId(int $scene_resource_id, int $photo_resource_id, string $auth) {

        $url = "/values/";

        $salsahRequest = new SalsahRequest();
        $salsahResponse = $salsahRequest->post($url, [
            "res_id" => $scene_resource_id,
            "value_arr" => [
                [
                    "value" => $photo_resource_id,
                    "prop" => "limc:photo"
                ]
            ]
        ], $auth, "scene_resource_id: " . $scene_resource_id . ", photo_resource_id: " . $photo_resource_id);

        $jsonArray = $salsahResponse->body;

        if (isset($jsonArray["status"]) === false) {
            throw new Exception("500: Unknown error", 500);
        } else if ($jsonArray["status"] !== 0) {
            throw new Exception("Status " . $jsonArray["status"] . ": " . $jsonArray["errormsg"], $jsonArray["status"]);
        }

    }

    /**
     * Gets json.
     * @param $scene_id
     * @param $resource_id
     * @return string
     */
    protected function getJson(int $scene_id, int $resource_id = 0): string {
        $array = [
            "resource_id" => $resource_id,
            "scene_id" => $scene_id
        ];
        return json_encode($array);
    }

    /**
     * Gets string.
     * @param $scene_id
     * @param $resource_id
     * @return string
     */
    protected function getString(int $scene_id, int $resource_id = 0): string {
        $str = "-----\n";
        $str .= "Scene ID = " . $scene_id . "\n";
        $str .= "Resource ID = " . $resource_id . "\n";
        $str .= "-----";
        return $str;
    }

}

if (isset($argv) && isset($argv[0]) && $argv[0] === basename(__FILE__)) {
    SceneResource::main($argv);
}
