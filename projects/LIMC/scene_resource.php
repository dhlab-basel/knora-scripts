<?php

require_once "../../general/api/api_inc.php";

class SceneResource {

    /**
     * Main method.
     * @param $argv
     * @throws
     */
    static function main(array $argv) {

        $mode = self::getValueOfArgs($argv, "-mode", "json");
        $scene_id = self::getValueOfArgs($argv, "-scene_id");

        // Get resource_id from salsah.org
        $resourceIdBySceneId = new SceneResource();
        $resource_id = $resourceIdBySceneId->getResourceId($scene_id);

        $str = "";
        switch ($mode) {
            case "json":
                $str = $resourceIdBySceneId->getJson($scene_id, $resource_id);
                break;
            default:
                $str = $resourceIdBySceneId->getString($scene_id, $resource_id);
                break;

        }

        echo $str . "\n";

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
     * Gets json.
     * @param $scene_id
     * @param $resource_id
     * @return string
     */
    private function getJson(int $scene_id, int $resource_id): string {
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
    private function getString(int $scene_id, int $resource_id): string {
        $str = "-----\n";
        $str .= "Scene ID = " . $scene_id . "\n";
        $str .= "Resource ID = " . $resource_id . "\n";
        $str .= "-----";
        return $str;
    }

    /**
     * Gets json.
     * @param $argv
     * @param $param
     * @param $default
     * @return string
     * @throws
     */
    private static function getValueOfArgs(array $argv, string $param, $default = null) {
        for ($i = 0; $i < count($argv); $i++) {
            if ($argv[$i] === $param && isset($argv[$i + 1])) {
                return $argv[$i + 1];
            }
        }
        if ($default === null) throw new Exception("Argument -" . $param . " not found.");
    }

}

if (isset($argv) && isset($argv[0]) && $argv[0] === basename(__FILE__)) {
    SceneResource::main($argv);
}
