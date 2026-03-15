document.addEventListener("DOMContentLoaded", () => {
  // Update state navigasi aktif (Warna pill menu)
  function updateActiveNav() {
    const currentPath = window.location.pathname.replace(/\/$/, "") || "/";
    const navLinks = document.querySelectorAll(".nav-item[href]");

    navLinks.forEach((link) => {
      const navPath = link.getAttribute("href").replace(/\/$/, "") || "/";
      const isActive = currentPath === navPath || currentPath.startsWith(navPath + "/");
      link.classList.toggle("active", isActive);
    });
  }

  // Tutup semua CSS Checkbox (Dropdown Settings, Profile, & Mobile Drawer)
  function closeAllMenus() {
    document.querySelectorAll('.css-toggle-input').forEach(input => {
      input.checked = false;
    });
  }

  // Initialize saat pertama kali load
  updateActiveNav();
  
  // Listeners khusus untuk SPA Mazu
  window.addEventListener("spa:navigated", () => {
    updateActiveNav();
    closeAllMenus(); // Otomatis tutup menu setelah pindah halaman
  });
  
  window.addEventListener("popstate", () => {
    setTimeout(updateActiveNav, 50);
    closeAllMenus();
  });

  // (Opsional) Jika di mode mobile, tutup drawer ketika link diklik
  document.querySelectorAll(".nav-menu .nav-item").forEach((link) => {
    link.addEventListener("click", closeAllMenus);
  });
});