// Toggle sidebar on button click
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
    document.getElementById('mainContent').classList.toggle('main-content-expanded');
    
    // Change icon based on state
    const icon = this.querySelector('i');
    if (document.getElementById('sidebar').classList.contains('sidebar-collapsed')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
    } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

// Set active menu item
const menuItems = document.querySelectorAll('.nav-menu li');
menuItems.forEach(item => {
    item.addEventListener('click', function() {
        menuItems.forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});

// Card click functionality
const cards = document.querySelectorAll('.card');
cards.forEach(card => {
    card.addEventListener('click', function(e) {
        // Don't trigger if clicking the "More info" button
        if (!e.target.classList.contains('card-more') && !e.target.closest('.card-more')) {
            const title = this.querySelector('.card-title').textContent;
            alert(`Navigating to ${title} section`);
        }
    });
});

// More info button functionality
const moreInfoButtons = document.querySelectorAll('.card-more');
moreInfoButtons.forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent card click from triggering
        const cardTitle = this.closest('.card').querySelector('.card-title').textContent;
        alert(`Showing detailed information for ${cardTitle}`);
    });
});

// Responsive sidebar toggle for mobile
function handleResize() {
    if (window.innerWidth <= 768) {
        document.getElementById('sidebar').classList.add('sidebar-collapsed');
        document.getElementById('mainContent').classList.add('main-content-expanded');
    } else {
        document.getElementById('sidebar').classList.remove('sidebar-collapsed');
        document.getElementById('mainContent').classList.remove('main-content-expanded');
    }
}

window.addEventListener('resize', handleResize);
handleResize(); // Run on initial load
