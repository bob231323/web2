/**
 * List of available pets that will be cycled through in the UI.
 * Each pet is sent to the backend API to fetch its fact and image.
 */
let pets = ["cat", "dog", "bird"];

/**
 * Current index used to cycle through pets array.
 * Increases on every API call.
 */
let index = 0;

/**
 * Fetches pet data from the backend API and updates the UI.
 *
 * Flow:
 * - Selects the next pet in rotation
 * - Sends request to Laravel API (/api/pet)
 * - Receives JSON response (fact + image)
 * - Updates DOM elements dynamically
 * - Handles errors gracefully if API fails
 *
 * @async
 * @function loadPet
 */
async function loadPet() {
    try {
        let pet = pets[index % pets.length];

        const response = await fetch("/api/pet?pet=" + pet);

        if (!response.ok) {
            throw new Error("API request failed");
        }

        const data = await response.json();

        // Update UI elements with API data
        document.getElementById("fact").innerText = data.fact;
        document.getElementById("image").src = data.image;
        document.getElementById("pet").innerText = pet.toUpperCase();

        index++;

    } catch (error) {
        // Fallback UI message when API is unavailable
        document.getElementById("fact").innerText =
            "API is unavailable. Please try again later.";
    }
}

/**
 * Initial API call when page loads
 */
loadPet();

/**
 * Automatically refresh pet data every 3 seconds
 * to create a dynamic changing UI experience.
 */
setInterval(loadPet, 3000);
