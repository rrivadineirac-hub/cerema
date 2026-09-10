document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggle-btn');
    const sidebar = document.getElementById('sidebar');
    
    // Toggle sidebar on desktop
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.toggle('collapsed');
            } else {
                sidebar.classList.toggle('mobile-open');
            }
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && 
            sidebar &&
            !sidebar.contains(e.target) && 
            toggleBtn &&
            !toggleBtn.contains(e.target) && 
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (sidebar) {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }
    });
});
