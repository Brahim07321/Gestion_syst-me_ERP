@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <div class="mb-4">
        <h2 class="fw-bold mb-1">Tableau de bord</h2>
        <p class="text-muted mb-0">
            Bienvenue dans votre espace de gestion. Voici un aperçu global de votre activité.
        </p>
    </div>

    <!-- ALERTE STOCK -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 position-relative overflow-hidden">
        <div class="card-body d-flex justify-content-between align-items-center p-4" >
            <div>
                <h5 class="fw-bold mb-1">Produits en stock faible</h5>
                <p class="text-muted mb-0">Surveillez les produits qui nécessitent un réapprovisionnement.</p>
            </div>

            <div class="text-end">
                <div class="fs-2 fw-bold text-danger">{{ $lowStockCount }}</div>

                @if ($lowStockCount > 0)
                    <span class="badge bg-danger px-3 py-2 mt-2">Alerte</span>
                @endif
            </div>
        </div>

        <div class="card-footer bg-white border-0 px-4 pb-4 pt-0">
            <a href="/stock?low_stock=1" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                Vérifier maintenant <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <!-- CARTES PRINCIPALES -->
    <div class="dashboard-cards">

        <div class="card card-blue">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-file-export"></i>
                </div>
                <div class="card-number">{{ $facturesCount }}</div>
                <div class="card-title">Bons de livraison</div>
                <a href="/facture">
                    <button class="card-more">
                        Voir plus <i class="fas fa-arrow-right"></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="card card-green">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="card-number">{{ $CategoryCount }}</div>
                <div class="card-title">Catégories</div>
                <a href="/Category">
                    <button class="card-more">
                        Voir plus <i class="fas fa-arrow-right"></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="card card-orange">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="card-number">{{ $productesCount }}</div>
                <div class="card-title">Produits</div>
                <a href="/product">
                    <button class="card-more">
                        Voir plus <i class="fas fa-arrow-right"></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="card card-red">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-number">{{ $CustomeresCount }}</div>
                <div class="card-title">Clients</div>
                <a href="/Customer">
                    <button class="card-more">
                        Voir plus <i class="fas fa-arrow-right"></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="card card-purple">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-file-import"></i>
                </div>
                <div class="card-number">{{ $PurchaseCount }}</div>
                <div class="card-title">Bons d'achat</div>
                <a href="/purchases">
                    <button class="card-more">
                        Voir plus <i class="fas fa-arrow-right"></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="card card-pink">
            <div class="card-body">
                <div class="card-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="card-number">{{ $facturesCount + $PurchaseCount }}</div>
                <div class="card-title">Total des documents</div>
                <a href="/archife">
                    <button class="card-more">
                        Voir plus <i class="fas fa-arrow-right"></i>
                    </button>
                </a>
            </div>
        </div>
    </div>

    <!-- SECTION COMPTABILITE -->
    <div class="mt-5 mb-3">
        <h4 class="fw-bold mb-1">Résumé comptable</h4>
        <p class="text-muted mb-0">Vue rapide de la situation financière de votre activité.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-4">
                    <h6 class="text-muted">Total des ventes</h6>
                    <h5 class="fw-bold text-primary mb-0">{{ number_format($totalSales, 2) }} MAD</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-4">
                    <h6 class="text-muted">Montant payé</h6>
                    <h5 class="fw-bold text-success mb-0">{{ number_format($totalPaid, 2) }} MAD</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-4">
                    <h6 class="text-muted">Reste à recevoir</h6>
                    <h5 class="fw-bold text-danger mb-0">{{ number_format($totalRemaining, 2) }} MAD</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-4">
                    <h6 class="text-muted">Total des achats</h6>
                    <h5 class="fw-bold text-warning mb-0">{{ number_format($totalPurchases, 2) }} MAD</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-4">
                    <h6 class="text-muted">Dépenses</h6>
                    <h5 class="fw-bold text-secondary mb-0">{{ number_format($totalExpenses, 2) }} MAD</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-4">
                    <h6 class="text-muted">Profit net</h6>
                    <h5 class="fw-bold mb-0 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($netProfit, 2) }} MAD
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- ALERTES -->
    @if ($totalRemaining > 0)
        <div class="alert alert-warning border-0 shadow-sm rounded-4">
            <strong>Attention :</strong>
            il reste <strong>{{ number_format($totalRemaining, 2) }} MAD</strong> à encaisser auprès des clients.
        </div>
    @endif

    @if ($netProfit < 0)
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <strong>Attention :</strong>
            le profit net est négatif. Veuillez vérifier les achats et les dépenses.
        </div>
    @endif

    <!-- GRAPHIQUE STOCK -->
    <div class="mt-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">État du stock</h4>
                <p class="text-muted mb-4">Visualisation des quantités disponibles par produit.</p>
                <canvas id="productLineChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- GRAPHIQUE COMPTABILITE -->
    <div class="mt-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3">Analyse comptable</h4>
                <p class="text-muted mb-4">Comparaison entre ventes, achats, dépenses et profit.</p>
                <canvas id="comptaChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- TOP PRODUITS VENDUS -->
    <div class="mt-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Produits les plus vendus</h4>
                    <span class="text-muted small">Top 5 des produits les plus demandés</span>
                </div>

                <div class="table-responsive dashboard-table">
                    <table class="table align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Référence</th>
                                <th>Désignation</th>
                                <th>Quantité vendue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ $product->referonce }}</td>
                                    <td>{{ $product->designation }}</td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success">
                                            {{ $product->total_sold }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucun produit vendu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUITS RENTABLES -->
    <div class="mt-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Produits les plus rentables</h4>
                    <span class="text-muted small">Top 5 des produits les plus profitables</span>
                </div>

                <div class="table-responsive dashboard-table">
                    <table class="table align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Désignation</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProfitProducts as $product)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ $product->designation }}</td>
                                    <td>
                                        <span class="badge rounded-pill px-3 py-2 bg-success-subtle text-success">
                                            {{ number_format($product->total_profit, 2) }} MAD
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Aucun produit rentable trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CHART PRODUITS RENTABLES -->
    <div class="mt-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">Analyse des produits rentables</h4>
                    <span class="text-muted small">Visualisation des profits</span>
                </div>

                <div style="height: 320px;">
                    <canvas id="profitProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .dashboard-table {
        max-height: 320px;
        overflow-y: auto;
        overflow-x: hidden;
        border-radius: 14px;
        background: #fff;
    }

    .dashboard-table table th,
    .dashboard-table table td {
        white-space: normal;
        word-break: break-word;
        vertical-align: middle;
    }

    .dashboard-table tbody tr {
        transition: all 0.2s ease;
    }

    .dashboard-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.01);
    }

    .bg-success-subtle {
        background: #ecfdf5;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const products = @json($productsChart ?? []);
    const labels = products.map(p => p.Designation);
    const quantities = products.map(p => p.Quantite);
    const lowStock = products.map(p => p.Quantite < 5 ? p.Quantite : null);

    const stockCanvas = document.getElementById('productLineChart');

    if (stockCanvas) {
        const ctx = stockCanvas.getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tous les produits',
                        data: quantities,
                        borderColor: 'blue',
                        backgroundColor: 'transparent',
                        tension: 0.3
                    },
                    {
                        label: 'Stock faible (< 5)',
                        data: lowStock,
                        borderColor: 'red',
                        backgroundColor: 'transparent',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    const comptaCanvas = document.getElementById('comptaChart');

    if (comptaCanvas) {
        const comptaCtx = comptaCanvas.getContext('2d');

        new Chart(comptaCtx, {
            type: 'bar',
            data: {
                labels: ['Ventes', 'Achats', 'Dépenses', 'Profit'],
                datasets: [{
                    label: 'Montant en MAD',
                    data: [
                        {{ $totalSales ?? 0 }},
                        {{ $totalPurchases ?? 0 }},
                        {{ $totalExpenses ?? 0 }},
                        {{ $netProfit ?? 0 }}
                    ],
                    borderWidth: 1,
                    borderRadius: {
                        topLeft: 10,
                        topRight: 10,
                        bottomLeft: 0,
                        bottomRight: 0
                    },
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    const profitProducts = @json($topProfitProducts ?? []);
    const profitLabels = profitProducts.map(item => item.designation);
    const profitValues = profitProducts.map(item => parseFloat(item.total_profit));
    const profitCanvas = document.getElementById('profitProductsChart');

    if (profitCanvas) {
        new Chart(profitCanvas, {
            type: 'bar',
            data: {
                labels: profitLabels,
                datasets: [{
                    label: 'Profit (MAD)',
                    data: profitValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1.5,
                    borderRadius: {
                        topLeft: 10,
                        topRight: 10,
                        bottomLeft: 0,
                        bottomRight: 0
                    },
                    borderSkipped: false,
                    maxBarThickness: 55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return Number(context.raw).toLocaleString('fr-FR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' MAD';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return Number(value).toLocaleString('fr-FR') + ' MAD';
                            }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection