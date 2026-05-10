@extends('layouts.app')

@section('title', 'Littlest — Find Your Perfect Pet')

@section('content')

{{-- ══════════════════════════════════════
     HERO SECTION
══════════════════════════════════════ --}}
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
        <img src="{{ asset('img/Dog2.png') }}" alt="">
        <div class="hero-stat-chips">
            <div class="stat-chip"><span id="chip-count">{{ $pets->count() }}</span>Pets Listed</div>
            <div class="stat-chip"><span>♥</span>Find a Home</div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════
     FLASH MESSAGES
══════════════════════════════════ --}}
@if (session('success'))
    <div id="toast-success" role="alert" aria-live="polite" class="toast-auto-show">
        <span class="toast-icon toast-icon--success">&#10003;</span>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div id="toast-error" role="alert" aria-live="assertive" class="toast-auto-show">
        <span class="toast-icon toast-icon--error">&#10007;</span>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- ══════════════════════════════════
     SEARCH BAR
══════════════════════════════════ --}}
<div class="search-section">
    <div class="search-inner">
        <div class="search-box">
            <input
                type="text"
                id="search-input"
                placeholder="Search by name or breed..."
                autocomplete="off"
                aria-label="Search pets"
                value="{{ request('search') }}"
            >
        </div>
        <select class="filter-select" id="type-filter" aria-label="Filter by type">
            <option value="">All Types</option>
            <option value="cat"    {{ request('type') === 'cat'    ? 'selected' : '' }}>Cats</option>
            <option value="dog"    {{ request('type') === 'dog'    ? 'selected' : '' }}>Dogs</option>
            <option value="bird"   {{ request('type') === 'bird'   ? 'selected' : '' }}>Birds</option>
            <option value="rabbit" {{ request('type') === 'rabbit' ? 'selected' : '' }}>Rabbits</option>
            <option value="other"  {{ request('type') === 'other'  ? 'selected' : '' }}>Other</option>
        </select>
        <button class="search-btn" id="search-btn" onclick="handleSearch()">Search</button>
        <button class="clear-btn"  id="clear-btn"  onclick="handleClear()">Clear</button>
    </div>
</div>

{{-- ══════════════════════════════════
     BROWSE / PETS GRID
══════════════════════════════════ --}}
<section id="browse-section">
    <div class="section-header">
        <h2 class="section-title">Available Pets</h2>
        <span class="section-count" id="pets-count">
            {{ $pets->count() }} pet{{ $pets->count() !== 1 ? 's' : '' }} available
        </span>
    </div>

    <div id="pets-grid">
        @if ($pets->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"></div>
                <h3>No pets found</h3>
                <p>Be the first to add a pet!</p>
                <button class="btn-primary" onclick="openAddModal()">Add a Pet</button>
            </div>
        @else
            @foreach ($pets as $i => $pet)
                <x-pet-card :pet="$pet" :index="$i" />
            @endforeach
        @endif
    </div>
</section>

{{-- ══════════════════════════════════
     FUN FACTS SECTION (API)
══════════════════════════════════ --}}
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

{{-- ══════════════════════════════════════
     ADD PET MODAL
══════════════════════════════════════ --}}
<div class="modal-overlay" id="add-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="add-modal-title">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="add-modal-title">Add a Pet</h2>
            <button class="modal-close" onclick="closeAddModal()" aria-label="Close">✕</button>
        </div>

        <form id="add-pet-form" action="{{ route('pets.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="add-name">Pet Name <span class="required">*</span></label>
                    <input class="form-input {{ $errors->has('name') ? 'input-error' : '' }}"
                           type="text" id="add-name" name="name"
                           placeholder="e.g. Biscuit" autocomplete="off"
                           value="{{ old('name') }}">
                    <span class="field-error" id="err-add-name">
                        @error('name'){{ $message }}@enderror
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="add-type">Type <span class="required">*</span></label>
                    <select class="form-select {{ $errors->has('type') ? 'input-error' : '' }}" id="add-type" name="type">
                        <option value="">Select type...</option>
                        @foreach (['cat','dog','bird','rabbit','other'] as $t)
                            <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>
                                {{ ucfirst($t) }}
                            </option>
                        @endforeach
                    </select>
                    <span class="field-error" id="err-add-type">
                        @error('type'){{ $message }}@enderror
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="add-breed">Breed</label>
                    <input class="form-input" type="text" id="add-breed" name="breed"
                           placeholder="e.g. Persian" autocomplete="off"
                           value="{{ old('breed') }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="add-age">Age (years) <span class="required">*</span></label>
                    <input class="form-input {{ $errors->has('age') ? 'input-error' : '' }}"
                           type="number" id="add-age" name="age"
                           placeholder="e.g. 2" min="0" max="30"
                           value="{{ old('age') }}">
                    <span class="field-error" id="err-add-age">
                        @error('age'){{ $message }}@enderror
                    </span>
                </div>

                <div class="form-group full">
                    <label class="form-label" for="add-description">Description <span class="required">*</span></label>
                    <textarea class="form-textarea {{ $errors->has('description') ? 'input-error' : '' }}"
                              id="add-description" name="description"
                              placeholder="Tell us about this pet's personality...">{{ old('description') }}</textarea>
                    <span class="field-error" id="err-add-description">
                        @error('description'){{ $message }}@enderror
                    </span>
                </div>

                <div class="form-group full">
                    <label class="form-label">Photo</label>
                    <div class="file-upload-area" id="add-upload-area">
                        <input type="file" id="add-image" name="image"
                               accept="image/jpeg,image/png,image/jpg"
                               onchange="handleFileSelect(this, 'add')">
                        <div class="file-upload-icon">+</div>
                        <div class="file-upload-text"><strong>Click to upload</strong> or drag & drop</div>
                        <div class="file-upload-hint">JPG or PNG only · Max 2MB</div>
                    </div>
                    <div class="file-preview" id="add-file-preview">
                        <img id="add-preview-img" src="" alt="">
                        <span class="file-preview-name" id="add-preview-name"></span>
                        <button type="button" class="file-preview-remove" onclick="removeFile('add')" aria-label="Remove file">✕</button>
                    </div>
                    <span class="field-error" id="err-add-image">
                        @error('image'){{ $message }}@enderror
                    </span>
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

{{-- ══════════════════════════════════════
     EDIT PET MODAL
══════════════════════════════════════ --}}
<div class="modal-overlay" id="edit-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="edit-modal-title">Edit Pet</h2>
            <button class="modal-close" onclick="closeEditModal()" aria-label="Close">✕</button>
        </div>

        <form id="edit-pet-form" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
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
                        @foreach (['cat','dog','bird','rabbit','other'] as $t)
                            <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                        @endforeach
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

{{-- ══════════════════════════════════════
     DELETE CONFIRM DIALOG
══════════════════════════════════════ --}}
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

{{-- Hidden delete form (submitted by JS) --}}
<form id="delete-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

{{-- Toast containers (JS-controlled) --}}
<div id="toast-success" role="alert" aria-live="polite">
    <span class="toast-icon toast-icon--success">&#10003;</span>
    <span id="toast-success-msg"></span>
</div>
<div id="toast-error" role="alert" aria-live="assertive">
    <span class="toast-icon toast-icon--error">&#10007;</span>
    <span id="toast-error-msg"></span>
</div>

@endsection

@push('scripts')
<script>
    // Override openConfirm to also store the delete route
    let _deleteRoute = null;

    function openConfirm(petId, deleteRoute) {
        _deleteRoute = deleteRoute;
        document.getElementById('confirm-overlay').classList.add('open');
    }

    function closeConfirm() {
        _deleteRoute = null;
        document.getElementById('confirm-overlay').classList.remove('open');
    }

    function confirmDelete() {
        if (!_deleteRoute) return;
        const form = document.getElementById('delete-form');
        form.action = _deleteRoute;
        closeConfirm();
        form.submit();
    }

    // Edit modal controls
    function openEditModal(pet) {
        const form = document.getElementById('edit-pet-form');
        form.action = '/pets/' + pet.id;

        document.getElementById('edit-name').value        = pet.name        || '';
        document.getElementById('edit-type').value        = pet.type        || '';
        document.getElementById('edit-breed').value       = pet.breed       || '';
        document.getElementById('edit-age').value         = pet.age         ?? '';
        document.getElementById('edit-description').value = pet.description || '';

        removeFile('edit');
        document.getElementById('edit-modal-overlay').classList.add('open');
    }

    function closeEditModal() {
        document.getElementById('edit-modal-overlay').classList.remove('open');
    }

    // Close modals on overlay click
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('edit-modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
    });

    // Auto-show flash toasts from server
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.toast-auto-show').forEach(el => {
            el.classList.add('show');
            setTimeout(() => el.classList.remove('show'), 4000);
        });
    });
</script>
@endpush