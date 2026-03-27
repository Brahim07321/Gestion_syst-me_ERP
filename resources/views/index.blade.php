@extends('layout')

@section('content')

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <h2>Dashboard Overview</h2>
        <p class="text-muted">Welcome back, Admin. Here's what's happening with your inventory today.</p>
        
        <div class="dashboard-cards">
            <!-- FACTURE Card -->
  
    <div class="card card-blue " >
        <div class="card-body">
            <div class="card-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="card-number">{{ $facturesCount }}</div>      
                  <div class="card-title"> Facture</div>
            <a href="/facture"><button class="card-more">More info<i class="fas fa-arrow-right"></i></button></button></a> 
        </div>
    </div>
    
    
    <!-- Category Card -->
    <div class="card card-green">
        <div class="card-body">
            <div class="card-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="card-number">{{ $CategoryCount }}</div>
            <div class="card-title">Categories</div>
            <a href="/Category"><button class="card-more">More info <i class="fas fa-arrow-right"></i></button></a>
        </div>
    </div>
    
    <!-- Product Card -->
    <div class="card card-orange">
        <div class="card-body">
            <div class="card-icon">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="card-number">{{  $productesCount }}</div>
            <div class="card-title">Products</div>
           <a href="/product"> <button class="card-more">More info <i class="fas fa-arrow-right"></i></button></a>
        </div>
    </div>
    
    <!-- Customer Card -->
    <div class="card card-red">
        <div class="card-body">
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-number">{{ $CustomeresCount }}</div>
            <div class="card-title">Customers</div>
            <a href="/Customer"><button class="card-more">More info <i class="fas fa-arrow-right"></i></button></a>
        </div>
    </div>
    
    <!-- Supplier Card -->
    <div class="card card-purple">
        <div class="card-body">
            <div class="card-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="card-number">{{ $lowStockCount }}</div>
            <div class="card-title">stock</div>
           <a href="/stock"><button class="card-more">More info <i class="fas fa-arrow-right"></i></button></a>
        </div>
    </div>
    
    <!-- Total Purchase Card -->
    <div class="card card-pink">
        <div class="card-body">
            <div class="card-icon">
                <i class="fas fa-archive"></i>
            </div>
            <div class="card-number">{{ $facturesCount }}</div>
            <div class="card-title">Total Factures</div>
           <a href="/archife"> <button class="card-more">More info <i class="fas fa-arrow-right"></i></button></a>
        </div>
    </div>
</div>
<!--chart------------->
<div class="mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">📈 Stock des Produits (Line Chart)</h4>
            <canvas id="productLineChart" height="120"></canvas>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const products = @json($productsChart);

    const labels = products.map(p => p.Designation);
    const quantities = products.map(p => p.Quantite);

    // 🔴 غير low stock
    const lowStock = products.map(p => p.Quantite < 5 ? p.Quantite : null);

    const ctx = document.getElementById('productLineChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Tous les Produits',
                    data: quantities,
                    borderColor: 'blue',
                    backgroundColor: 'transparent',
                    tension: 0.3
                },
                {
                    label: 'Low Stock (< 5)',
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
</script>@endsection