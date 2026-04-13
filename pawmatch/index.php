<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawMatch — Find Your Perfect Pet</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🐾</text></svg>">
</head>
<body>

<?php include 'header.php'; ?>

<!-- ══════════════════════════════════════
     HERO SECTION
══════════════════════════════════════ -->
<main>
    <section class="hero">
        <div class="hero-content">
            <span class="hero-label">Cairo's #1 Pet Adoption App</span>
            <h1>Find your perfect <em>furry</em> companion</h1>
            <p class="hero-sub">Browse adorable pets looking for a loving home. Every adoption changes two lives — yours and theirs.</p>
            <div class="hero-actions">
                <button class="btn-primary" onclick="scrollToSection('browse-section')">Browse Pets 🐾</button>
                <button class="btn-secondary" onclick="openAddModal()">List a Pet</button>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-blob"></div>
            <div class="hero-emoji">🐶</div>
            <div class="hero-stat-chips">
                <div class="stat-chip"><span id="chip-count">0</span> Pets Listed</div>
                <div class="stat-chip">🏠 Find a Home</div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════
         SEARCH BAR
    ══════════════════════════════════ -->
    <div class="search-section">
        <div class="search-inner">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    id="search-input"
                    placeholder="Search by name or breed..."
                    autocomplete="off"
                    aria-label="Search pets"
                >
            </div>
            <select class="filter-select" id="type-filter" aria-label="Filter by type">
                <option value="">All Types</option>
                <option value="cat">🐱 Cats</option>
                <option value="dog">🐶 Dogs</option>
                <option value="bird">🐦 Birds</option>
                <option value="rabbit">🐰 Rabbits</option>
                <option value="other">🐾 Other</option>
            </select>
            <button class="search-btn" id="search-btn" onclick="handleSearch()">Search</button>
            <button class="clear-btn" id="clear-btn" onclick="handleClear()">Clear</button>
        </div>
    </div>

    <!-- ══════════════════════════════════
         BROWSE / PETS GRID
    ══════════════════════════════════ -->
    <section id="browse-section">
        <div class="section-header">
            <h2 class="section-title">Available Pets</h2>
            <span class="section-count" id="pets-count"></span>
        </div>

        <!-- Person 7 injects cards here -->
        <div id="pets-grid">
            <!-- Skeleton loaders shown while fetching -->
            <div class="skeleton-card" id="sk1">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton-body">
                    <div class="skeleton skeleton-line w-30"></div>
                    <div class="skeleton skeleton-line w-60"></div>
                    <div class="skeleton skeleton-line w-80"></div>
                    <div class="skeleton skeleton-line w-full"></div>
                </div>
            </div>
            <div class="skeleton-card" id="sk2">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton-body">
                    <div class="skeleton skeleton-line w-30"></div>
                    <div class="skeleton skeleton-line w-60"></div>
                    <div class="skeleton skeleton-line w-80"></div>
                    <div class="skeleton skeleton-line w-full"></div>
                </div>
            </div>
            <div class="skeleton-card" id="sk3">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton-body">
                    <div class="skeleton skeleton-line w-30"></div>
                    <div class="skeleton skeleton-line w-60"></div>
                    <div class="skeleton skeleton-line w-80"></div>
                    <div class="skeleton skeleton-line w-full"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════
         FUN FACTS SECTION (API)
    ══════════════════════════════════ -->
    <section id="facts-section">
        <div class="facts-inner">
            <span class="facts-label">✨ Powered by Live API</span>
            <h2 class="facts-title">Fun Pet Facts</h2>
            <div class="facts-card-wrapper">
                <div class="fact-card">
                    <div>
                        <div class="fact-animal-label">
                            <span id="pet">Loading...</span>
                        </div>
                        <div id="fact">Fetching an amazing fact for you...</div>
                    </div>
                    <div class="fact-dots">
                        <div class="fact-dot active" id="dot-0"></div>
                        <div class="fact-dot" id="dot-1"></div>
                        <div class="fact-dot" id="dot-2"></div>
                    </div>
                </div>
                <div class="fact-image-col">
                    <div class="fact-image-frame">
                        <img id="image" src="" alt="Pet image" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- ══════════════════════════════════════
     ADD PET MODAL
══════════════════════════════════════ -->
<div class="modal-overlay" id="add-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="add-modal-title">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="add-modal-title">Add a Pet 🐾</h2>
            <button class="modal-close" onclick="closeAddModal()" aria-label="Close">✕</button>
        </div>

        <form id="add-pet-form" novalidate>
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="add-name">Pet Name <span class="required">*</span></label>
                    <input class="form-input" type="text" id="add-name" name="name" placeholder="e.g. Biscuit" autocomplete="off">
                    <span class="field-error" id="err-add-name"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="add-type">Type <span class="required">*</span></label>
                    <select class="form-select" id="add-type" name="type">
                        <option value="">Select type...</option>
                        <option value="cat">🐱 Cat</option>
                        <option value="dog">🐶 Dog</option>
                        <option value="bird">🐦 Bird</option>
                        <option value="rabbit">🐰 Rabbit</option>
                        <option value="other">🐾 Other</option>
                    </select>
                    <span class="field-error" id="err-add-type"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="add-breed">Breed</label>
                    <input class="form-input" type="text" id="add-breed" name="breed" placeholder="e.g. Persian" autocomplete="off">
                    <span class="field-error" id="err-add-breed"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="add-age">Age (years) <span class="required">*</span></label>
                    <input class="form-input" type="number" id="add-age" name="age" placeholder="e.g. 2" min="0" max="30">
                    <span class="field-error" id="err-add-age"></span>
                </div>

                <div class="form-group full">
                    <label class="form-label" for="add-description">Description <span class="required">*</span></label>
                    <textarea class="form-textarea" id="add-description" name="description" placeholder="Tell us about this pet's personality, habits, and what makes them special..."></textarea>
                    <span class="field-error" id="err-add-description"></span>
                </div>

                <div class="form-group full">
                    <label class="form-label">Photo</label>
                    <div class="file-upload-area" id="add-upload-area">
                        <input type="file" id="add-image" name="image_path" accept="image/jpeg,image/png,image/jpg" onchange="handleFileSelect(this, 'add')">
                        <div class="file-upload-icon">📷</div>
                        <div class="file-upload-text"><strong>Click to upload</strong> or drag & drop</div>
                        <div class="file-upload-hint">JPG or PNG only · Max 2MB</div>
                    </div>
                    <div class="file-preview" id="add-file-preview">
                        <img id="add-preview-img" src="" alt="">
                        <span class="file-preview-name" id="add-preview-name"></span>
                        <button type="button" class="file-preview-remove" onclick="removeFile('add')" aria-label="Remove file">✕</button>
                    </div>
                    <span class="field-error" id="err-add-image"></span>
                </div>

            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-submit" id="add-submit-btn">
                    <span class="spinner"></span>
                    <span class="btn-text">Add Pet 🐾</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════
     EDIT PET MODAL
══════════════════════════════════════ -->
<div class="modal-overlay" id="edit-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-modal-title">Edit Pet ✏️</h2>
            <button class="modal-close" onclick="closeEditModal()" aria-label="Close">✕</button>
        </div>

        <form id="edit-pet-form" novalidate>
            <input type="hidden" id="edit-id" name="id">
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="edit-name">Pet Name <span class="required">*</span></label>
                    <input class="form-input" type="text" id="edit-name" name="name" placeholder="e.g. Biscuit" autocomplete="off">
                    <span class="field-error" id="err-edit-name"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-type">Type <span class="required">*</span></label>
                    <select class="form-select" id="edit-type" name="type">
                        <option value="">Select type...</option>
                        <option value="cat">🐱 Cat</option>
                        <option value="dog">🐶 Dog</option>
                        <option value="bird">🐦 Bird</option>
                        <option value="rabbit">🐰 Rabbit</option>
                        <option value="other">🐾 Other</option>
                    </select>
                    <span class="field-error" id="err-edit-type"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-breed">Breed</label>
                    <input class="form-input" type="text" id="edit-breed" name="breed" placeholder="e.g. Persian" autocomplete="off">
                    <span class="field-error" id="err-edit-breed"></span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="edit-age">Age (years) <span class="required">*</span></label>
                    <input class="form-input" type="number" id="edit-age" name="age" placeholder="e.g. 2" min="0" max="30">
                    <span class="field-error" id="err-edit-age"></span>
                </div>

                <div class="form-group full">
                    <label class="form-label" for="edit-description">Description <span class="required">*</span></label>
                    <textarea class="form-textarea" id="edit-description" name="description" placeholder="Tell us about this pet..."></textarea>
                    <span class="field-error" id="err-edit-description"></span>
                </div>

                <div class="form-group full">
                    <label class="form-label">New Photo <span style="color:var(--muted);font-weight:400">(optional — leave blank to keep current)</span></label>
                    <div class="file-upload-area" id="edit-upload-area">
                        <input type="file" id="edit-image" name="image_path" accept="image/jpeg,image/png,image/jpg" onchange="handleFileSelect(this, 'edit')">
                        <div class="file-upload-icon">📷</div>
                        <div class="file-upload-text"><strong>Click to upload</strong> or drag & drop</div>
                        <div class="file-upload-hint">JPG or PNG only · Max 2MB</div>
                    </div>
                    <div class="file-preview" id="edit-file-preview">
                        <img id="edit-preview-img" src="" alt="">
                        <span class="file-preview-name" id="edit-preview-name"></span>
                        <button type="button" class="file-preview-remove" onclick="removeFile('edit')" aria-label="Remove file">✕</button>
                    </div>
                    <span class="field-error" id="err-edit-image"></span>
                </div>

            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-submit" id="edit-submit-btn">
                    <span class="spinner"></span>
                    <span class="btn-text">Save Changes ✓</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════
     DELETE CONFIRM DIALOG
══════════════════════════════════════ -->
<div class="confirm-overlay" id="confirm-overlay" role="alertdialog" aria-modal="true">
    <div class="confirm-box">
        <div class="confirm-icon">🗑️</div>
        <h3 class="confirm-title">Delete this pet?</h3>
        <p class="confirm-msg">This action cannot be undone. The pet listing will be permanently removed.</p>
        <div class="confirm-actions">
            <button class="btn-confirm-cancel" onclick="closeConfirm()">Keep Pet</button>
            <button class="btn-confirm-delete" id="confirm-delete-btn" onclick="confirmDelete()">Yes, Delete</button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     TOAST NOTIFICATIONS
══════════════════════════════════════ -->
<div id="toast-success" role="alert" aria-live="polite">
    <span class="toast-icon">✅</span>
    <span id="toast-success-msg"></span>
</div>
<div id="toast-error" role="alert" aria-live="assertive">
    <span class="toast-icon">❌</span>
    <span id="toast-error-msg"></span>
</div>

<?php include 'footer.php'; ?>

<script src="validation.js"></script>
<script src="API_Ops.js"></script>
<!-- Person 7's AJAX file goes here: -->
<!-- <script src="app.js"></script> -->

</body>
</html>
