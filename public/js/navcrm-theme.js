/**
 * NavCRM Theme JavaScript
 * Handles: sidebar toggle, dropdowns, toasts, mobile nav, theme utilities
 */

(function () {
  'use strict';

  // ── Constants ─────────────────────────────────────────────────────────────
  const STORAGE_SIDEBAR = 'ncv_sidebar_collapsed';
  const wrapper          = document.getElementById('appWrapper');

  // ─────────────────────────────────────────────────────────────────────────
  // 1. SIDEBAR TOGGLE
  // ─────────────────────────────────────────────────────────────────────────
  function toggleSidebar() {
    if (window.innerWidth < 992) {
      // Mobile: slide in/out
      openMobileSidebar();
      return;
    }
    // Desktop: collapse/expand
    const collapsed = wrapper.classList.toggle('sidebar-collapsed');
    localStorage.setItem(STORAGE_SIDEBAR, collapsed ? '1' : '0');
  }

  function openMobileSidebar() {
    wrapper.classList.add('sidebar-mobile-open');
    document.body.style.overflow = 'hidden';
  }

  function closeMobileSidebar() {
    wrapper.classList.remove('sidebar-mobile-open');
    document.body.style.overflow = '';
  }

  // Restore sidebar state on load
  if (window.innerWidth >= 992) {
    if (localStorage.getItem(STORAGE_SIDEBAR) === '1') {
      wrapper && wrapper.classList.add('sidebar-collapsed');
    }
  }

  // Close mobile sidebar on resize to desktop
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 992) {
      closeMobileSidebar();
    }
  });

  // Expose globally
  window.toggleSidebar      = toggleSidebar;
  window.openMobileSidebar  = openMobileSidebar;
  window.closeMobileSidebar = closeMobileSidebar;


  // ─────────────────────────────────────────────────────────────────────────
  // 2. DROPDOWN MENUS
  // ─────────────────────────────────────────────────────────────────────────
  function toggleDropdown(menuId) {
    const menu   = document.getElementById(menuId);
    if (!menu) return;
    const parent = menu.closest('.ncv-dropdown');
    const isOpen = parent.classList.contains('open');

    // Close all others
    document.querySelectorAll('.ncv-dropdown.open').forEach(d => d.classList.remove('open'));

    if (!isOpen) {
      parent.classList.add('open');
    }
  }

  // Close dropdowns when clicking outside
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.ncv-dropdown')) {
      document.querySelectorAll('.ncv-dropdown.open').forEach(d => d.classList.remove('open'));
    }
  });

  window.toggleDropdown = toggleDropdown;


  // ─────────────────────────────────────────────────────────────────────────
  // 3. TOAST NOTIFICATIONS
  // ─────────────────────────────────────────────────────────────────────────
  const TOAST_ICONS = {
    success : '✅',
    error   : '❌',
    warning : '⚠️',
    info    : 'ℹ️',
  };

  function showToast(title, message, type = 'success', duration = 4000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `ncv-toast ${type}`;
    toast.innerHTML = `
      <div class="ncv-toast-icon">${TOAST_ICONS[type] || TOAST_ICONS.info}</div>
      <div style="flex:1;">
        <div class="ncv-toast-title">${escapeHtml(title)}</div>
        ${message ? `<div class="ncv-toast-msg">${escapeHtml(message)}</div>` : ''}
      </div>
      <button class="ncv-toast-close" onclick="this.closest('.ncv-toast').remove()">
        &#x2715;
      </button>
    `;

    container.appendChild(toast);

    // Auto-dismiss
    if (duration > 0) {
      setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'opacity .3s, transform .3s';
        setTimeout(() => toast.remove(), 300);
      }, duration);
    }
  }

  window.showToast = showToast;


  // ─────────────────────────────────────────────────────────────────────────
  // 4. GLOBAL SEARCH (keyboard shortcut: Ctrl+K / Cmd+K)
  // ─────────────────────────────────────────────────────────────────────────
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      const searchInput = document.getElementById('globalSearch');
      if (searchInput) {
        searchInput.focus();
        searchInput.select();
      }
    }
    // Escape closes mobile sidebar and dropdowns
    if (e.key === 'Escape') {
      closeMobileSidebar();
      document.querySelectorAll('.ncv-dropdown.open').forEach(d => d.classList.remove('open'));
    }
  });


  // ─────────────────────────────────────────────────────────────────────────
  // 5. ACTIVE NAV LINK HIGHLIGHT
  // ─────────────────────────────────────────────────────────────────────────
  (function highlightActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.ncv-nav-item').forEach(function (item) {
      const href = item.getAttribute('href');
      if (href && href !== '#' && path.startsWith(href) && href.length > 1) {
        item.classList.add('active');
      }
    });
  })();


  // ─────────────────────────────────────────────────────────────────────────
  // 6. DATA TABLE SORT (simple client-side)
  // ─────────────────────────────────────────────────────────────────────────
  document.querySelectorAll('.ncv-table thead th').forEach(function (th) {
    th.addEventListener('click', function () {
      const table = th.closest('table');
      if (!table) return;
      const idx   = Array.from(th.parentNode.children).indexOf(th);
      const tbody = table.querySelector('tbody');
      if (!tbody) return;

      const asc = th.classList.contains('sorted-asc');
      table.querySelectorAll('thead th').forEach(h => {
        h.classList.remove('sorted', 'sorted-asc', 'sorted-desc');
      });

      const rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort(function (a, b) {
        const aText = (a.cells[idx] ? a.cells[idx].textContent.trim() : '').toLowerCase();
        const bText = (b.cells[idx] ? b.cells[idx].textContent.trim() : '').toLowerCase();
        return asc ? bText.localeCompare(aText) : aText.localeCompare(bText);
      });

      rows.forEach(r => tbody.appendChild(r));
      th.classList.add('sorted', asc ? 'sorted-desc' : 'sorted-asc');
    });
  });


  // ─────────────────────────────────────────────────────────────────────────
  // 7. FORM VALIDATION UX
  // ─────────────────────────────────────────────────────────────────────────
  document.querySelectorAll('.ncv-input, .ncv-select, .ncv-textarea').forEach(function (el) {
    el.addEventListener('blur', function () {
      if (el.required && !el.value.trim()) {
        el.classList.add('is-invalid');
      } else {
        el.classList.remove('is-invalid');
      }
    });
    el.addEventListener('input', function () {
      if (el.value.trim()) {
        el.classList.remove('is-invalid');
      }
    });
  });


  // ─────────────────────────────────────────────────────────────────────────
  // 8. CARD HOVER LIFT
  // ─────────────────────────────────────────────────────────────────────────
  document.querySelectorAll('.ncv-card').forEach(function (card) {
    if (card.closest('a') || card.tagName === 'A') return;
    card.addEventListener('mouseenter', function () {
      this.style.transform = 'translateY(-2px)';
    });
    card.addEventListener('mouseleave', function () {
      this.style.transform = '';
    });
  });


  // ─────────────────────────────────────────────────────────────────────────
  // 9. AUTO-DISMISS ALERTS
  // ─────────────────────────────────────────────────────────────────────────
  document.querySelectorAll('.alert').forEach(function (alert) {
    setTimeout(function () {
      alert.classList.remove('show');
      setTimeout(() => alert.remove(), 300);
    }, 5000);
  });


  // ─────────────────────────────────────────────────────────────────────────
  // 10. UTILITY: Escape HTML
  // ─────────────────────────────────────────────────────────────────────────
  function escapeHtml(str) {
    const el = document.createElement('div');
    el.textContent = str;
    return el.innerHTML;
  }


  // ─────────────────────────────────────────────────────────────────────────
  // 11. KANBAN DRAG-AND-DROP (lightweight)
  // ─────────────────────────────────────────────────────────────────────────
  let dragCard = null;

  document.addEventListener('dragstart', function (e) {
    if (e.target.classList.contains('ncv-kanban-card')) {
      dragCard = e.target;
      e.target.style.opacity = '0.5';
    }
  });

  document.addEventListener('dragend', function (e) {
    if (e.target.classList.contains('ncv-kanban-card')) {
      e.target.style.opacity = '';
      dragCard = null;
    }
  });

  document.querySelectorAll('.ncv-kanban-col').forEach(function (col) {
    col.addEventListener('dragover', function (e) {
      e.preventDefault();
      col.style.outline = '2px dashed var(--ncv-blue-400)';
    });
    col.addEventListener('dragleave', function () {
      col.style.outline = '';
    });
    col.addEventListener('drop', function (e) {
      e.preventDefault();
      col.style.outline = '';
      if (dragCard) {
        col.appendChild(dragCard);
        showToast('Stage Updated', 'Opportunity moved to new stage.', 'success');
      }
    });
  });


  // ─────────────────────────────────────────────────────────────────────────
  // 12. COLLAPSIBLE SIDEBAR SECTIONS
  // ─────────────────────────────────────────────────────────────────────────
  (function initCollapsibleNav() {
    var STORAGE_KEY = 'ncv_collapsed_sections';

    function getCollapsed() {
      try { return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]'); }
      catch (e) { return []; }
    }

    function saveCollapsed(list) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
    }

    document.querySelectorAll('.ncv-nav-section').forEach(function (section) {
      var label = section.querySelector('.ncv-nav-label');
      if (!label) return;

      var sectionId = label.textContent.trim().toLowerCase().replace(/\s+/g, '-');
      section.dataset.section = sectionId;

      // Wrap all nav-items inside a .ncv-nav-items container
      var items = section.querySelectorAll('.ncv-nav-item');
      if (items.length === 0) return;

      var itemsWrap = document.createElement('div');
      itemsWrap.className = 'ncv-nav-items';
      items.forEach(function (item) { itemsWrap.appendChild(item); });
      section.appendChild(itemsWrap);

      // Add chevron indicator to label
      var chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      chevron.setAttribute('class', 'ncv-nav-chevron');
      chevron.setAttribute('viewBox', '0 0 24 24');
      chevron.setAttribute('fill', 'none');
      chevron.setAttribute('stroke', 'currentColor');
      chevron.setAttribute('stroke-width', '2.5');
      var poly = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
      poly.setAttribute('points', '6 9 12 15 18 9');
      chevron.appendChild(poly);
      label.appendChild(chevron);

      // Restore collapsed state (keep open if section has active item)
      var hasActive = itemsWrap.querySelector('.ncv-nav-item.active');
      var collapsed = getCollapsed();
      if (collapsed.indexOf(sectionId) !== -1 && !hasActive) {
        section.classList.add('collapsed');
      }

      // Toggle on click
      label.addEventListener('click', function () {
        section.classList.toggle('collapsed');
        var list = getCollapsed();
        if (section.classList.contains('collapsed')) {
          if (list.indexOf(sectionId) === -1) list.push(sectionId);
        } else {
          var idx = list.indexOf(sectionId);
          if (idx > -1) list.splice(idx, 1);
        }
        saveCollapsed(list);
      });
    });
  })();


  // ─────────────────────────────────────────────────────────────────────────
  // 13. TOOLTIP INIT (Bootstrap)
  // ─────────────────────────────────────────────────────────────────────────
  if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
    document.querySelectorAll('[title]').forEach(function (el) {
      new bootstrap.Tooltip(el, { trigger: 'hover', delay: { show: 300, hide: 0 } });
    });
  }

})();
