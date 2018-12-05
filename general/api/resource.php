<?php

class Resource {

    /**
     * Deletes any resource by its id.
     * @param int $resource_id
     * @param string $auth
     * @throws Exception
     */
    function deleteByResourceId(int $resource_id, string $auth) {

        $url = "/resources/" . $resource_id;

        $salsahRequest = new SalsahRequest();
        $salsahResponse = $salsahRequest->delete($url, $auth, "resource_id: " . $resource_id);

        $jsonArray = $salsahResponse->body;

        if (isset($jsonArray["status"]) === false) {
            throw new Exception("500: Unknown error", 500);
        } else if ($jsonArray["status"] !== 0) {
            throw new Exception("Status " . $jsonArray["status"] . ": " . $jsonArray["errormsg"], $jsonArray["status"]);
        }

    }

    /**
     * Gets json.
     * @param $resource_id
     * @return string
     */
    protected function getJson(int $resource_id): string {
        $array = [
            "resource_id" => $resource_id,
        ];
        return json_encode($array);
    }

    /**
     * Gets string.
     * @param $resource_id
     * @return string
     */
    protected function getString(int $resource_id): string {
        $str = "-----\n";
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
    protected static function getValueOfArgs(array $argv, string $param, $default = null): string {
        for ($i = 0; $i < count($argv); $i++) {
            if ($argv[$i] === $param && isset($argv[$i + 1])) {
                return $argv[$i + 1];
            }
        }
        if ($default === null) throw new Exception("Argument " . $param . " not found.");
        return $default;
    }

}