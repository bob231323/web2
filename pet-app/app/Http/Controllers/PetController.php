<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PetService;

/**
 * Class PetController
 *
 * Handles HTTP requests related to pets and returns pet facts
 * using the PetService (third-party API integration).
 *
 * Responsibilities:
 * - Receive pet type from request
 * - Call service layer to fetch data
 * - Return JSON response to frontend
 * - Handle API failures gracefully
 */
class PetController extends Controller
{
    /**
     * Service instance used to interact with external pet API.
     *
     * @var PetService
     */
    private PetService $petService;

    /**
     * PetController constructor.
     *
     * Injects the PetService dependency using Laravel's service container.
     *
     * @param PetService $petService Service responsible for fetching pet data
     */
    public function __construct(PetService $petService)
    {
        $this->petService = $petService;
    }

    /**
     * Retrieve pet information (fact + image) based on query parameter.
     *
     * Example request:
     *  GET /api/pet?pet=cat
     *
     * Flow:
     * - Reads "pet" from query string (default = cat)
     * - Calls PetService to fetch data from external API
     * - Returns JSON response
     * - If API fails, returns fallback message and default image
     *
     * @param Request $request HTTP request containing query parameters
     * @return \Illuminate\Http\JsonResponse JSON response with pet data or error fallback
     */
    public function getPet(Request $request)
    {
        $pet = $request->query('pet', 'cat');

        try {
            $data = $this->petService->getPet($pet);

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                "pet" => $pet,
                "fact" => "API is currently unavailable. Please try again later.",
                "image" => asset("img/cat.jpg")
            ], 500);
        }
    }
}
