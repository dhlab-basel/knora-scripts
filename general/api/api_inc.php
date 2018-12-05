<?php

/**
 * Class SalsahResponse.
 */
class SalsahResponse {

    /**
     * @var int
     */
    public $responseCode = 0;

    /**
     * @var string
     */
    public $responseString = "";

    /**
     * @var string
     */
    public $contentType = "";

    /**
     * @var array the full headers data
     */
    public $headers = [];

    /**
     * @var array the json object
     */
    public $body = [];


    public function __construct(array $responseHeader, $result) {
        $this->saveHeaders($responseHeader);
        $this->saveResult($result);
    }

    /**
     * Saves the headers
     * @param array $responseHeader
     */
    private function saveHeaders(array $responseHeader) {

        if (\count($responseHeader) === 0) return;

        $head = array();

        foreach($responseHeader as $k => $v) {
            $t = explode( ':', $v, 2 );
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)\s(([A-Za-z]+\s?)+)#", $v, $out)) {
                    $head["responseCode"] = intval($out[1]);
                    $this->responseCode = $head["responseCode"];
                    $this->responseString = $out[2];
                }
            }
        }

        $this->headers = $head;
        $this->contentType = $head["Content-Type"];

    }

    /**
     * Saves the result.
     * @param $result
     */
    private function saveResult($result) {

        if ($result === false) return;

        $body = \json_decode($result, true);

        if (is_array($body)) $this->body = $body;

    }

}

/**
 * Class SalsahRequest.
 */
class SalsahRequest {

    const API_URL = "http://www.salsah.org/api";

    private const METHOD_GET = "GET";
    private const METHOD_POST = "POST";
    private const METHOD_PUT = "PUT";
    private const METHOD_DELETE = "DELETE";

    /**
     * SalsahRequest constructor.
     */
    function __construct() {}

    /**
     * Performs a HTTP GET Salsah request with basic auth.
     * @param string $url
     * @param string $auth
     * @param string $message
     * @return SalsahResponse
     */
    function get(string $url, string $auth = "", string $message = ""): SalsahResponse {
        return $this->makeJsonRequest(self::METHOD_GET, $auth, $url, [], $message);
    }

    /**
     * Performs a HTTP POST Salsah request with basic auth.
     * @param string $url
     * @param array $data
     * @param string $auth
     * @param string $message
     * @return SalsahResponse
     */
    function post(string $url, array $data, string $auth = "", string $message = ""): SalsahResponse {
        return $this->makeJsonRequest(self::METHOD_POST, $auth, $url, $data, $message);
    }

    /**
     * Performs a HTTP POST Salsah request with basic auth, including an image.
     * @param string $url
     * @param array  $data
     * @param string $filePath
     * @param string $auth
     * @param string $message
     * @return SalsahResponse
     */
    function postWithFile(string $url, array $data, string $filePath, string $auth, string $message = ""): SalsahResponse {

        echo "POST" . " " . $url . "\t \t" . $message;

        $cid = curl_init(self::API_URL . $url);
        curl_setopt($cid, CURLOPT_HTTPHEADER, [
            'Content-Type: multipart/form-data',
            "Authorization: Basic " . $auth
        ]);
        curl_setopt($cid, CURLOPT_VERBOSE, 1);
        curl_setopt($cid, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($cid, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //curl_setopt($cid, CURLOPT_USERPWD, $GLOBALS['username'].':'.$GLOBALS['password']);
        curl_setopt($cid, CURLOPT_POST, true);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        curl_setopt($cid, CURLOPT_POSTFIELDS, [
            "json" => \json_encode($data),
            "file" => new CURLFile($filePath, $mimetype)
        ]);

        $response = curl_exec($cid);

        $header_size = curl_getinfo($cid, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

        curl_close($cid);

        /*

        // Do the HTTP request
        $opts = ["http" =>
            [
                "method"  => "POST",
                "header"  => "Content-type: multipart/form-data\r\nAuthorization: Basic " . $auth,
                "content" => http_build_query([
                    "data" => ( \count($data) > 0 ? \json_encode($data) : "{}" ),
                    "file" => $file
                ])
            ]
        ];
        $context = stream_context_create($opts);
        $result = @file_get_contents(self::API_URL . $url, false, $context);
*/


        // Save the result in object
        var_dump(\json_decode($body));
        $salsahResponse = new SalsahResponse(\explode($header, "\n"), $body);

        echo "\t \t" . $salsahResponse->responseCode . " " . $salsahResponse->responseString . "\n";

        return $salsahResponse;

    }

    /**
     * Performs a HTTP PUT Salsah request with basic auth.
     * @param string $url
     * @param array $data
     * @param string $auth
     * @param string $message
     * @return SalsahResponse
     */
    function put(string $url, array $data, string $auth = "", string $message = ""): SalsahResponse {
        return $this->makeJsonRequest(self::METHOD_PUT, $auth, $url, $data, $message);
    }

    /**
     * Performs a HTTP DELETE Salsah request with basic auth.
     * @param string $url
     * @param string $auth
     * @param string $message
     * @return SalsahResponse
     */
    function delete(string $url, string $auth = "", string $message = ""): SalsahResponse {
        return $this->makeJsonRequest(self::METHOD_DELETE, $auth, $url, [], $message);
    }

    /**
     * Makes the Salsah request.
     * @param string $method
     * @param string $auth
     * @param string $url
     * @param array  $data
     * @param string $message
     * @return SalsahResponse
     */
    protected function makeJsonRequest(string $method, string $auth, string $url, array $data = [], string $message = ""): SalsahResponse {

        echo $method . " " . $url . "\t \t" . $message;

        // Do the HTTP request
        $opts = ["http" =>
            [
                "method"  => $method,
                "header"  => "Content-type: application/json\r\nAuthorization: Basic " . $auth,
                "content" => \count($data) > 0 ? \json_encode($data) : ""
            ]
        ];
        $context = stream_context_create($opts);
        $result = @file_get_contents(self::API_URL . $url, false, $context);

        // Save the result in object
        $salsahResponse = new SalsahResponse($http_response_header, $result);

        echo "\t \t" . $salsahResponse->responseCode . " " . $salsahResponse->responseString . "\n";

        return $salsahResponse;

    }

}

?>
