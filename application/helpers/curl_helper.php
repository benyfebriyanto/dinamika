<?php

function perform_http_request($method, $url, $data)
{
    $curl = curl_init();
    $headers = array(
        'Content-Type:application/json',
    );

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }

            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);

            break;
        default:
            if ($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //If SSL Certificate Not Available, for example, I am calling from http://localhost URL

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function _GET($url, $header = array())
{
    $curl = curl_init(); //Initializes a cURL session and returns a cURL handler.
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => FAlSE,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    curl_setopt_array($curl, $options);

    //get headers response
    curl_setopt(
        $curl,
        CURLOPT_HEADERFUNCTION,
        function ($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid headers
                return $len;

            $headers[strtolower(trim($header[0]))][] = trim($header[1]);

            return $len;
        }
    );

    $response = curl_exec($curl); //Executes the cURL session.
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl); //Closes the session and deletes the variable (recommended use)

    // var_dump($response);
    // exit();
    // Creating an object
    $res = new stdClass();

    // Property added to the object
    $res->httpcode = $httpcode;
    $res->token_expired = isset($headers['is-token-expired'][0]);
    $res->response = $response;

    return $res;
}

function _POST($url, $body, $token = "", $headers = array())
{
    //Initialize cURL extension
    $curl = curl_init();

    //Initialize Headers
    $header = array(
        "Accept: application/json",
        "Content-Type: application/json",
        sprintf("Authorization : Bearer %s", $token),
        sprintf('Ocp-Apim-Subscription-Key: %s', API_SUBSCRIPTION_KEY)
    );
    //All options in an array
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_SSL_VERIFYPEER => FAlSE,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];

    //Set the Options array.
    curl_setopt_array($curl, $options);

    //get headers response
    curl_setopt(
        $curl,
        CURLOPT_HEADERFUNCTION,
        function ($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid headers
                return $len;

            $headers[strtolower(trim($header[0]))][] = trim($header[1]);

            return $len;
        }
    );

    //Run cURL request
    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    //If error occurs
    if (curl_errno($curl)) {
        echo "CURL ERROR - " . curl_error($curl);
    }

    //If no errors
    else {
        // Creating an object
        $res = new stdClass();

        // Property added to the object
        $res->httpcode = $httpcode;
        $res->token_expired = isset($headers['is-token-expired'][0]);
        $res->response = $response;

        return $res;
    }

    //Close to remove $curl from memory
    curl_close($curl);
}

function GenerateNewToken()
{
    $CI = &get_instance();
    $accessToken = $CI->input->cookie('access_token', TRUE);
    $refreshToken = $CI->input->cookie('refresh_token', TRUE);

    $token = json_encode(array(
        "accessToken" => $accessToken,
        "refreshToken" => $refreshToken,
    ));

    $res = _POST(API_RefreshToken, $token);

    if ($res->httpcode == 200) {
        $response = json_decode($res->response);
        setcookie("access_token", $response->accessToken, strtotime("+60 minutes"), "/");
    }

    return $response->accessToken;
}

function CheckTokenExpiration()
{
    $CI = &get_instance();
    $CI->load->helper('curl');

    $accessToken = $CI->input->cookie('access_token', TRUE);

    $headers = array(
        sprintf("Authorization : Bearer %s", $accessToken),
        sprintf('Ocp-Apim-Subscription-Key: %s', API_SUBSCRIPTION_KEY)

    );

    $res = _GET(API_CheckToken, $headers);

    if ($res->token_expired == 1) {
        $newToken = GenerateNewToken();
    } else {
        $newToken = $accessToken;
    }

    return $newToken;
    // return $accessToken;
}
