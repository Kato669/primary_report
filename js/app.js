const name_1 = document.getElementById('name');
const icon = document.getElementById('icon');
const leftBar = document.getElementById('left-bar');
const rightBar = document.getElementById('right-bar');
const sideTexts = document.querySelectorAll('.side-text');
const toggleBar = document.querySelector('.toggleBar');
const overlay = document.getElementById('sidebarOverlay');

function openSidebar() {
    name_1.classList.add('active');
    leftBar.classList.add('active');
    rightBar.classList.add('active');
    sideTexts.forEach(t => t.classList.add('active'));
    if (overlay) overlay.classList.add('active');
}

function closeSidebar() {
    name_1.classList.remove('active');
    leftBar.classList.remove('active');
    rightBar.classList.remove('active');
    sideTexts.forEach(t => t.classList.remove('active'));
    if (overlay) overlay.classList.remove('active');
}

function toggleSidebar() {
    if (leftBar.classList.contains('active')) {
        closeSidebar();
    } else {
        openSidebar();
    }
}

if (icon) icon.addEventListener('click', toggleSidebar);
if (toggleBar) toggleBar.addEventListener('click', toggleSidebar);
if (overlay) overlay.addEventListener('click', closeSidebar);

document.addEventListener('DOMContentLoaded', function() {
    // Wrap plain tables in .table-responsive if not already wrapped
    document.querySelectorAll('table').forEach(function(tbl) {
        if (tbl.closest('.table-responsive')) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        tbl.parentNode.insertBefore(wrapper, tbl);
        wrapper.appendChild(tbl);
    });

    // Initialize DataTable on #example with horizontal scroll
    if (document.querySelector('#example')) {
        new DataTable('#example', { scrollX: true });
    }

    // Initialize any .datatable tables
    document.querySelectorAll('table.datatable').forEach(function(t) {
        try { new DataTable(t, { scrollX: true }); } catch(e) {}
    });

    // Ensure images scale responsively
    document.querySelectorAll('img').forEach(function(img) {
        if (!img.classList.contains('img-fluid')) {
            img.classList.add('img-fluid');
        }
    });

    // Make primary submit buttons full-width on mobile via d-grid wrapper.
    // Match explicit type="submit" OR buttons without type (default = submit).
    // Exclude type="button", btn-sm (toolbar), and already-wrapped buttons.
    document.querySelectorAll('form button.btn:not([type="button"]):not(.btn-sm), form input[type="submit"].btn').forEach(function(b) {
        if (b.closest('.d-grid')) return; // already wrapped
        const wrapper = document.createElement('div');
        wrapper.className = 'd-grid d-md-block';
        b.parentNode.insertBefore(wrapper, b);
        wrapper.appendChild(b);
    });
});
