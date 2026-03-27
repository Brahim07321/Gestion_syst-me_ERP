@extends('layout')

@section('content')

<!-- Add Customer Form Always Visible -->
<div class="card mb-4" 
     style="position: fixed; top: 35px; left: 50%; transform: translateX(-50%); width: 800px; z-index: 1050;">
    <div class="card-header bg-primary text-white p-4">
        <h5 class="mb-0">
            <i class="fas fa-user-edit me-2"></i>Edit Customer Information
        </h5>
        
    </div>
    <div class="card-body">
        <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="customerName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" id="customerName" value="{{ $customer->name }}" required>
                </div>
               
                <div class="col-md-12">
                    <label for="customerAddress" class="form-label">Address</label>
                    <input type="text" class="form-control" id="customerAddress" name="address" value="{{ $customer->address }}" required>
                </div>

                <div class="col-md-12">
                    <label for="customerContact" class="form-label">Contact Number</label>
                    <input type="tel" class="form-control" id="customerContact" name="contact" value="{{ $customer->contact }}" pattern="[0-9]{10}" required>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Save Changes
                </button>
                <a href="/Customer" class="btn btn-danger">
                    <i class="fas fa-ban me-2"></i>Cancel
                </a>
            </div>
            
        </form>
    </div>
</div>

<!-- Main Content -->
<div class="col-md-10 main-content" style="margin-top: 250px;"> <!-- Adjust margin-top based on form height -->    <h2>List of Customers</h2>
    <div class="top-bar d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex gap-2">
            <button class="btn btn-danger"><i class="fas fa-file-pdf me-2"></i>Export PDF</button>
            <button class="btn btn-primary"><i class="fas fa-file-excel me-2"></i>Export Excel</button>
        </div>
    </div>

    <!-- Message Alerts -->
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

    <!-- Customer Table -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>{{ $customer->id }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->address }}</td>
                        <td>{{ $customer->contact }}</td>
                        <td>
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit me-2"></i>Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-end">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
</div>

@endsection
