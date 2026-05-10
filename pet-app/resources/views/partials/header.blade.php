<header class="site-header">
    <div class="header-inner">
        <div class="logo">
            <a href="{{ route('pets.index') }}">
                <img src="{{ asset('img/logo.png') }}" alt="Littlest Logo" class="logo-image">
            </a>
        </div>
        <nav class="nav-links">
            <a href="{{ route('pets.index') }}" class="nav-link">Browse</a>
            <a href="{{ route('pets.index') }}#facts-section" class="nav-link">Fun Facts</a>
            <button class="nav-link nav-cta" onclick="openAddModal()">+ Add Pet</button>
        </nav>
        <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
    <div class="mobile-menu" id="mobile-menu">
        <a href="{{ route('pets.index') }}">Browse Pets</a>
        <a href="{{ route('pets.index') }}#facts-section">Fun Facts</a>
        <button class="nav-link nav-cta" onclick="openAddModal()">+ Add Pet</button>
    </div>
</header>
