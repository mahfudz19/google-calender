document.addEventListener('DOMContentLoaded', () => {
    const mobileBtn = document.getElementById('mobileMenuBtn');
    const navMenu = document.getElementById('navMenu');

    // Toggle menu navigasi khusus untuk mode Mobile
    if (mobileBtn && navMenu) {
        mobileBtn.addEventListener('click', () => {
            navMenu.classList.toggle('show');
        });
    }

    // Update active navigation state
    function updateActiveNav() {
        const currentPath = window.location.pathname.replace(/\/$/, '') || '/';
        const navLinks = document.querySelectorAll('.nav-item[href]');
        
        navLinks.forEach(link => {
            const navPath = link.getAttribute('href').replace(/\/$/, '') || '/';
            const isActive = currentPath === navPath || currentPath.startsWith(navPath + '/');
            
            link.classList.toggle('active', isActive);
        });
    }

    // Initialize and listen for navigation
    updateActiveNav();
    window.addEventListener('spa:navigated', updateActiveNav);
    window.addEventListener('popstate', () => setTimeout(updateActiveNav, 50));

    // Close menu on nav link click (mobile)
    navMenu.querySelectorAll('.nav-item').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                navMenu.classList.remove('show');
            }
        });
    });
});