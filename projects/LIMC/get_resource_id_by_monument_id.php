<?php

/**
 * Main method.
 * @param $argv
 */
function main(array $argv) {

	$mode = getValueOfArgs($argv, "-mode" , "json");
	$monument_id = getValueOfArgs($argv, "-monument_id");
	
	// Get resource_id from salsah.org
	$resource_id = getResourceId($monument_id);
	
	$str = "";
	switch ($mode) {
		case "json":
			$str = getJson($monument_id, $resource_id);
		break;
		default:
			$str = getString($monument_id, $resource_id);
		break;
	
	}
	
	echo $str;

}

/**
 * Gets the resource id from the salsah.org server.
 * @param $monument_id
 * @return int
 * @throws
 */
function getResourceId(int $monument_id): int {
	
	$url = "http://salsah.org/api/search/?searchtype=extended&property_id%5B%5D=619&compop%5B%5D=EQ&searchval%5B%5D=" . $monument_id . "&show_nrows=1&start_at=0&progvalfile=prog_945971.salsah&filter_by_restype=70";
	$jsonString = file_get_contents($url);

	$jsonArray = json_decode($jsonString, true);
	
	if (isset($jsonArray["subjects"][0]["obj_id"]) === false) {
		throw new Exception("");
	}

	return str_replace("_-_local", "", $jsonArray["subjects"][0]["obj_id"]);
	
}

/**
 * Gets json.
 * @param $monument_id
 * @param $resource_id
 * @return string
 */
function getJson(int $monument_id, int $resource_id): string {
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
function getString(int $monument_id, int $resource_id): string {
	$str = "-----\n";
	$str .= "Monument ID = " . $monument_id . "\n";
	$str .= "Resource ID = " . $resource_id . "\n";
	$str .= "-----\n";
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
function getValueOfArgs(array $argv, string $param, $default = null) {
	for ($i = 0; $i < count($argv); $i++) {
		if ($argv[$i] === $param && isset($argv[$i+1])) {
			return $argv[$i+1];
		}
	}
	if ($default === null) throw new Exception("Argument -" . $param . " not found.");
}


main(isset($argv) ? $argv : []);

?>
