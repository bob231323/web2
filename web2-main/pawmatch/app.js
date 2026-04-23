/**
 * app.js
 * Person 7 | IS333 Spring 2026
 *
 * Client-side glue between the frontend UI (validation.js) and the
 * traditional PHP form submission backend (DB_Ops.php).
 *
 * Responsibilities:
 *  - Intercept Add / Edit form submits → run validation before allowing POST
 *  - Handle delete → submit hidden form to DB_Ops.php via index.php
 *  - Show toast messages from URL query params (?msg=created|updated|deleted|error)
 *  - Client-side search/filter on the already-rendered pet cards (no AJAX)
 */

"use strict";

/* ════════════════════════════════════════════
   1. TOAST FROM URL PARAMS
   After a form submission, DB_Ops.php redirects back to
   index.php?msg=created|updated|deleted|error&detail=...
   We read those params and show the appropriate toast.
════════════════════════════════════════════ */
document.addEventListener("DOMContentLoaded", function () {
    const params = new URLSearchParams(window.location.search);
    const msg    = params.get("msg");
    const detail = params.get("detail");

    if (msg === "created") {
        showSuccess("Pet added successfully!");
    } else if (msg === "updated") {
        showSuccess("Pet updated successfully!");
    } else if (msg === "deleted") {
        showSuccess("Pet deleted successfully!");
    } else if (msg === "error") {
        showError(detail ? decodeURIComponent(detail) : "Something went wrong. Please try again.");
    }

    // Clean the URL so refresh doesn't re-show the toast
    if (msg) {
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});

/* ════════════════════════════════════════════
   2. FORM SUBMIT INTERCEPTORS
   Run client-side validation (from validation.js) before
   allowing the native form POST to go through to DB_Ops.php.
════════════════════════════════════════════ */
document.addEventListener("DOMContentLoaded", function () {

    // ── Add Pet form ─────────────────────────────────────────────────────────
    const addForm = document.getElementById("add-pet-form");
    if (addForm) {
        addForm.addEventListener("submit", function (e) {
            const isValid = validateAddForm();   // from validation.js
            if (!isValid) {
                e.preventDefault();              // block submission if invalid
                return;
            }
            setLoading("add", true);             // show spinner while submitting
        });
    }

    // ── Edit Pet form ─────────────────────────────────────────────────────────
    const editForm = document.getElementById("edit-pet-form");
    if (editForm) {
        editForm.addEventListener("submit", function (e) {
            const isValid = validateEditForm();  // from validation.js
            if (!isValid) {
                e.preventDefault();
                return;
            }
            setLoading("edit", true);
        });
    }
});

/* ════════════════════════════════════════════
   3. DELETE — submit hidden form
   validation.js calls window.onDeleteConfirmed(petId)
   when the user confirms deletion. We create a hidden form
   and POST it to index.php (which is handled by DB_Ops.php).
════════════════════════════════════════════ */
window.onDeleteConfirmed = function (petId) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "index.php";

    const actionInput  = document.createElement("input");
    actionInput.type   = "hidden";
    actionInput.name   = "action";
    actionInput.value  = "delete";

    const idInput      = document.createElement("input");
    idInput.type       = "hidden";
    idInput.name       = "id";
    idInput.value      = petId;

    form.appendChild(actionInput);
    form.appendChild(idInput);
    document.body.appendChild(form);
    form.submit();
};

/* ════════════════════════════════════════════
   4. CLIENT-SIDE SEARCH / FILTER
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

    let visibleCount = 0;

    _allCards.forEach(card => {
        const name  = (card.querySelector(".pet-card-name")?.textContent  || "").toLowerCase();
        const meta  = (card.querySelector(".pet-card-meta")?.textContent  || "").toLowerCase();
        const badge = (card.querySelector(".pet-card-type")?.textContent  || "").toLowerCase();

        const matchesQuery = !q || name.includes(q) || meta.includes(q);
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
