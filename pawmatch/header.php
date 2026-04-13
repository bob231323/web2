<?php // header.php ?>
<header class="site-header">
    <div class="header-inner">
        <div class="logo">
            <span class="logo-icon">🐾</span>
            <span class="logo-text">PawMatch</span>
        </div>
        <nav class="nav-links">
            <a href="#browse" class="nav-link" onclick="scrollToSection('browse-section')">Browse</a>
            <a href="#facts" class="nav-link" onclick="scrollToSection('facts-section')">Fun Facts</a>
            <a href="#add" class="nav-link nav-cta" onclick="openAddModal()">+ Add Pet</a>
        </nav>
        <button class="hamburger" id="hamburger" onclick="toggleMobileMenu()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
    <div class="mobile-menu" id="mobile-menu">
        <a href="#" onclick="scrollToSection('browse-section'); toggleMobileMenu()">Browse Pets</a>
        <a href="#" onclick="scrollToSection('facts-section'); toggleMobileMenu()">Fun Facts</a>
        <a href="#" onclick="openAddModal(); toggleMobileMenu()">+ Add Pet</a>
    </div>
</header>
