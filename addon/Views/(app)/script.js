
/**
 * Navigation Active State Manager for SPA
 * Updates navigation active state based on current URL
 */
(function() {
  'use strict';
  
  /**
   * Update navigation active state
   * @param {string} currentUrl - Current page URL
   */
  function updateNavigationActiveState(currentUrl = null) {
    const url = currentUrl || window.location.pathname;
    const baseUrl = window.mazuConfig?.base_url || '/';
    
    // Normalize base URL and current path
    const normalizedBaseUrl = baseUrl.replace(/\/$/, '');
    const normalizedUrl = url.replace(/\/$/, '') || '/';
    
    // Find all navigation links with data-nav-path attribute
    const navLinks = document.querySelectorAll('.nav-link[data-nav-path]');
    
    navLinks.forEach(link => {
      const navPath = link.getAttribute('data-nav-path');
      const normalizedNavPath = navPath.replace(/\/$/, '') || '/';
      
      // Check if current URL matches navigation path
      // Support both exact match and path prefix match
      const isActive = normalizedUrl === normalizedNavPath || 
                      normalizedUrl.startsWith(normalizedNavPath + '/');
      
      // Update active state
      if (isActive) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  }
  
  /**
   * Initialize navigation active state
   */
  function initNavigationActiveState() {
    // Set initial active state
    updateNavigationActiveState();
    
    // Listen for SPA navigation events
    window.addEventListener('spa:navigated', () => {
      updateNavigationActiveState();
    });
    
    // Also listen for popstate (browser back/forward)
    window.addEventListener('popstate', () => {
      setTimeout(() => {
        updateNavigationActiveState();
      }, 50); // Small delay to ensure URL is updated
    });
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNavigationActiveState);
  } else {
    initNavigationActiveState();
  }
  
  // Expose function globally for manual updates if needed
  window.updateNavigationActiveState = updateNavigationActiveState;
})();