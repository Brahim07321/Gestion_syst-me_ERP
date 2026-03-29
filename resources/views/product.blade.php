@extends('layout')

@section('content')
    <div class="main-content main-content-expanded" id="mainContent">
        <h2 class="page-title">Liste des Produits</h2>

        <div class="table-container">
            <div class="table-header">
                <div>
                    <button type="button" class="btn btn-add btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#productModal" onclick="openAddModal()">
                        <i class="fa-solid fa-plus"></i> Ajouter un Produit
                    </button>
                    <a href="{{ route('products.template') }}" class="btn btn-dark btn-sm ">
                        <i class="fas fa-file-download me-2"></i>
                        Download Template
                    </a>

                   

                   

                        <a href="{{ route('stock.export', request()->query()) }}"
                            class="btn btn-export text-white btn-success  btn-sm">
                            <i class="fas fa-file-export"></i> Exporter
                        </a>
                  


                    <form id="importForm" action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                    
                        <input type="file" name="file" class="form-control mb-2 mt-2">
                    
                        <div class="progress mb-2" style="height: 10px;">
                            <div id="progressBar" class="progress-bar" style="width: 0%">0%</div>
                        </div>
                    
                        <button type="submit" class="btn btn-primary"><i class="fas fa-file-import me-1"></i>Import Excel</button>
                    </form>
                </div>

                <form action="{{ url('/product') }}" method="GET" class="search-box">
                    <div class="input-group mt-2">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search by designation, reference or code..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary btn-sm" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

            </div>

            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <table id="productTable" class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Référence</th>
                        <th>Code</th>
                        <th>Catégorie</th>
                        <th>Désignation</th>
                        <th>Prix Achat</th>
                        <th>Prix Vente</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->Referonce }}</td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->category->Category ?? ($product->category->category ?? 'Non défini') }}</td>
                            <td>{{ $product->Designation }}</td>
                            <td>{{ $product->prace_bay }} DH</td>
                            <td>{{ $product->prace_sell }} DH</td>
                            <td class="product-quantity text-center">{{ $product->Quantite }}</td>
                            <td class="text-center d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-primary btn-sm action-btn" data-bs-toggle="modal"
                                    data-bs-target="#productModal"
                                    onclick="openEditModal(
                                        '{{ $product->id }}',
                                        '{{ $product->Category_ID }}',
                                        '{{ $product->Referonce }}',
                                        '{{ $product->Designation }}',
                                        '{{ $product->code }}',
                                        '{{ $product->Quantite }}',
                                        '{{ $product->prace_bay }}',
                                        '{{ $product->prace_sell }}'
                                    )">
                                    Modifier
                                </button>

                                <form action="{{ route('product.destroy', $product->id) }}" method="POST"
                                    onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm action-btn">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $products->appends(['search' => request('search')])->links('pagination::bootstrap-5') }} </div>
        </div>
    </div>

    <style>
        .low-quantity-cell {
            background-color: #ffcccc !important;
            color: #ff0000 !important;
            font-weight: bold;
        }
    </style>

    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-green">
                    <h5 class="modal-title" id="productModalLabel">Ajouter un Produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="productForm" method="POST" action="{{ route('product.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Catégorie</label>
                            <select class="form-select" id="productCategory" name="Category_ID" required>
                                <option value="">-- Choisissez une Catégorie --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->Category ?? $category->category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="productReferonce" class="form-label">Référence</label>
                            <input type="text" class="form-control" id="productReferonce" name="Referonce" required>
                        </div>

                        <div class="mb-3">
                            <label for="productName" class="form-label">Désignation</label>
                            <input type="text" class="form-control" id="productName" name="Designation" required>
                        </div>

                        <div class="mb-3">
                            <label for="productCode" class="form-label">Code</label>
                            <input type="text" class="form-control" id="productCode" name="code" required>
                        </div>

                        <div class="mb-3">
                            <label for="productQuantity" class="form-label">Quantité</label>
                            <input type="number" class="form-control" id="productQuantity" name="Quantite" required>
                        </div>

                        <div class="mb-3">
                            <label for="productPriceBay" class="form-label">Prix d'achat</label>
                            <input type="number" step="0.01" class="form-control" id="productPriceBay"
                                name="prace_bay" required>
                        </div>

                        <div class="mb-3">
                            <label for="productPriceSell" class="form-label">Prix de vente</label>
                            <input type="number" step="0.01" class="form-control" id="productPriceSell"
                                name="prace_sell" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-success" id="submitBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('productModalLabel').textContent = 'Ajouter un Produit';
            document.getElementById('submitBtn').textContent = 'Ajouter';

            const form = document.getElementById('productForm');
            form.action = "{{ route('product.store') }}";

            document.getElementById('formMethod').value = 'POST';
            document.getElementById('productCategory').value = '';
            document.getElementById('productReferonce').value = '';
            document.getElementById('productName').value = '';
            document.getElementById('productCode').value = '';
            document.getElementById('productQuantity').value = '';
            document.getElementById('productPriceBay').value = '';
            document.getElementById('productPriceSell').value = '';
        }

        function openEditModal(id, categoryId, referonce, designation, code, quantite, priceBay, priceSell) {
            document.getElementById('productModalLabel').textContent = 'Modifier le Produit';
            document.getElementById('submitBtn').textContent = 'Mettre à jour';

            const form = document.getElementById('productForm');
            form.action = `/product/${id}`;

            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('productCategory').value = categoryId;
            document.getElementById('productReferonce').value = referonce;
            document.getElementById('productName').value = designation;
            document.getElementById('productCode').value = code;
            document.getElementById('productQuantity').value = quantite;
            document.getElementById('productPriceBay').value = priceBay;
            document.getElementById('productPriceSell').value = priceSell;
        }



        document.getElementById('importForm').addEventListener('submit', function() {

let bar = document.getElementById('progressBar');

let width = 0;

let interval = setInterval(() => {
    if (width >= 90) {
        clearInterval(interval);
    } else {
        width += 10;
        bar.style.width = width + "%";
        bar.innerHTML = width + "%";
    }
}, 100);
});
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
