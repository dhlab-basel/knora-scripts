<?php

/**
 * Main method.
 * @param $argv
 */
function post(int $resourceId, string $propertyName, $value) {

    $mode = getValueOfArgs($argv, "-mode" , "json");
    $project_id = getValueOfArgs($argv, "-project_id");
    $resource_id = getValueOfArgs($argv, "-resource_id");
    $handle_id = sprintf(ARKSTR, ArkId_FromResId($project_id, $resource_id));

    $str = "";
    switch ($mode) {
        case "json":
            $str = getJson($project_id, $resource_id, $handle_id);
            break;
        default:
            $str = getString($project_id, $resource_id, $handle_id);
            break;

    }

    echo $str;

}

/**
 * Gets json.
 * @param $project_id
 * @param $resource_id
 * @param $handle_id
 * @return string
 */
function getJson(int $project_id, int $resource_id, string $handle_id): string {
    $array = [
        "project_id" => $project_id,
        "resource_id" => $resource_id,
        "handle_id" => $handle_id
    ];
    return json_encode($array);
}

/**
 * Gets string.
 * @param $project_id
 * @param $resource_id
 * @param $handle_id
 * @return string
 */
function getString(int $project_id, int $resource_id, string $handle_id): string {
    $str = "-----\n";
    $str .= "Project ID = " . $project_id . "\n";
    $str .= "Resource ID = " . $resource_id . "\n";
    $str .= "Handle ID = " . sprintf(ARKSTR, ArkId_FromResId($project_id, $resource_id)) . "\n";
    $str .= "-----\n";
    return $str;
}

/**
 * Gets json.
 * @param $argv
 * @param $param
 * @param $default
 * @return string
 * @throws Exception
 */
function getValueOfArgs(array $argv, string $param, $default = null) {
    for ($i = 0; $i < count($argv); $i++) {
        if ($argv[$i] === $param && isset($argv[$i+1])) {
            return $argv[$i+1];
        }
    }
    if ($default === null) throw new Exception("Argument -" . $param . " not found.");
}

main(isset($argv) ? $argv : []);





/*
$salsahRequest = new SalsahRequest();

//$salsahResponse1 = $salsahRequest->get("/resources/2072622");
//$salsahResponse2 = $salsahRequest->get("/resources/20726220");
$salsahResponse3 = $salsahRequest->post("/values/", [
    "res_id" => 2072622,
    "value_arr" => [
        [
            "prop" => "limc:url",
            "value" => "test"
        ]
    ]
], "");
var_dump($salsahResponse3->body["retvals"][0]["id"]);
//$salsahResponse4 = $salsahRequest->delete("/values/11898116", "")


//var_dump(post("/values/", [], "=="));
//var_dump(delete("/values/11760515", "=="));*/



?>
