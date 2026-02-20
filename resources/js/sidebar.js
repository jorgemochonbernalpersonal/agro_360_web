/**
 * Agro365 Sidebar Logic
 * Replaces inline <script> in sidebar component
 * Uses CustomEvent instead of polling for layout sync
 */

// Desktop collapse/expand
window.toggleSidebarCollapse = function () {
    const sidebar = document.getElementById('sidebar');
    const icon = document.getElementById('collapse-icon');
    if (!sidebar) return;

    const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
    const willCollapse = !isCollapsed;

    sidebar.classList.toggle('lg:w-20', willCollapse);
    sidebar.classList.toggle('lg:w-72', !willCollapse);
    sidebar.setAttribute('data-collapsed', String(willCollapse));
    if (icon) icon.style.transform = willCollapse ? 'rotate(180deg)' : '';

    // Toggle text visibility
    document.querySelectorAll('.sidebar-text, .sidebar-indicator, .sidebar-submenu').forEach(el => {
        if (willCollapse) {
            el.style.opacity = '0';
            setTimeout(() => { if (sidebar.getAttribute('data-collapsed') === 'true') el.style.display = 'none'; }, 150);
        } else {
            setTimeout(() => { el.style.display = ''; el.style.opacity = '1'; }, 150);
        }
    });

    // Broadcast change for top-bar and main content
    window.dispatchEvent(new CustomEvent('sidebar-toggle', { detail: { collapsed: willCollapse } }));
};

// Mobile open/close
window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (!sidebar) return;

    sidebar.classList.toggle('-translate-x-full');
    if (overlay) overlay.classList.toggle('hidden');
    document.body.style.overflow = sidebar.classList.contains('-translate-x-full') ? '' : 'hidden';
};

// Listen for sidebar-toggle to update main content + top-bar
window.addEventListener('sidebar-toggle', (e) => {
    const collapsed = e.detail.collapsed;
    const main = document.getElementById('main-content');
    const topBar = document.getElementById('top-bar');

    if (main) {
        main.classList.toggle('lg:pl-20', collapsed);
        main.classList.toggle('lg:pl-72', !collapsed);
    }
    if (topBar) {
        topBar.classList.toggle('lg:left-20', collapsed);
        topBar.classList.toggle('lg:left-72', !collapsed);
    }
});

// Close sidebar on outside click (mobile)
document.addEventListener('click', (e) => {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    if (window.innerWidth < 1024 && sidebar && toggle &&
        !sidebar.contains(e.target) && !toggle.contains(e.target) &&
        !sidebar.classList.contains('-translate-x-full')) {
        toggleSidebar();
    }
});

// Handle resize
window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (window.innerWidth >= 1024) {
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        document.body.style.overflow = '';
    } else if (sidebar) {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('lg:w-20');
        sidebar.classList.add('lg:w-72');
        sidebar.setAttribute('data-collapsed', 'false');
    }
});

// Escape key closes mobile sidebar
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
            toggleSidebar();
        }
    }
});

// Init transition on text elements
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.sidebar-text, .sidebar-indicator, .sidebar-submenu').forEach(el => {
        el.style.transition = 'opacity 150ms ease-in-out';
    });
});
