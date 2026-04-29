<?php
/** IMPORTANT NOTE :
 * you may need to uncomment some lines to woke in local host
 * go to file DB_Ops.php and uncomment line number 22 and comment line number 25*/

/* ════════════════════════════════════════════
   SERVER-SIDE: Load pets from database via DB_Ops.php
   DB_Ops.php includes root index.php (unchanged) to reuse all
   existing CRUD functions. POST actions are handled by DB_Ops.php
   and redirect back here with ?msg=... before the page renders.
════════════════════════════════════════════ */
require_once "DB_Ops.php";

// Fetch all pets using the backend getAllPets() from root index.php
// (called via getPets() in DB_Ops.php which captures its JSON output)
$pets = getPets($conn);

/** Helper: escape output to prevent XSS */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Littlest — Find Your Perfect Pet</title>
    <link rel="icon" type="image/png" href="img/Dog2.png">
    <link rel="stylesheet" href="style.css">
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
                <button class="btn-primary" onclick="scrollToSection('browse-section')">Browse Pets</button>
                <button class="btn-secondary" onclick="openAddModal()">List a Pet</button>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-blob"></div>
            <img src="img/Dog2.png" alt="">
            <div class="hero-stat-chips">
                <div class="stat-chip"><span id="chip-count"><?php echo count($pets); ?></span>Pets Listed</div>
                <div class="stat-chip"><span>♥</span>Find a Home</div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════
         SEARCH BAR
    ══════════════════════════════════ -->
    <div class="search-section">
        <div class="search-inner">
            <div class="search-box">
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
                <option value="cat">Cats</option>
                <option value="dog">Dogs</option>
                <option value="bird">Birds</option>
                <option value="rabbit">Rabbits</option>
                <option value="other">Other</option>
            </select>
            <button class="search-btn" id="search-btn" onclick="handleSearch()">Search</button>
            <button class="clear-btn" id="clear-btn" onclick="handleClear()">Clear</button>
        </div>
    </div>

    <!-- ══════════════════════════════════
         BROWSE / PETS GRID (server-rendered)
    ══════════════════════════════════ -->
    <section id="browse-section">
        <div class="section-header">
            <h2 class="section-title">Available Pets</h2>
            <span class="section-count" id="pets-count"><?php echo count($pets); ?> pet<?php echo count($pets) !== 1 ? 's' : ''; ?> available</span>
        </div>

        <div id="pets-grid">
            <?php if (empty($pets)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"></div>
                    <h3>No pets found</h3>
                    <p>Be the first to add a pet!</p>
                    <button class="btn-primary" onclick="openAddModal()">Add a Pet</button>
                </div>
            <?php else: ?>
                <?php foreach ($pets as $i => $pet): ?>
                    <div class="pet-card" data-pet-id="<?php echo e($pet['id']); ?>" style="animation-delay: <?php echo $i * 0.07; ?>s">
                        <div class="pet-card-image">
                            <?php if (!empty($pet['image_path'])): ?>
                                <img src="../<?php echo e($pet['image_path']); ?>" alt="<?php echo e($pet['name']); ?>" loading="lazy"
                                     onerror="this.parentElement.innerHTML='<span class=pet-type-initial><?php echo strtoupper(substr($pet["type"],0,1)); ?></span>'">
                            <?php else: ?>
                                <span class="pet-type-initial"><?php echo strtoupper(substr($pet['type'],0,1)); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="pet-card-body">
                            <span class="pet-card-type"><?php echo e($pet['type'] ?? 'other'); ?></span>
                            <div class="pet-card-name"><?php echo e($pet['name']); ?></div>
                            <div class="pet-card-meta">
                                <?php if (!empty($pet['breed'])): ?><?php echo e($pet['breed']); ?> · <?php endif; ?>
                                <?php if ($pet['age'] !== null): ?><?php echo (int)$pet['age']; ?> yr<?php echo $pet['age'] == 1 ? '' : 's'; ?><?php endif; ?>
                            </div>
                            <div class="pet-card-desc"><?php echo e($pet['description'] ?? ''); ?></div>
                            <div class="pet-card-actions">
                                <button class="btn-edit" onclick='openEditModal(<?php echo json_encode($pet, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Edit</button>
                                <button class="btn-delete" onclick="openConfirm(<?php echo (int)$pet['id']; ?>)">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ══════════════════════════════════
         FUN FACTS SECTION (API)
    ══════════════════════════════════ -->
    <section id="facts-section">
        <div class="facts-inner">
            <span class="facts-label">Powered by Live API</span>
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
                        <img id="image" alt="Pet image" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- ══════════════════════════════════════
     ADD PET MODAL (form POSTs to process.php)
══════════════════════════════════════ -->
<div class="modal-overlay" id="add-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="add-modal-title">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="add-modal-title">Add a Pet</h2>
            <button class="modal-close" onclick="closeAddModal()" aria-label="Close">✕</button>
        </div>

        <form id="add-pet-form" action="index.php" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="action" value="create">
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
                        <option value="cat">Cat</option>
                        <option value="dog">Dog</option>
                        <option value="bird">Bird</option>
                        <option value="rabbit">Rabbit</option>
                        <option value="other">Other</option>
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
                        <input type="file" id="add-image" name="image" accept="image/jpeg,image/png,image/jpg" onchange="handleFileSelect(this, 'add')">
                        <div class="file-upload-icon">+</div>
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
                    <span class="btn-text">Add Pet</span>
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
            <h2 class="modal-title" id="edit-modal-title">Edit Pet</h2>
            <button class="modal-close" onclick="closeEditModal()" aria-label="Close">✕</button>
        </div>

        <form id="edit-pet-form" action="index.php" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit-id" name="id">
            <input type="hidden" id="edit-existing-image" name="existing_image">
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
                        <option value="cat">Cat</option>
                        <option value="dog">Dog</option>
                        <option value="bird">Bird</option>
                        <option value="rabbit">Rabbit</option>
                        <option value="other">Other</option>
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
                        <input type="file" id="edit-image" name="image" accept="image/jpeg,image/png,image/jpg" onchange="handleFileSelect(this, 'edit')">
                        <div class="file-upload-icon">+</div>
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
                    <span class="btn-text">Save Changes</span>
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
        <div class="confirm-icon"></div>
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
    <span class="toast-icon toast-icon--success">&#10003;</span>
    <span id="toast-success-msg"></span>
</div>
<div id="toast-error" role="alert" aria-live="assertive">
    <span class="toast-icon toast-icon--error">&#10007;</span>
    <span id="toast-error-msg"></span>
</div>

<?php include 'footer.php'; ?>

<?php
// Cache-bust JS files so browser always loads latest edits during development.
$validationVer = @filemtime(__DIR__ . '/validation.js') ?: time();
$apiOpsVer = @filemtime(__DIR__ . '/API_Ops.js') ?: time();
$appVer = @filemtime(__DIR__ . '/app.js') ?: time();
?>
<script src="validation.js?v=<?php echo $validationVer; ?>"></script>
<script src="API_Ops.js?v=<?php echo $apiOpsVer; ?>"></script>
<script src="app.js?v=<?php echo $appVer; ?>"></script>

</body>
</html>
