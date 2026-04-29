<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetController extends Controller
{
    private function callAPI($url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    public function getPet(Request $request)
    {
        $pet = $request->query('pet', 'cat');

        $data = [
            "pet" => $pet,
            "fact" => "No data available",
            "image" => asset("img/default.jpg")
        ];

        if ($pet == "cat") {
            $res = $this->callAPI("https://catfact.ninja/fact");

            if ($res && isset($res["fact"])) {
                $data["fact"] = $res["fact"];
            }

            $data["image"] = asset("img/cat.jpg");
        }

        elseif ($pet == "dog") {
            $res = $this->callAPI("https://dogapi.dog/api/v2/facts");

            if ($res && isset($res["data"][0]["attributes"]["body"])) {
                $data["fact"] = $res["data"][0]["attributes"]["body"];
            }

            $data["image"] = asset("img/dog.jpg");
        }

        else {
            $res = $this->callAPI("https://some-random-api.com/animal/bird");

            if ($res && isset($res["fact"])) {
                $data["fact"] = $res["fact"];
            }

            $data["image"] = asset("img/bird.jpg");
        }

        return response()->json($data);
    }
}
