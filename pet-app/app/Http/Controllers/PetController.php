<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PetService;
use App\Models\Pet;
use Illuminate\Support\Facades\Storage;

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
    public function create()
    {
        return view('pets.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'breed' => 'nullable|string|max:255',
                'age' => 'required|integer|min:0',
                'description' => 'required|string',
                'image' => 'nullable|file|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first(),
                    'errors' => $e->validator->errors()
                ], 422);
            }
            throw $e;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('pets', 'public');
            $validated['image_path'] = $path;
        }

        $pet = Pet::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pet added successfully!',
                'pet' => $pet,
                'html' => view('components.pet-card', ['pet' => $pet, 'index' => 0])->render()
            ]);
        }

        return redirect()->route('pets.index')->with('success', 'Pet added successfully!');
    }

    public function index(Request $request)
    {
        $query = Pet::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('breed', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        $pets = $query->select(['id', 'name', 'type', 'breed', 'age', 'description', 'image_path'])
                      ->orderBy('created_at', 'desc')
                      ->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = '';
            foreach ($pets as $index => $pet) {
                $html .= view('components.pet-card', ['pet' => $pet, 'index' => $index])->render();
            }

            return response()->json([
                'success' => true,
                'count' => $pets->count(),
                'html' => $html,
                'pets' => $pets
            ]);
        }

        return view('pets.index', compact('pets'));
    }

    // update pet --> fot data base 
    public function update(Request $request, Pet $pet)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'breed' => 'nullable|string|max:255',
                'age' => 'required|integer|min:0',
                'description' => 'required|string',
                'image' => 'nullable|file|max:2048',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first(),
                    'errors' => $e->validator->errors()
                ], 422);
            }
            throw $e;
        }

        if ($request->hasFile('image')) {
            if ($pet->image_path && Storage::disk('public')->exists($pet->image_path)) {
                Storage::disk('public')->delete($pet->image_path);
            }
            $path = $request->file('image')->store('pets', 'public');
            $validated['image_path'] = $path;
        }

        $pet->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pet updated successfully!',
                'pet' => $pet,
                'html' => view('components.pet-card', ['pet' => $pet, 'index' => 0])->render()
            ]);
        }

        return redirect()->route('pets.index')->with('success', 'Pet updated successfully!');
    }

    // destory 
    public function destroy(Request $request, Pet $pet)
    {
        if ($pet->image_path && Storage::disk('public')->exists($pet->image_path)) {
            Storage::disk('public')->delete($pet->image_path);
        }

        $pet->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pet deleted successfully!'
            ]);
        }

        return redirect()->route('pets.index')->with('success', 'Pet deleted successfully!');
    }

}
