<?php

namespace App\Services;

/**
 * Class PetService
 *
 * Handles all logic related to fetching pet data from external APIs.
 * Acts as a service layer between the controller and third-party APIs.
 *
 * Responsibilities:
 * - Make HTTP requests to external pet APIs
 * - Normalize API responses into a unified format
 * - Provide fallback values in case of failure
 */
class PetService
{
    /**
     * Executes a GET request to an external API using cURL.
     *
     * @param string $url The API endpoint URL
     * @return array|null Decoded JSON response as associative array, or null on failure
     */
    protected function callAPI($url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Fetches pet data (fact + image) based on pet type.
     *
     * Supported pets:
     * - cat
     * - dog
     * - bird (default case)
     *
     * Flow:
     * - Calls the appropriate external API based on pet type
     * - Extracts relevant fact from API response
     * - Assigns a corresponding local image
     * - Returns a unified structured response
     *
     * @param string $pet The type of pet requested (cat, dog, bird)
     * @return array Structured array containing:
     *               - pet (string)
     *               - fact (string)
     *               - image (string URL)
     */
    public function getPet($pet)
    {
        $data = [
            "pet" => $pet,
            "fact" => "No data available",
            "image" => asset("img/cat.jpg")
        ];

        try {
            switch ($pet) {

                case "cat":
                    $res = $this->callAPI(config('services.pet.cat'));

                    if ($res && isset($res["fact"])) {
                        $data["fact"] = $res["fact"];
                    }

                    $data["image"] = asset("img/cat.jpg");
                    break;

                case "dog":
                    $res = $this->callAPI(config('services.pet.dog'));

                    if ($res && isset($res["data"][0]["attributes"]["body"])) {
                        $data["fact"] = $res["data"][0]["attributes"]["body"];
                    }

                    $data["image"] = asset("img/dog.jpg");
                    break;

                default:
                    $res = $this->callAPI(config('services.pet.bird'));

                    if ($res && isset($res["fact"])) {
                        $data["fact"] = $res["fact"];
                    }

                    $data["image"] = asset("img/bird.jpg");
                    break;
            }

        } catch (\Exception $e) {
            $data["fact"] = "API unavailable";
        }

        return $data;
    }
}
