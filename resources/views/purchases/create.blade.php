@extends('layout')

@section('content')

<div class="main-content  main-content-expanded" id="mainContent">
    <div class=" shadow p-4">
        <h3 class="mb-4 text-primary">Nouvel Achat</h3>

        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Fournisseur</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">Choisir un fournisseur</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Date Achat</label>
                    <input type="date" name="purchase_date" class="form-control"
                    value="{{ old('purchase_date', date('Y-m-d')) }}">                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm m-2"
    data-bs-toggle="modal" data-bs-target="#addProductModal">
    <i class="fas fa-plus"></i> Nouveau Produit
</button>      
  <!--message-->
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
                 <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix Achat</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="items-list">
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-control" required>
                                <option value="">Choisir produit</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->Designation }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="number" name="items[0][quantity]" class="form-control quantity" value="1" min="1">
                        </td>

                        <td>
                            <input type="number" name="items[0][buy_price]" class="form-control price" step="0.01" min="0">
                        </td>

                        <td>
                            <span class="total">0.00</span>
                        </td>

                        <td>
                            <button type="button" class="btn btn-danger btn-sm delete-item">X</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="button" id="add-item" class="btn btn-primary mb-3">+ Ajouter Produit</button>

            <div class="text-end">
                <button type="submit" class="btn btn-success">Enregistrer Achat</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-green">
                <h5 class="modal-title" id="addProductModalLabel">Ajouter un Produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productForm" method="POST" action="/product" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Catégorie</label>
                        <select class="form-select" id="productCategory" name="Category_ID" required>
                            <option value="">-- Choisissez une Catégorie --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->Category }}</option>
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
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>

        </div>
    </div>
<script>
let index = 1;

document.getElementById('add-item').addEventListener('click', function () {
    const row = document.createElement('tr');

    row.innerHTML = `
        <td>
            <select name="items[${index}][product_id]" class="form-control" required>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->Designation }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="items[${index}][quantity]" class="form-control quantity" value="1"></td>
        <td><input type="number" name="items[${index}][buy_price]" class="form-control price"></td>
        <td><span class="total">0.00</span></td>
        <td><button type="button" class="btn btn-danger btn-sm delete-item">X</button></td>
    `;

    document.getElementById('items-list').appendChild(row);
    index++;
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-item')) {
        e.target.closest('tr').remove();
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection