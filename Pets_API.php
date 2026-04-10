<!--
    This file was created by student: Bassant Tarek
    Purpose: Demonstrates API handling using third-party pet APIs (Cats, Dogs, Birds)
-->

<?php
    header("Content-Type: application/json"); // returns JSON instead of HTML

    /** Get pet type from URL (example: ?pet=cat) */
    $pet = isset($_GET["pet"]) ? $_GET["pet"] : "cat";

    /** This function uses cURL to safely request data from third-party APIs */
    function callAPI($url) {

        $ch = curl_init(); // start cURL session

        curl_setopt_array($ch, [ // set options for request
            CURLOPT_URL => $url, // API endpoint
            CURLOPT_RETURNTRANSFER => true, // return response as string instead of printing
            CURLOPT_TIMEOUT => 2 // stop request after 2 seconds if no response
        ]);

        $response = curl_exec($ch); // execute API request

        # if cURL has an error (network/API failure)
        if (curl_errno($ch)) {
            curl_close($ch); // close connection
            return null; // return nothing (safe fail)
        }

        curl_close($ch); // close connection normally

        # convert JSON response into PHP array
        return json_decode($response, true);
    }

    /** This is what API returns if something fails */
    $data = [
        "pet" => $pet, // selected animal type
        "fact" => "No data available", // default message
        "image" => "img/default.jpg" // fallback image
    ];

    /** third party */
    if ($pet == "cat") {
        # call cat fact API
        $res = callAPI("https://catfact.ninja/fact");

        # check if response is valid and contains "fact"
        if ($res && isset($res["fact"])) {
            $data["fact"] = $res["fact"]; // store fact in response
        }

        # use local image for cat
        $data["image"] = "img/cat.jpg";
    }
    elseif ($pet == "dog") {
        $res = callAPI("https://dogapi.dog/api/v2/facts");

        if ($res && isset($res["data"][0]["attributes"]["body"])) {
            $data["fact"] = $res["data"][0]["attributes"]["body"];
        }

        $data["image"] = "img/dog.jpg";
    }
    else {
        $res = callAPI("https://some-random-api.com/animal/bird");

        if ($res && isset($res["fact"])) {
            $data["fact"] = $res["fact"];
        }

        $data["image"] = "img/bird.jpg";
    }

    /** If API failed and fact is still empty, show friendly message */
    if (!$data["fact"]) {
        $data["fact"] = "API is currently unavailable. Please try again later.";
    }

    /** Convert PHP array into JSON for frontend (JavaScript) */
    echo json_encode($data);
?>