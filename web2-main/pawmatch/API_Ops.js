// This file was created by student: Bassant Tarek
// Purpose: Demonstrates API handling using third-party pet APIs (Cats, Dogs, Birds)

/** List of pets that will be shown in order */
let pets = ["cat", "dog", "bird"];

/** This keeps track of which pet is currently shown */
let index = 0;

/** This function connects frontend to backend (PHP API) ,It fetches data and updates the HTML page dynamically */
async function loadPet() {
    try {
        // Select current pet based on index (rotates in loop)
        let pet = pets[index % pets.length];

        // Send request to PHP backend API
        const response = await fetch("API_Ops.php?pet=" + pet);

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
