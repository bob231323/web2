/**
 * API_Ops.js
 * Created by: Bassant Tarek
 * Updated to integrate with validation.js UX helpers (Person 8)
 * Purpose: AJAX bridge between frontend and API_Ops.php (Pets fun facts)
 */

/** List of pets shown in order */
let pets = ["cat", "dog", "bird"];

/** Tracks which pet is currently shown */
let petIndex = 0;

/**
 * Fetch a pet fact from the PHP backend and update the UI.
 * Never reloads the page — pure AJAX.
 */
async function loadPet() {
    const pet = pets[petIndex % pets.length];

    try {
        const response = await fetch("API_Ops.php?pet=" + pet);

        if (!response.ok) {
            throw new Error("Server returned " + response.status);
        }

        const data = await response.json();

        // Update fact text
        const factEl = document.getElementById("fact");
        if (factEl) {
            factEl.style.opacity = "0";
            setTimeout(() => {
                factEl.textContent  = data.fact || "No fact available.";
                factEl.style.opacity = "1";
            }, 250);
        }

        // Update image
        const imgEl = document.getElementById("image");
if (imgEl && data.image) {
    imgEl.style.opacity = "0";
    imgEl.style.display = "block";
    imgEl.style.width = "100%";
    imgEl.style.height = "100%";
    imgEl.style.objectFit = "cover";
    setTimeout(() => {
        imgEl.src = "/pawmatch/" + data.image;
        imgEl.style.opacity = "1";
    }, 250);
}

        // Update pet label
        const petEl = document.getElementById("pet");
        if (petEl) {
            petEl.textContent = pet.charAt(0).toUpperCase() + pet.slice(1) + " Fact";
        }

        // Sync progress dots (from validation.js)
        if (typeof syncFactDots === "function") {
            syncFactDots(petIndex);
        }

    } catch (error) {
        const factEl = document.getElementById("fact");
        if (factEl) {
            factEl.textContent = "Unable to load fact. Please try again later.";
        }
    }

    petIndex++;
}

// Load first fact immediately on page load
loadPet();

// Rotate every 5 seconds
setInterval(loadPet, 5000);
