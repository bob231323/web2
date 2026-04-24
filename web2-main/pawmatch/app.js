/**
 * Client-side glue between the frontend UI (validation.js) and the
 * AJAX backend flow (DB_Ops.php).
 *
 * Responsibilities:
 *  - Intercept Add / Edit form submits and send AJAX requests
 *  - Handle delete with AJAX
 *  - Refresh pets grid from backend JSON without page reload
 *  - Client-side search/filter on the rendered pet cards
 */

"use strict";

/* ════════════════════════════════════════════
  1. FORM SUBMIT INTERCEPTORS (AJAX)
════════════════════════════════════════════ */
document.addEventListener("DOMContentLoaded", function () {

    // ── Add Pet form ─────────────────────────────────────────────────────────
    const addForm = document.getElementById("add-pet-form");
    if (addForm) {
        addForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            const isValid = validateAddForm();   // from validation.js
            if (!isValid) {
                return;
            }
            setLoading("add", true);             // show spinner while submitting
            await submitPetForm(addForm, "add", "Pet added successfully!", closeAddModal);
        });
    }

    // ── Edit Pet form ─────────────────────────────────────────────────────────
    const editForm = document.getElementById("edit-pet-form");
    if (editForm) {
        editForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            const isValid = validateEditForm();  // from validation.js
            if (!isValid) {
                return;
            }
            setLoading("edit", true);
            await submitPetForm(editForm, "edit", "Pet updated successfully!", closeEditModal);
        });
    }
});

/* ════════════════════════════════════════════
  2. DELETE (AJAX)
   validation.js calls window.onDeleteConfirmed(petId)
  when the user confirms deletion.
════════════════════════════════════════════ */
window.onDeleteConfirmed = async function (petId) {
    const payload = new FormData();
    payload.append("action", "delete");
    payload.append("id", petId);

    try {
        // Send delete request without page reload.
        const response = await fetch("DB_Ops.php", {
            method: "POST",
            body: payload
        });
        const result = await response.json();
        if (result.status === "success") {
            await refreshPetsFromServer();
            showSuccess("Pet deleted successfully!");
        } else {
            showError(result.message || "Delete failed.");
        }
    } catch (error) {
        showError("Delete failed. Please try again.");
    }
};

/* ════════════════════════════════════════════
  3. CLIENT-SIDE SEARCH / FILTER
   validation.js calls window.onSearch({ query, type })
   and window.onClear(). We filter the already-rendered
   pet cards without a page reload.
════════════════════════════════════════════ */

/** All pet cards (cached after DOM is ready) */
let _allCards = [];

document.addEventListener("DOMContentLoaded", function () {
    // Cache all rendered pet cards from the PHP output
    _allCards = Array.from(document.querySelectorAll("#pets-grid .pet-card"));
});

/**
 * Filter cards matching query (name/breed) AND type.
 */
window.onSearch = function ({ query, type }) {
    const q = (query || "").toLowerCase().trim();
    const t = (type  || "").toLowerCase().trim();
    const normalizedQuery = q.replace(/\b(yr|yrs|year|years)\b/g, "").trim();
    const hasOnlyAgeUnit = !!q && !normalizedQuery;

    let visibleCount = 0;

    _allCards.forEach(card => {
        const name  = (card.querySelector(".pet-card-name")?.textContent  || "").toLowerCase();
        const meta  = (card.querySelector(".pet-card-meta")?.textContent  || "").toLowerCase();
        const desc  = (card.querySelector(".pet-card-desc")?.textContent  || "").toLowerCase();
        const badge = (card.querySelector(".pet-card-type")?.textContent  || "").toLowerCase();

        // meta is shown as "breed · age yrs"; keep only breed for search
        const breed = meta.includes("·") ? meta.split("·")[0].trim() : "";

        const matchesQuery = hasOnlyAgeUnit
            ? false
            : (!normalizedQuery || name.includes(normalizedQuery) || breed.includes(normalizedQuery) || desc.includes(normalizedQuery) || badge.includes(normalizedQuery));
        const matchesType  = !t || badge.includes(t);

        if (matchesQuery && matchesType) {
            card.style.display = "";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    updatePetCount(visibleCount);   // from validation.js

    // Show empty state if nothing matches
    const existingEmpty = document.querySelector("#pets-grid .empty-state");
    if (visibleCount === 0 && !existingEmpty) {
        const empty = document.createElement("div");
        empty.className = "empty-state";
        empty.id = "search-empty";
        empty.innerHTML = `
            <div class="empty-state-icon"></div>
            <h3>No pets found</h3>
            <p>Try a different name, breed, or type.</p>
        `;
        document.getElementById("pets-grid").appendChild(empty);
    } else if (visibleCount > 0) {
        const searchEmpty = document.getElementById("search-empty");
        if (searchEmpty) searchEmpty.remove();
    }
};

/**
 * Clear search — show all cards again.
 */
window.onClear = function () {
    _allCards.forEach(card => { card.style.display = ""; });
    updatePetCount(_allCards.length);   // from validation.js

    const searchEmpty = document.getElementById("search-empty");
    if (searchEmpty) searchEmpty.remove();
};

/* ════════════════════════════════════════════
  4. AJAX HELPERS
════════════════════════════════════════════ */
async function submitPetForm(form, prefix, successMessage, onSuccess) {
    try {
        // FormData includes text fields + selected image file.
        const formData = new FormData(form);
        const response = await fetch("DB_Ops.php", {
            method: "POST",
            body: formData
        });
        const result = await response.json();

        if (result.status === "success") {
            await refreshPetsFromServer();
            if (typeof onSuccess === "function") onSuccess();
            showSuccess(successMessage);
        } else {
            showError(result.message || "Something went wrong.");
        }
    } catch (error) {
        showError("Request failed. Please try again.");
    } finally {
        setLoading(prefix, false);
    }
}

async function refreshPetsFromServer() {
    // Pull fresh data from PHP API, then rebuild cards in-place.
    const response = await fetch("DB_Ops.php?action=list");
    const result = await response.json();

    if (result.status !== "success") {
        throw new Error(result.message || "Failed to load pets.");
    }

    renderPets(result.data || []);
    _allCards = Array.from(document.querySelectorAll("#pets-grid .pet-card"));
}
