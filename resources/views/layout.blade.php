<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            /* Card Colors */
            --card-blue: #3498db;
            --card-green: #2ecc71;
            --card-orange: #e67e22;
            --card-red: #e74c3c;
            --card-purple: #9b59b6;
            --card-pink: #e84393;
            /* Menu Item Colors */
            --menu-dashboard: #3498db;
            --menu-category: #2ecc71;
            --menu-product: #e67e22;
            --menu-customer: #e74c3c;
            --menu-supplier: #9b59b6;
            --menu-outgoing: #f39c12;
            --menu-purchase: #1abc9c;
            --menu-users: #34495e;
        }

        /* Card Colors */
        .card-blue {
            background-color: var(--card-blue);
        }

        .card-green {
            background-color: var(--card-green);
        }

        .card-orange {
            background-color: var(--card-orange);
        }

        .card-red {
            background-color: var(--card-red);
        }

        .card-purple {
            background-color: var(--card-purple);
        }

        .card-pink {
            background-color: var(--card-pink);
        }
    </style>
    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="admin-profile">
            <div class="admin-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="admin-info">
                <h5>Mariv Part</h5>
                <p><span class="badge bg-success">Getion de Stock</span></p>
            </div>
        </div>

        <ul class="nav-menu">
            <a href="/">
                <li class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
            </a>
            <a href="/facture">
                <li><i class="fas fa-file-invoice"></i>Facture</li>
            </a>
            <a href="/Category">
                <li><i class="fas fa-tags"></i> Category</li>
            </a>
            <a href="/product">
                <li><i class="fas fa-box"></i> Product</li>
            </a>
            <a href="/Customer">
                <li><i class="fas fa-users"></i> Customer</li>
            </a>
            <a href="/stock">
                <li><i class="fas fa-boxes"></i> Stock</li>
            </a>
            <a href="/archife">
                <li><i class="fas fa-archive"></i> archife Factures </li>
            </a>
            <li><i class="fas fa-sign-in-alt"></i> Purchase Products</li>
            <li><i class="fas fa-user-cog"></i> System Users</li>
        </ul>
    </div>

    <!-- Header -->
    <div class="header">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1>Inventory System</h1>
        <div class="user-profile">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Ik0yNTYgMEMxMTQuNiAwIDAgMTE0LjYgMCAyNTZzMTE0LjYgMjU2IDI1NiAyNTYgMjU2LTExNC42IDI1Ni0yNTZTMzk3LjQgMCAyNTYgMHptMCAxMjhjMzUuMyAwIDY0IDI4LjcgNjQgNjRzLTI4LjcgNjQtNjQgNjQtNjQtMjguNy02NC02NCAyOC43LTY0IDY0LTY0em0wIDI4OGMtODUuNSAwLTE2MC42LTUzLjMtMTkwLjQtMTI4aDM4MC44Yy0yOS44IDc0LjctMTA0LjkgMTI4LTE5MC40IDEyOHoiLz48L3N2Zz4="
                alt="User">
            <span>Admin</span>
        </div>
    </div>



    @yield('content')



    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
    <script src="{{ asset('js/app.js') }}"></script>

</body>

</html>
