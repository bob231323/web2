"use strict";

/* ════════════════════════════════════════════
  1. FORM SUBMIT INTERCEPTORS (AJAX CRUD)
════════════════════════════════════════════ */
document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // ── Add Pet form ──────────────────────────────────────────────────────────
    const addForm = document.getElementById("add-pet-form");
    if (addForm) {
        addForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const isValid = validateAddForm(); // from validation.js
            if (!isValid) return;

            setLoading("add", true);

            try {
                const formData = new FormData(addForm);
                const response = await fetch(addForm.action, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess(data.message);
                    
                    // Add new card to grid
                    const grid = document.getElementById("pets-grid");
                    const emptyState = grid.querySelector(".empty-state");
                    if (emptyState) emptyState.remove();

                    // Create a temporary container to parse HTML
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    const newCard = temp.firstElementChild;
                    grid.prepend(newCard);

                    // Update local cards cache for search
                    if (window._allCards) {
                        window._allCards.unshift(newCard);
                    }

                    closeAddModal();
                    addForm.reset();
                    updatePetCount(document.querySelectorAll("#pets-grid .pet-card").length);
                } else {
                    showError(data.message || "Something went wrong.");
                }
            } catch (error) {
                console.error("Error:", error);
                showError("Server error. Please try again.");
            } finally {
                setLoading("add", false);
            }
        });
    }

    // ── Edit Pet form ─────────────────────────────────────────────────────────
    const editForm = document.getElementById("edit-pet-form");
    if (editForm) {
        editForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const isValid = validateEditForm(); // from validation.js
            if (!isValid) return;

            setLoading("edit", true);

            try {
                const formData = new FormData(editForm);
                // Laravel PUT workaround for multipart/form-data
                formData.append("_method", "PUT");

                const response = await fetch(editForm.action, {
                    method: "POST", // Use POST with _method=PUT for file uploads
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess(data.message);
                    
                    // Update existing card in grid
                    const oldCard = document.querySelector(`.pet-card[data-pet-id="${data.pet.id}"]`);
                    if (oldCard) {
                        const temp = document.createElement('div');
                        temp.innerHTML = data.html;
                        const newCard = temp.firstElementChild;
                        oldCard.replaceWith(newCard);

                        // Update local cards cache for search
                        if (window._allCards) {
                            const idx = window._allCards.findIndex(c => c.dataset.petId == data.pet.id);
                            if (idx !== -1) window._allCards[idx] = newCard;
                        }
                    }

                    closeEditModal();
                } else {
                    showError(data.message || "Something went wrong.");
                }
            } catch (error) {
                console.error("Error:", error);
                showError("Server error. Please try again.");
            } finally {
                setLoading("edit", false);
            }
        });
    }
});

/* ════════════════════════════════════════════
  2. DELETE (AJAX)
════════════════════════════════════════════ */
window.onDeleteConfirmed = async function (petId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const deleteBtn = document.getElementById("confirm-delete-btn");
    
    if (deleteBtn) {
        deleteBtn.disabled = true;
        deleteBtn.textContent = "Deleting...";
    }

    try {
        const response = await fetch(`/pets/${petId}`, {
            method: "DELETE",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json"
            }
        });

        const data = await response.json();

        if (data.success) {
            showSuccess(data.message);
            
            // Remove card from grid
            const card = document.querySelector(`.pet-card[data-pet-id="${petId}"]`);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.remove();
                    
                    // Update local cards cache for search
                    if (window._allCards) {
                        window._allCards = window._allCards.filter(c => c.dataset.petId != petId);
                    }

                    const remaining = document.querySelectorAll("#pets-grid .pet-card").length;
                    updatePetCount(remaining);
                    if (remaining === 0) {
                        if (typeof renderEmpty === "function") renderEmpty();
                    }
                }, 300);
            }
        } else {
            showError(data.message || "Could not delete pet.");
        }
    } catch (error) {
        console.error("Error:", error);
        showError("Server error. Please try again.");
    } finally {
        if (deleteBtn) {
            deleteBtn.disabled = false;
            deleteBtn.textContent = "Yes, Delete";
        }
        closeConfirm();
    }
};

/* ════════════════════════════════════════════
  3. AJAX SEARCH / FILTER (With Debounce)
════════════════════════════════════════════ */

let _searchTimeout = null;

/**
 * Perform AJAX search.
 * @param {Object} params - { query, type, immediate }
 */
window.onSearch = function ({ query, type, immediate = false }) {
    // Clear previous timeout
    clearTimeout(_searchTimeout);

    const performSearch = async () => {
        const grid = document.getElementById("pets-grid");
        const countEl = document.getElementById("pets-count");
        const chipCount = document.getElementById("chip-count");

        try {
            const url = new URL('/pets', window.location.origin);
            if (query) url.searchParams.append('search', query);
            if (type) url.searchParams.append('type', type);

            const response = await fetch(url, {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const data = await response.json();

            if (data.success) {
                grid.innerHTML = data.html;
                
                if (countEl) countEl.textContent = `${data.count} pet${data.count !== 1 ? 's' : '' } available`;
                if (chipCount) chipCount.textContent = data.count;

                if (data.count === 0) {
                    if (typeof renderEmpty === "function") renderEmpty();
                }
                
                window._allCards = Array.from(grid.querySelectorAll(".pet-card"));
            }
        } catch (error) {
            console.error("Search error:", error);
        }
    };

    if (immediate) {
        performSearch();
    } else {
        _searchTimeout = setTimeout(performSearch, 300);
    }
};

window.onClear = function () {
    const input = document.getElementById("search-input");
    const filter = document.getElementById("type-filter");
    if (input) input.value = "";
    if (filter) filter.value = "";
    window.onSearch({ query: "", type: "", immediate: true });
};
