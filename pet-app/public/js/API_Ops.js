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
        // Select current pet based on index (rotates in loop)
        let pet = pets[index % pets.length];

        // Send request to PHP backend API
        const response = await fetch("/api/pet?pet=" + pet);

        // Convert response from JSON string into JS object
        const data = await response.json();

        // Update HTML page with API data
        document.getElementById("fact").innerText = data.fact;
        document.getElementById("image").src = data.image;
        document.getElementById("pet").innerText = pet.toUpperCase();

        // Move to next pet for next request
        index++;

    } catch (error) {
        // If API fails or server is down
        document.getElementById("fact").innerText = " Unable to load data. Please try again later.";
    }
}

/** Load first pet immediately when page opens */
loadPet();

/**  Repeat function every 3 seconds
 * This creates live updating UI without refreshing page */
setInterval(loadPet, 3000);
