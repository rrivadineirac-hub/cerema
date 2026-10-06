document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggle-btn');
    const mobileToggleBtn = document.getElementById('mobile-toggle-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('mobile-open');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('mobile-open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleDesktopSidebar() {
        if (!sidebar) return;
        sidebar.classList.toggle('collapsed');
    }

    // mobileToggleBtn click is handled via onclick="toggleMobileSidebarDirect(event)" in header.php

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (window.innerWidth <= 1024) {
                if (sidebar.classList.contains('mobile-open')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                toggleDesktopSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 1024 && 
            sidebar && 
            sidebar.classList.contains('mobile-open') &&
            !sidebar.contains(e.target) &&
            mobileToggleBtn &&
            !mobileToggleBtn.contains(e.target)) {
            closeMobileSidebar();
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            closeMobileSidebar();
        }
    });
});
