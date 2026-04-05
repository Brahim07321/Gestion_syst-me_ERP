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

            --card-blue: #3b82f6;
            --card-green: #10b981;
            --card-orange: #f59e0b;
            --card-red: #ef4444;
            --card-purple: #8b5cf6;
            --card-pink: #ec4899;

            --menu-dashboard: #3b82f6;
            --menu-category: #10b981;
            --menu-product: #f59e0b;
            --menu-customer: #ef4444;
            --menu-supplier: #8b5cf6;
            --menu-outgoing: #06b6d4;
            --menu-purchase: #14b8a6;
            --menu-users: #64748b;
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

        .nav-menu li.active {
            background: #c5bebe;
            color: #000000;
            font-weight: 600;
        }


        .nav-menu {
            list-style: none;

            overflow-y: auto;
            overflow-x: hidden;
        }



        /* SCROLLBAR MODERN */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: #a7acb4 transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #3b82f6, #6366f1);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #60a5fa, #818cf8);
        }

        .user-profile {
    position: relative;
}

.user-trigger {
    border: 0;
    background:#2ecc71;
    border-radius: 18px;
    padding: 8px 14px;
    min-width: 220px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    transition: all 0.2s ease;
}



.user-trigger-left {
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: #000000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.user-avatar.large {
    width: 46px;
    height: 46px;
    font-size: 18px;
    flex-shrink: 0;
}

.user-meta {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.user-name {
    font-weight: 700;
    color: #0f172a;
    font-size: 14px;
}

.user-role {
    color: #64748b;
    font-size: 12px;
}

.user-arrow {
    color: #94a3b8;
    font-size: 12px;
}

.user-dropdown-menu {
    position: absolute;
    top: 58px;
    right: 0;
    width: 270px;
    background: #ffffff;
    border-radius: 20px;
    padding: 14px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    border: 1px solid #e2e8f0;
    display: none;
    z-index: 1055;
}

.user-dropdown-menu.show {
    display: block;
    animation: dropdownFade 0.2s ease;
}

@keyframes dropdownFade {
    from {
        opacity: 0;
        transform: translateY(-6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-dropdown-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dropdown-user-name {
    font-weight: 700;
    color: #0f172a;
    font-size: 14px;
}

.dropdown-user-email {
    color: #64748b;
    font-size: 12px;
    word-break: break-word;
}

.dropdown-divider-custom {
    height: 1px;
    background: #e2e8f0;
    margin: 14px 0;
}

.dropdown-action {
    width: 100%;
    border: 0;
    background: transparent;
    border-radius: 14px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.dropdown-action i {
    width: 18px;
    text-align: center;
}

.logout-action {
    color: #dc2626;
    background: #fef2f2;
}

.logout-action:hover {
    background: #fee2e2;
}

.logout-modal-icon {
    width: 68px;
    height: 68px;
    margin: 0 auto;
    border-radius: 50%;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
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
            </div>
        </div>

        <ul class="nav-menu">

            <a href="/">
                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Dashboard
                </li>
            </a>

            <a href="/facture">
                <li class="{{ request()->is('facture') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i> Factures
                </li>
            </a>

            <a href="/archife">
                <li class="{{ request()->is('archife') ? 'active' : '' }}">
                    <i class="fas fa-folder-open"></i> Archive Factures
                </li>
            </a>

            <a href="/product">
                <li class="{{ request()->is('product') ? 'active' : '' }}">
                    <i class="fas fa-box-open"></i> Produits
                </li>
            </a>

            <a href="/Category">
                <li class="{{ request()->is('Category') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> Catégories
                </li>
            </a>

            <a href="/stock">
                <li class="{{ request()->is('stock') || request()->is('stock/*') ? 'active' : '' }}">
                    <i class="fas fa-warehouse"></i> Stock
                </li>
            </a>

            <a href="/purchases">
                <li
                    class="{{ request()->is('purchases') || request()->is('purchases') || request()->is('purchase/*') ? 'active' : '' }}">
                    <i class="fas fa-cart-plus"></i> Achats
                </li>
            </a>
            <a href="/stock-movements">
                <li class="{{ request()->is('stock-movements') ? 'active' : '' }}">
                    <i class="fas fa-exchange-alt"></i> Historique Stock
                </li>
            </a>

            <a href="/suppliers">
                <li class="{{ request()->is('suppliers') || request()->is('suppliers/*') ? 'active' : '' }}">
                    <i class="fas fa-truck"></i> Fournisseurs
                </li>
            </a>

            <a href="/Customer">
                <li class="{{ request()->is('Customer') || request()->is('customers/*') ? 'active' : '' }}">
                    <i class="fas fa-user-friends"></i> Clients
                </li>
            </a>
            <a href="/expenses">
                <li class="{{ request()->is('expenses') || request()->is('expenses/*') ? 'active' : '' }}">
                    <i class="fas fa-wallet"></i> Dépenses
                </li>
            </a>

            <a href="{{ route('settings.company.edit') }}">
                <li class="{{ request()->routeIs('settings.company.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i> Paramètres société
                </li>
            </a>
         
            @auth
    @if(Auth::user()->role === 'admin')
        <a href="{{ route('users.index') }}">
            <li class="{{ request()->is('users') || request()->is('users/*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i> Utilisateurs
            </li>
        </a>
    @endif
@endauth


        </ul>
    </div>

    <!-- Header -->
    <div class="header">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
            
        </button>
        <h1>Inventory System</h1>
        <div class="user-profile position-relative">

            <button type="button" class="user-trigger" onclick="toggleUserDropdown()">
                <div class="user-trigger-left">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-meta">
                        <span class="user-name">{{ Auth::user()->name }}</span>
                        <small class="user-role">Administrateur</small>
                    </div>
                </div>
                <i class="fas fa-chevron-down user-arrow"></i>
            </button>
        
            <div id="userDropdownMenu" class="user-dropdown-menu">
                <div class="user-dropdown-header">
                    <div class="user-avatar large">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="dropdown-user-name">{{ Auth::user()->name }}</div>
                        <div class="dropdown-user-email">{{ Auth::user()->email }}</div>
                    </div>
                </div>
        
                <div class="dropdown-divider-custom"></div>
        
                <button type="button"
                        class="dropdown-action logout-action"
                        data-bs-toggle="modal"
                        data-bs-target="#logoutConfirmModal">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </button>
            </div>
        
            <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                @csrf
            </form>
        </div>
    </div>
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
    
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-sign-out-alt text-danger fs-1"></i>
                    </div>
    
                    <h5 class="fw-bold mb-2">Confirmation de déconnexion</h5>
    
                    <p class="text-muted mb-0">
                        Voulez-vous vraiment vous déconnecter ?
                    </p>
    
                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button"
                                class="btn btn-light rounded-pill px-4"
                                data-bs-dismiss="modal">
                            Annuler
                        </button>
    
                        <button type="button"
                                class="btn btn-danger rounded-pill px-4"
                                onclick="document.getElementById('logoutForm').submit()">
                            Oui, déconnexion
                        </button>
                    </div>
                </div>
    
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-4 m-3">
        {{ session('error') }}
    </div>
@endif

    @yield('content')



    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle sidebar on button click
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
            const mainContent = document.getElementById('mainContent');
            if (mainContent) {
                mainContent.classList.toggle('main-content-expanded');
            }
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
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            if (!sidebar || !mainContent) return;

            if (window.innerWidth <= 768) {
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.add('main-content-expanded');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.remove('main-content-expanded');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Run on initial load


        function toggleUserDropdown() {
        const menu = document.getElementById('userDropdownMenu');
        menu.classList.toggle('show');
    }

    document.addEventListener('click', function (e) {
        const profile = document.querySelector('.user-profile');
        const menu = document.getElementById('userDropdownMenu');

        if (profile && !profile.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
    </script>
    <script src="{{ asset('js/app.js') }}"></script>

</body>

</html>
