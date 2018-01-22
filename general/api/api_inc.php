<?php

const API_URL = "http://www.salsah.org/api";

/**
 * Performs a HTTP GET Salsah request with basic auth.
 * @param string $url
 * @param string $auth
 * @return array|null
 */
function get(string $url, string $auth = ""): ?array {

    // Do the HTTP request
    $opts = array("http" =>
        array(
            "method"  => "GET",
            "header"  => "Content-type: application/json\r\nAuthorization: Basic " . $auth
        )
    );
    $context = stream_context_create($opts);
    $result = @file_get_contents(API_URL . $url, false, $context);

    if ($result === false) {
        return null;
    }

    return \json_decode($result, true);

}

/**
 * Performs a HTTP POST Salsah request with basic auth.
 * @param string $url
 * @param array $data
 * @param string $auth
 * @return array|null
 */
function post(string $url, array $data, string $auth = ""): ?array {

    $jsonString = \json_encode($data);

    // Do the HTTP request
    $opts = array("http" =>
        array(
            "method"  => "POST",
            "header"  => "Content-type: application/json\r\nAuthorization: Basic " . $auth,
            "content" => $jsonString
        )
    );
    $context = stream_context_create($opts);
    $result = file_get_contents(API_URL . $url, false, $context);
    var_dump($http_response_header);

    if ($result === false) {
        return null;
    }

    return \json_decode($result, true);

}

/**
 * Performs a HTTP PUT Salsah request with basic auth.
 * @param string $url
 * @param array $data
 * @param string $auth
 * @return array|null
 */
function put(string $url, array $data, string $auth = ""): ?array {

}

/**
 * Performs a HTTP DELETE Salsah request with basic auth.
 * @param string $url
 * @param string $auth
 * @return array|null
 */
function delete(string $url, string $auth = ""): ?array {

    // Do the HTTP request
    $opts = array("http" =>
        array(
            "method"  => "DELETE",
            "header"  => "Content-type: application/json\r\nAuthorization: Basic " . $auth
        )
    );
    $context = stream_context_create($opts);
    $result = @file_get_contents(API_URL . $url, false, $context);

    if ($result === false) {
        return null;
    }

    return \json_decode($result, true);

}


//var_dump(get("/resources/11760492"));
var_dump(post("/values/", [], "=="));
//var_dump(delete("/values/11760515", "=="));


?>
