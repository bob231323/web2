"use strict";

/* ════════════════════════════════════════════
  1. FORM SUBMIT INTERCEPTORS
     Forms now submit normally to Laravel (no AJAX intercept)
     Laravel handles redirect + flash message after store/update
════════════════════════════════════════════ */
document.addEventListener("DOMContentLoaded", function () {

    // ── Add Pet form ──────────────────────────────────────────────────────────
    const addForm = document.getElementById("add-pet-form");
    if (addForm) {
        addForm.addEventListener("submit", function (e) {
            const isValid = validateAddForm(); // from validation.js
            if (!isValid) {
                e.preventDefault(); // stop submit only if invalid
                return;
            }
            setLoading("add", true);
            // Let the form submit normally to Laravel route (pets.store)
        });
    }

    // ── Edit Pet form ─────────────────────────────────────────────────────────
    const editForm = document.getElementById("edit-pet-form");
    if (editForm) {
        editForm.addEventListener("submit", function (e) {
            const isValid = validateEditForm(); // from validation.js
            if (!isValid) {
                e.preventDefault();
                return;
            }
            setLoading("edit", true);
            // Let the form submit normally to Laravel route (pets.update)
        });
    }
});

/* ════════════════════════════════════════════
  2. DELETE
     Uses hidden form with @csrf + @method('DELETE')
     submitted to Laravel route (pets.destroy)
════════════════════════════════════════════ */
window.onDeleteConfirmed = function (petId) {
    const form = document.getElementById("delete-form");
    if (!form) return;

    form.action = "/pets/" + petId;
    form.submit();
};

/* ════════════════════════════════════════════
  3. CLIENT-SIDE SEARCH / FILTER
     Filters already-rendered Blade pet cards
════════════════════════════════════════════ */

let _allCards = [];

document.addEventListener("DOMContentLoaded", function () {
    _allCards = Array.from(document.querySelectorAll("#pets-grid .pet-card"));
});

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
        const breed = meta.includes("·") ? meta.split("·")[0].trim() : "";

        const matchesQuery = hasOnlyAgeUnit
            ? false
            : (!normalizedQuery || name.includes(normalizedQuery) || breed.includes(normalizedQuery) || desc.includes(normalizedQuery) || badge.includes(normalizedQuery));
        const matchesType = !t || badge.includes(t);

        if (matchesQuery && matchesType) {
            card.style.display = "";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    updatePetCount(visibleCount);

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

window.onClear = function () {
    _allCards.forEach(card => { card.style.display = ""; });
    updatePetCount(_allCards.length);
    const searchEmpty = document.getElementById("search-empty");
    if (searchEmpty) searchEmpty.remove();
};