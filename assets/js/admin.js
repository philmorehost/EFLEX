// IIFE to avoid polluting the global namespace
(function() {
  "use strict";

  // --- Sidebar Toggle Functionality ---
  const sidebar = document.querySelector('.sidebar');
  const sidebarToggle = document.querySelector('#sidebarToggle');
  const sidebarToggleTop = document.querySelector('#sidebarToggleTop');

  const toggleSidebar = () => {
    document.body.classList.toggle('sidebar-toggled');
    sidebar.classList.toggle('toggled');
  };

  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', toggleSidebar);
  }
  if (sidebarToggleTop) {
    sidebarToggleTop.addEventListener('click', toggleSidebar);
  }

  // --- Scroll to Top Button ---
  const scrollToTopButton = document.querySelector('.scroll-to-top');

  window.addEventListener('scroll', () => {
    if (scrollToTopButton) {
      if (document.documentElement.scrollTop > 100) {
        scrollToTopButton.style.display = 'block';
      } else {
        scrollToTopButton.style.display = 'none';
      }
    }
  });

  if (scrollToTopButton) {
    scrollToTopButton.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

})();