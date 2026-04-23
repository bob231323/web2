/**
 * validation.js
 * Person 8 | IS333 Spring 2026
 * Client-side validation, UX helpers, and UI utilities.
 *
 * PUBLIC API (Person 7 calls these):
 *   validateAddForm()       → boolean
 *   validateEditForm()      → boolean
 *   showSuccess(message)    → void
 *   showError(message)      → void
 *   setLoading(formId, bool)→ void
 *   openAddModal()          → void
 *   closeAddModal()         → void
 *   openEditModal(petData)  → void
 *   closeEditModal()        → void
 *   openConfirm(petId)      → void
 *   closeConfirm()          → void
 *   confirmDelete()         → void (calls window.onDeleteConfirmed if set)
 *   scrollToSection(id)     → void
 *   toggleMobileMenu()      → void
 *   handleSearch()          → void (calls window.onSearch if set)
 *   handleClear()           → void (calls window.onClear if set)
 *   renderPets(petsArray)   → void
 *   renderEmpty()           → void
 *   clearSkeletons()        → void
 *   updatePetCount(n)       → void
 *   handleFileSelect(input, prefix) → void
 *   removeFile(prefix)      → void
 */

"use strict";

/* ════════════════════════════════════════════
   CONSTANTS
════════════════════════════════════════════ */
const VALID_TYPES   = ["cat", "dog", "bird", "rabbit", "other"];
const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
const ALLOWED_TYPES = ["image/jpeg", "image/png", "image/jpg"];

/* ════════════════════════════════════════════
   INTERNAL HELPERS
════════════════════════════════════════════ */

/** Set a field as invalid and show an error message */
function _setInvalid(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    if (input)  { input.classList.add("invalid"); input.classList.remove("valid"); }
    if (error)  { error.textContent = message; }
}

/** Set a field as valid and clear any error */
function _setValid(inputId, errorId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    if (input)  { input.classList.remove("invalid"); input.classList.add("valid"); }
    if (error)  { error.textContent = ""; }
}

/** Clear all validation states for a form prefix (add | edit) */
function _clearValidation(prefix) {
    const fields = ["name", "type", "breed", "age", "description", "image"];
    fields.forEach(field => {
        const input = document.getElementById(`${prefix}-${field}`);
        const error = document.getElementById(`err-${prefix}-${field}`);
        if (input) { input.classList.remove("invalid", "valid"); }
        if (error) { error.textContent = ""; }
    });
}

/** Validate a single field and return true if valid */
function _validateField(prefix, field) {
    const inputId = `${prefix}-${field}`;
    const errorId = `err-${prefix}-${field}`;
    const input   = document.getElementById(inputId);
    if (!input) return true;

    const value = input.value.trim();

    switch (field) {
        case "name":
            if (!value) {
                _setInvalid(inputId, errorId, "Pet name is required.");
                return false;
            }
            if (value.length < 2) {
                _setInvalid(inputId, errorId, "Name must be at least 2 characters.");
                return false;
            }
            if (value.length > 100) {
                _setInvalid(inputId, errorId, "Name must be under 100 characters.");
                return false;
            }
            _setValid(inputId, errorId);
            return true;

        case "type":
            if (!value || !VALID_TYPES.includes(value)) {
                _setInvalid(inputId, errorId, "Please select a valid pet type.");
                return false;
            }
            _setValid(inputId, errorId);
            return true;

        case "breed":
            // Optional, but validate length if provided
            if (value.length > 100) {
                _setInvalid(inputId, errorId, "Breed must be under 100 characters.");
                return false;
            }
            _setValid(inputId, errorId);
            return true;

        case "age":
            if (!value && value !== "0") {
                _setInvalid(inputId, errorId, "Age is required.");
                return false;
            }
            const ageNum = Number(value);
            if (isNaN(ageNum) || !Number.isInteger(ageNum)) {
                _setInvalid(inputId, errorId, "Age must be a whole number.");
                return false;
            }
            if (ageNum < 0 || ageNum > 30) {
                _setInvalid(inputId, errorId, "Age must be between 0 and 30.");
                return false;
            }
            _setValid(inputId, errorId);
            return true;

        case "description":
            if (!value) {
                _setInvalid(inputId, errorId, "Description is required.");
                return false;
            }
            if (value.length < 10) {
                _setInvalid(inputId, errorId, "Description must be at least 10 characters.");
                return false;
            }
            if (value.length > 1000) {
                _setInvalid(inputId, errorId, "Description must be under 1000 characters.");
                return false;
            }
            _setValid(inputId, errorId);
            return true;

        default:
            return true;
    }
}

/** Validate image file input — returns true if valid (or no file selected) */
function _validateFile(prefix, required = false) {
    const inputId = `${prefix}-image`;
    const errorId = `err-${prefix}-image`;
    const input   = document.getElementById(inputId);
    if (!input || input.files.length === 0) {
        if (required) {
            _setInvalid(inputId, errorId, "Please select a photo.");
            return false;
        }
        return true; // optional — no file is fine
    }

    const file = input.files[0];

    if (!ALLOWED_TYPES.includes(file.type)) {
        _setInvalid(inputId, errorId, "Only JPG and PNG files are allowed.");
        return false;
    }

    if (file.size > MAX_FILE_SIZE) {
        _setInvalid(inputId, errorId, "File size must be under 2MB.");
        return false;
    }

    _setValid(inputId, errorId);
    return true;
}

/* ════════════════════════════════════════════
   PUBLIC: FORM VALIDATION
════════════════════════════════════════════ */

/**
 * Validate the Add Pet form.
 * @returns {boolean} true if all fields are valid
 */
function validateAddForm() {
    const nameOk  = _validateField("add", "name");
    const typeOk  = _validateField("add", "type");
    const breedOk = _validateField("add", "breed");
    const ageOk   = _validateField("add", "age");
    const descOk  = _validateField("add", "description");
    const fileOk  = _validateFile("add", false); // photo optional
    return nameOk && typeOk && breedOk && ageOk && descOk && fileOk;
}

/**
 * Validate the Edit Pet form.
 * @returns {boolean} true if all fields are valid
 */
function validateEditForm() {
    const nameOk  = _validateField("edit", "name");
    const typeOk  = _validateField("edit", "type");
    const breedOk = _validateField("edit", "breed");
    const ageOk   = _validateField("edit", "age");
    const descOk  = _validateField("edit", "description");
    const fileOk  = _validateFile("edit", false);  // photo optional on edit
    return nameOk && typeOk && breedOk && ageOk && descOk && fileOk;
}

/* Live validation — validate on blur */
(function attachLiveValidation() {
    document.addEventListener("DOMContentLoaded", () => {
        ["add", "edit"].forEach(prefix => {
            ["name", "type", "breed", "age", "description"].forEach(field => {
                const el = document.getElementById(`${prefix}-${field}`);
                if (!el) return;
                el.addEventListener("blur", () => _validateField(prefix, field));
                el.addEventListener("input", () => {
                    // Clear error on typing so user gets instant feedback
                    const errorId = `err-${prefix}-${field}`;
                    const error   = document.getElementById(errorId);
                    if (error && error.textContent) {
                        _validateField(prefix, field);
                    }
                });
            });

            const fileInput = document.getElementById(`${prefix}-image`);
            if (fileInput) {
                fileInput.addEventListener("change", () => _validateFile(prefix, false));
            }
        });
    });
})();

/* ════════════════════════════════════════════
   PUBLIC: TOAST NOTIFICATIONS
════════════════════════════════════════════ */

let _toastTimer = null;

/**
 * Show a green success toast.
 * @param {string} message
 */
function showSuccess(message) {
    _showToast("toast-success", "toast-success-msg", message);
}

/**
 * Show a red error toast.
 * @param {string} message
 */
function showError(message) {
    _showToast("toast-error", "toast-error-msg", message);
}

function _showToast(toastId, msgId, message) {
    const toast = document.getElementById(toastId);
    const msg   = document.getElementById(msgId);
    if (!toast || !msg) return;

    // Hide both first
    document.getElementById("toast-success").classList.remove("show");
    document.getElementById("toast-error").classList.remove("show");
    clearTimeout(_toastTimer);

    msg.textContent = message;
    toast.classList.add("show");

    _toastTimer = setTimeout(() => {
        toast.classList.remove("show");
    }, 3500);
}

/* ════════════════════════════════════════════
   PUBLIC: LOADING STATE
════════════════════════════════════════════ */

/**
 * Toggle loading state on a form's submit button.
 * @param {string} formPrefix - "add" or "edit"
 * @param {boolean} isLoading
 */
function setLoading(formPrefix, isLoading) {
    const btn = document.getElementById(`${formPrefix}-submit-btn`);
    if (!btn) return;
    if (isLoading) {
        btn.classList.add("loading");
        btn.disabled = true;
    } else {
        btn.classList.remove("loading");
        btn.disabled = false;
    }
}

/* ════════════════════════════════════════════
   PUBLIC: MODAL CONTROLS
════════════════════════════════════════════ */

/** Open the Add Pet modal */
function openAddModal() {
    _clearValidation("add");
    document.getElementById("add-pet-form").reset();
    removeFile("add");
    document.getElementById("add-modal-overlay").classList.add("open");
    document.getElementById("add-name").focus();
}

/** Close the Add Pet modal */
function closeAddModal() {
    document.getElementById("add-modal-overlay").classList.remove("open");
    setLoading("add", false);
}

/**
 * Open the Edit Pet modal pre-filled with pet data.
 * @param {Object} pet - pet object from DB { id, name, type, breed, age, description, image_path }
 */
function openEditModal(pet) {
    _clearValidation("edit");
    removeFile("edit");

    document.getElementById("edit-id").value          = pet.id          || "";
    document.getElementById("edit-name").value        = pet.name        || "";
    document.getElementById("edit-type").value        = pet.type        || "";
    document.getElementById("edit-breed").value       = pet.breed       || "";
    document.getElementById("edit-age").value         = pet.age         || "";
    document.getElementById("edit-description").value = pet.description || "";
    document.getElementById("edit-existing-image").value = pet.image_path || "";

    document.getElementById("edit-modal-overlay").classList.add("open");
    document.getElementById("edit-name").focus();
}

/** Close the Edit Pet modal */
function closeEditModal() {
    document.getElementById("edit-modal-overlay").classList.remove("open");
    setLoading("edit", false);
}

/* Close modal when clicking the dark overlay */
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("add-modal-overlay").addEventListener("click", function(e) {
        if (e.target === this) closeAddModal();
    });
    document.getElementById("edit-modal-overlay").addEventListener("click", function(e) {
        if (e.target === this) closeEditModal();
    });

    /* ESC key closes any open modal */
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeAddModal();
            closeEditModal();
            closeConfirm();
        }
    });
});

/* ════════════════════════════════════════════
   PUBLIC: DELETE CONFIRM DIALOG
════════════════════════════════════════════ */

let _pendingDeleteId = null;

/**
 * Show the delete confirmation dialog.
 * @param {number|string} petId
 */
function openConfirm(petId) {
    _pendingDeleteId = petId;
    document.getElementById("confirm-overlay").classList.add("open");
}

/** Close the confirm dialog without deleting */
function closeConfirm() {
    _pendingDeleteId = null;
    document.getElementById("confirm-overlay").classList.remove("open");
}

/**
 * Called when user clicks "Yes, Delete".
 * Calls window.onDeleteConfirmed(petId) if Person 7 sets it.
 */
function confirmDelete() {
    const id = _pendingDeleteId;
    closeConfirm();
    if (typeof window.onDeleteConfirmed === "function" && id !== null) {
        window.onDeleteConfirmed(id);
    }
}

/* ════════════════════════════════════════════
   PUBLIC: FILE UPLOAD UX
════════════════════════════════════════════ */

/**
 * Handle file selection — show preview.
 * @param {HTMLInputElement} input
 * @param {string} prefix - "add" | "edit"
 */
function handleFileSelect(input, prefix) {
    const preview     = document.getElementById(`${prefix}-file-preview`);
    const previewImg  = document.getElementById(`${prefix}-preview-img`);
    const previewName = document.getElementById(`${prefix}-preview-name`);
    const errorId     = `err-${prefix}-image`;

    if (!input.files || input.files.length === 0) return;

    const file = input.files[0];

    // Validate immediately
    if (!ALLOWED_TYPES.includes(file.type)) {
        _setInvalid(input.id, errorId, "Only JPG and PNG files are allowed.");
        input.value = "";
        return;
    }
    if (file.size > MAX_FILE_SIZE) {
        _setInvalid(input.id, errorId, "File size must be under 2MB.");
        input.value = "";
        return;
    }

    _setValid(input.id, errorId);

    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImg.src        = e.target.result;
        previewName.textContent = file.name.length > 30
            ? file.name.substring(0, 27) + "..."
            : file.name;
        preview.classList.add("show");
    };
    reader.readAsDataURL(file);
}

/**
 * Remove selected file and clear preview.
 * @param {string} prefix - "add" | "edit"
 */
function removeFile(prefix) {
    const input   = document.getElementById(`${prefix}-image`);
    const preview = document.getElementById(`${prefix}-file-preview`);
    const img     = document.getElementById(`${prefix}-preview-img`);
    if (input)   { input.value = ""; }
    if (img)     { img.src = ""; }
    if (preview) { preview.classList.remove("show"); }
    const errorId = `err-${prefix}-image`;
    const error   = document.getElementById(errorId);
    if (error)   { error.textContent = ""; }
}

/* Drag-and-drop visual feedback */
document.addEventListener("DOMContentLoaded", () => {
    ["add-upload-area", "edit-upload-area"].forEach(areaId => {
        const area = document.getElementById(areaId);
        if (!area) return;
        area.addEventListener("dragover",  (e) => { e.preventDefault(); area.classList.add("dragover"); });
        area.addEventListener("dragleave", ()  => { area.classList.remove("dragover"); });
        area.addEventListener("drop",      (e) => { e.preventDefault(); area.classList.remove("dragover"); });
    });
});

/* ════════════════════════════════════════════
   PUBLIC: RENDER HELPERS (for Person 7)
════════════════════════════════════════════ */

/**
 * Render an array of pets as cards in #pets-grid.
 * @param {Array} pets
 */
function renderPets(pets) {
    clearSkeletons();
    const grid = document.getElementById("pets-grid");
    grid.innerHTML = "";

    if (!pets || pets.length === 0) {
        renderEmpty();
        return;
    }

    updatePetCount(pets.length);

    pets.forEach((pet, i) => {
        const card = _buildPetCard(pet, i);
        grid.appendChild(card);
    });

    // Update hero chip
    document.getElementById("chip-count").textContent = pets.length;
}

/** Build a single pet card DOM element */
function _buildPetCard(pet, index) {
    const card = document.createElement("div");
    card.className = "pet-card";
    card.style.animationDelay = `${index * 0.07}s`;
    card.dataset.petId = pet.id;

    const typeEmoji = {
        cat: "🐱", dog: "🐶", bird: "🐦", rabbit: "🐰", other: "🐾"
    };
    const emoji = typeEmoji[pet.type] || "🐾";

    const imageSrc = pet.image_path ? `../${String(pet.image_path).replace(/^\/+/, "")}` : "";
    const imageHtml = imageSrc
        ? `<img src="${_escapeHtml(imageSrc)}" alt="${_escapeHtml(pet.name)}" loading="lazy" onerror="this.parentElement.innerHTML='<span style=font-size:4rem>${emoji}</span>'">`
        : `<span style="font-size:4rem">${emoji}</span>`;

    const breedMeta = pet.breed ? `${_escapeHtml(pet.breed)} · ` : "";

    card.innerHTML = `
        <div class="pet-card-image">${imageHtml}</div>
        <div class="pet-card-body">
            <span class="pet-card-type">${_escapeHtml(pet.type || "other")}</span>
            <div class="pet-card-name">${_escapeHtml(pet.name)}</div>
            <div class="pet-card-meta">${breedMeta}${pet.age != null ? pet.age + " yr" + (pet.age == 1 ? "" : "s") : ""}</div>
            <div class="pet-card-desc">${_escapeHtml(pet.description || "")}</div>
            <div class="pet-card-actions">
                <button class="btn-edit" onclick="openEditModal(${JSON.stringify(pet).replace(/"/g, '&quot;')})">✏️ Edit</button>
                <button class="btn-delete" onclick="openConfirm(${pet.id})">🗑️</button>
            </div>
        </div>
    `;

    return card;
}

/** Show empty state in the grid */
function renderEmpty() {
    const grid = document.getElementById("pets-grid");
    grid.innerHTML = `
        <div class="empty-state">
            <div class="empty-state-icon">🐾</div>
            <h3>No pets found</h3>
            <p>Try a different search or be the first to add a pet!</p>
            <button class="btn-primary" onclick="openAddModal()">Add a Pet</button>
        </div>
    `;
    updatePetCount(0);
}

/** Remove skeleton loading cards */
function clearSkeletons() {
    ["sk1", "sk2", "sk3"].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.remove();
    });
}

/**
 * Update the pet count display.
 * @param {number} count
 */
function updatePetCount(count) {
    const el = document.getElementById("pets-count");
    if (el) el.textContent = `${count} pet${count !== 1 ? "s" : ""} available`;
    const chip = document.getElementById("chip-count");
    if (chip) chip.textContent = count;
}

/* ════════════════════════════════════════════
   PUBLIC: NAVIGATION HELPERS
════════════════════════════════════════════ */

function scrollToSection(id) {
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: "smooth", block: "start" });
}

function toggleMobileMenu() {
    const menu = document.getElementById("mobile-menu");
    if (menu) menu.classList.toggle("open");
}

/* ════════════════════════════════════════════
   PUBLIC: SEARCH HANDLERS
════════════════════════════════════════════ */

/**
 * Called when Search button is clicked.
 * Calls window.onSearch({ query, type }) if Person 7 sets it.
 */
function handleSearch() {
    const query = (document.getElementById("search-input")?.value || "").trim();
    const type  = document.getElementById("type-filter")?.value || "";
    if (typeof window.onSearch === "function") {
        window.onSearch({ query, type });
    }
}

/**
 * Called when Clear button is clicked.
 * Resets search inputs and calls window.onClear() if set.
 */
function handleClear() {
    const input  = document.getElementById("search-input");
    const filter = document.getElementById("type-filter");
    if (input)  input.value  = "";
    if (filter) filter.value = "";
    if (typeof window.onClear === "function") {
        window.onClear();
    }
}

/* Live search + Enter key support */
document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("search-input");
    const filter = document.getElementById("type-filter");

    if (input) {
        // Search as the user types.
        input.addEventListener("input", () => handleSearch());
    }

    if (filter) {
        // Re-run search when type filter changes.
        filter.addEventListener("change", () => handleSearch());
    }

    if (input) {
        input.addEventListener("keydown", (e) => {
            if (e.key === "Enter") handleSearch();
        });
    }
});

/* ════════════════════════════════════════════
   UTILITY
════════════════════════════════════════════ */

/** Escape HTML to prevent XSS — mirrors PHP's htmlspecialchars */
function _escapeHtml(str) {
    if (str == null) return "";
    return String(str)
        .replace(/&/g,  "&amp;")
        .replace(/</g,  "&lt;")
        .replace(/>/g,  "&gt;")
        .replace(/"/g,  "&quot;")
        .replace(/'/g,  "&#039;");
}

/* ════════════════════════════════════════════
   FACTS SECTION — DOT INDICATOR SYNC
   (Called by API_Ops.js after each fact load)
════════════════════════════════════════════ */

/**
 * Sync the dots in the fun-facts section.
 * @param {number} activeIndex - 0, 1, or 2
 */
function syncFactDots(activeIndex) {
    [0, 1, 2].forEach(i => {
        const dot = document.getElementById(`dot-${i}`);
        if (!dot) return;
        dot.classList.toggle("active", i === activeIndex % 3);
    });
}
