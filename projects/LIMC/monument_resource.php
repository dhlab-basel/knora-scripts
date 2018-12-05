<?php

require_once "../../general/api/api_inc.php";

class MonumentResource {

    /**
     * Main method.
     * @param $argv
     * @throws
     */
    static function main(array $argv) {

        $mode = self::getValueOfArgs($argv, "-mode", "json");
        $monument_id = self::getValueOfArgs($argv, "-monument_id");

        // Get resource_id from salsah.org
        $resourceIdByMonumentId = new MonumentResource();
        $resource_id = $resourceIdByMonumentId->getResourceId($monument_id);

        $str = "";
        switch ($mode) {
            case "json":
                $str = $resourceIdByMonumentId->getJson($monument_id, $resource_id);
                break;
            default:
                $str = $resourceIdByMonumentId->getString($monument_id, $resource_id);
                break;

        }

        echo $str . "\n";

    }

    /**
     * Gets the resource id from the salsah.org server.
     * @param $monument_id
     * @return int
     * @throws
     */
    function getResourceId(int $monument_id): int {

        $url = "/search/?searchtype=extended&property_id%5B%5D=619&compop%5B%5D=EQ&searchval%5B%5D=" . $monument_id . "&show_nrows=1&start_at=0&filter_by_restype=70";

        $salsahRequest = new SalsahRequest();
        $salsahResponse = $salsahRequest->get($url, "", "monument_id: " . $monument_id);

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
     * @param $monument_id
     * @param $resource_id
     * @return string
     */
    private function getJson(int $monument_id, int $resource_id): string {
        $array = [
            "resource_id" => $resource_id,
            "monument_id" => $monument_id
        ];
        return json_encode($array);
    }

    /**
     * Gets string.
     * @param $monument_id
     * @param $resource_id
     * @return string
     */
    private function getString(int $monument_id, int $resource_id): string {
        $str = "-----\n";
        $str .= "Monument ID = " . $monument_id . "\n";
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
    MonumentResource::main($argv);
}
