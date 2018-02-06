<?php

require_once "../../general/api/api_inc.php";
require_once "get_resource_id_by_monument_id.php";

class LinksToMonument {

    /**
     * Main method.
     * @param $argv
     * @throws Exception
     */
    static function main(array $argv) {

        $url = self::getValueOfArgs($argv, "-url", null);
        $path = self::getValueOfArgs($argv, "-path", null);
        $auth = self::getValueOfArgs($argv, "-auth", null);

        switch ($url) {

            case "inscription":
            case "museum":
            case "monument":

                $pltm = new LinksToMonument();
                $array = $pltm->getJsonArrayFromPath($path);
                $pltm->postDataToSalsahMonument("limc:" . $url . "Url", $array, $auth);

                break;

            default:
                throw new Exception("Invalid argument -url with value " . $url);
                break;

        }

    }

    /**
     * Gets a json array from the json file path.
     * @param $path
     * @return array
     * @throws Exception
     */
    private function getJsonArrayFromPath($path): array {

        echo "-\nReading JSON file";

        $exception = new Exception("Json file does not exist or is invalid, must contain [ { \"id\": [number], \"url\": [string] }, ... ].");

        // Check if file exists
        if (file_exists($path) === false) {
            throw $exception;
        }

        // Make general json check
        $string = file_get_contents($path);
        if ($string === false) {
            throw $exception;
        }

        // Get json array
        $array = \json_decode($string, true);
        if (is_array($array) === false) {
            throw $exception;
        }

        // Do validation
        foreach ($array as $object) {
            if (isset($object["id"]) === false || isset($object["url"]) === false) {
                throw $exception;
            }
        }

        echo "\nDone!\n";

        // Return the validated array
        return $array;

    }

    /**
     * Posts the data from the json array to Salsah.
     * @param string $propertyName the property name of Salsah, for example limc:url
     * @param array  $array the data array of format [["id" => int, "url" => string], ...]
     * @param string $auth
     * @throws Exception
     */
    private function postDataToSalsahMonument(string $propertyName, array $array, string $auth) {

        echo "-\nStarting HTTP requests\n";

        foreach ($array as $object) {

            // Get resource id
            $ribmi = new ResourceIdByMonumentId();
            $resource_id = $ribmi->getResourceId($object["id"]);

            // Make salsah post request
            $salsahRequest = new SalsahRequest();
            $salsahResponse = $salsahRequest->post("/values/", [
                "res_id" => $resource_id,
                "value_arr" => [
                    [
                        "prop" => $propertyName,
                        "value" => $object["url"]
                    ]
                ]
            ], $auth, "resource_id: " . $resource_id . ", id: " . $object["id"] . ", url: " . $object["url"]);

        }

        echo "Done!\n";

    }

    /**
     * Gets json.
     * @param $argv
     * @param $param
     * @param $default
     * @return string
     * @throws Exception
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
    LinksToMonument::main($argv);
}