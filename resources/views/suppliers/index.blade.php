@extends('layout')

@section('content')


<div class="main-content  main-content-expanded" id="mainContent">

    <div class=" shadow p-4">

        <h3 class="mb-4 text-primary">Gestion des Fournisseurs</h3>

        {{-- FORM --}}
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf

            <div class="row mb-3">
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Nom fournisseur" required>
                </div>

                <div class="col-md-3">
                    <input type="text" name="phone" class="form-control" placeholder="Téléphone">
                </div>

                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email">
                </div>

                <div class="col-md-3">
                    <input type="text" name="address" class="form-control" placeholder="Adresse">
                </div>
            </div>

            <button type="submit" class="btn btn-success mb-4">
                ➕ Ajouter Fournisseur
            </button>
        </form>

        {{-- TABLE --}}
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Adresse</th>
                </tr>
            </thead>

            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->id }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone }}</td>
                        <td>{{ $supplier->email }}</td>
                        <td>{{ $supplier->address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Aucun fournisseur</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@endsection