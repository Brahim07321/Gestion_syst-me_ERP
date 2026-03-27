@extends('layout')
@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- Main Content -->
    <div class="main-content mx-4">
        <h2 class="page-title">Contrôle des Quantités</h2>

        <div class="table-container">
            <div class="table-header">
                <div>
                    <button type="button" class="btn btn-add btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#addProductModal">
                        <i class="fa-solid fa-plus"></i> Ajouter un Produit
                    </button>
                    <button class="btn btn-export text-white bg-black  btn-sm">
                        <i class="fas fa-file-export"></i> Exporter
                    </button>
                </div>
                <div class="search-box mt-2 ">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" placeholder="Search...">
                        <button class="btn btn-outline-secondary btn-sm" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="entries-control mb-3">
                <span>Afficher</span>
                <select class="form-select form-select-sm entries-dropdown">
                    <option selected>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span>entrées</span>
            </div>

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
                            <td>{{ $product->category->Category ?? 'Non défini' }}</td>
                            <td>{{ $product->Designation }}</td>
                            <td>{{ $product->prace_bay }}</td>
                            <td>{{ $product->prace_sell }}</td>
                            <td
                                class="product-quantity text-center {{ $product->Quantite < 2 ? 'text-danger fw-bold' : '' }}">
                                {{ $product->Quantite }}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-add btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <a href="{{ route('stock.edit', $product->id) }}" class="text-white text-decoration-none">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


            <style>
                .low-quantity-cell {
                    background-color: #ffcccc !important;
                    /* Rouge clair pour l'arrière-plan */
                    color: #ff0000 !important;
                    /* Rouge vif pour le texte */
                    font-weight: bold;
                }
            </style>

            <!-- JavaScript corrigé -->
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const tableBody = document.querySelector("#productTable tbody");

                    if (!tableBody) {
                        console.error("Table introuvable !");
                        return;
                    }

                    const rows = Array.from(tableBody.querySelectorAll("tr"));
                    const lowQuantityRows = [];

                    rows.forEach(row => {
                        const quantityCell = row.querySelector(".product-quantity");
                        const quantity = parseInt(quantityCell.textContent.trim(), 10);

                        if (!isNaN(quantity) && quantity < 5) {
                            // Ajoute une classe spécifique à la cellule
                            quantityCell.classList.add("low-quantity-cell");
                            lowQuantityRows.push(row); // Ajoute la ligne à la liste pour les déplacer en haut
                        }
                    });

                    // Déplace les lignes avec une faible quantité en haut du tableau
                    lowQuantityRows.forEach(row => {
                        tableBody.prepend(row);
                    });
                });
            </script>
            <div class="pagination-info">
                <div>
                    Affichage de 1 à 6 sur 6 entrées
                </div>
                <div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Précédent</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Suivant</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for modifer Product Form -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-green">
                    <h5 class="modal-title" id="addProductModalLabel">Modifier le product  </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('stock.update', $product->id) }}" method="POST">

                        @csrf
                        @method('PUT')
                
                        <div class="mb-3">
                            <label for="Category_ID">Catégorie</label>
                            <select name="Category_ID" id="Category_ID" class="form-control" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->Category_ID == $category->id ? 'selected' : '' }}>
                                        {{ $category->Category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                
                        <div class="mb-3">
                            <label for="Designation">Désignation</label>
                            <input type="text" name="Designation" value="{{ $product->Designation }}" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="code">Code</label>
                            <input type="number" name="code" value="{{ $product->code }}" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="Quantite">Quantité</label>
                            <input type="number" name="Quantite" value="{{ $product->Quantite }}" class="form-control" required>
                        </div>
                
                        <div class="mb-3">
                            <label for="prace_bay">Prix Achat</label>
                            <input type="number" name="prace_bay" value="{{ $product->prace_bay }}" class="form-control">
                        </div>
                
                        <div class="mb-3">
                            <label for="prace_sell">Prix Vente</label>
                            <input type="number" name="prace_sell" value="{{ $product->prace_sell }}" class="form-control">
                        </div>
                
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                    </form>
                    <script>
                        rows.forEach(row => {
    const quantityCell = row.querySelector(".product-quantity");
    if (quantityCell) {
        const quantity = parseInt(quantityCell.textContent.trim(), 10);
        if (!isNaN(quantity) && quantity < 5) {
            quantityCell.classList.add("low-quantity-cell");
            lowQuantityRows.push(row);
        }
    }
});

                    </script>
                </div>
               
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /*
            // JavaScript for form submission
            document.getElementById('productForm').addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                // Collect form data
                const productName = document.getElementById('productName').value;
                const productPrice = document.getElementById('productPrice').value;
                const productQuantity = document.getElementById('productQuantity').value;
                const productCategory = document.getElementById('productCategory').value;

                // Basic validation
                if (!productName || !productPrice || !productQuantity || !productCategory) {
                    alert('Veuillez remplir tous les champs obligatoires.');
                    return;
                }

                // Show success message
                alert(`Produit ajouté avec succès :
            Nom : ${productName}
            Prix : ${productPrice}
            Quantité : ${productQuantity}
            Catégorie : ${productCategory}`);

                // Reset form
                this.reset();

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
                modal.hide();
            });
    </script>
@endsection








